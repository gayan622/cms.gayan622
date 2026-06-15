<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    // ── POST /api/orders ───────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
            'coupon_code'        => 'nullable|string',
            'customer.first_name'=> 'required|string',
            'customer.last_name' => 'required|string',
            'customer.email'     => 'required|email',
            'customer.phone'     => 'nullable|string',
            'customer.address'   => 'nullable|string',
            'customer.city'      => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Load products and compute subtotal
            $productIds = collect($request->items)->pluck('product_id');
            $products   = Product::findMany($productIds)->keyBy('id');
            $subtotal   = 0;

            $itemsData = collect($request->items)->map(function ($item) use ($products, &$subtotal) {
                $product   = $products[$item['product_id']];
                $unitPrice = $product->sale_price ?? $product->price;
                $total     = $unitPrice * $item['qty'];
                $subtotal += $total;

                return [
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'product_sku'    => $product->sku,
                    'unit_price'     => $unitPrice,
                    'original_price' => $product->price,
                    'qty'            => $item['qty'],
                    'total'          => $total,
                ];
            });

            // 2. Apply coupon if provided
            $discountAmount = 0;
            $couponId       = null;
            $couponCode     = null;

            if ($request->filled('coupon_code')) {
                $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
                if ($coupon) {
                    $validity = $coupon->isValid($subtotal);
                    if ($validity['valid']) {
                        $discountAmount = $coupon->calculateDiscount($subtotal);
                        $couponId       = $coupon->id;
                        $couponCode     = $coupon->code;
                        $coupon->increment('used_count');
                    }
                }
            }

            $total = $subtotal - $discountAmount;

            // 3. Create order
            $customer = $request->customer;
            $order    = Order::create([
                'status'           => 'pending',
                'payment_status'   => 'pending',
                'customer_name'    => $customer['first_name'] . ' ' . $customer['last_name'],
                'customer_email'   => $customer['email'],
                'customer_phone'   => $customer['phone'] ?? null,
                'customer_address' => $customer['address'] ?? null,
                'customer_city'    => $customer['city'] ?? null,
                'subtotal'         => $subtotal,
                'discount_amount'  => $discountAmount,
                'total'            => $total,
                'coupon_code'      => $couponCode,
                'coupon_id'        => $couponId,
            ]);

            // 4. Create order items
            $order->items()->createMany($itemsData);

            // 5. Deduct stock
            $itemsData->each(function ($item) {
                Product::find($item['product_id'])->decrement('stock', $item['qty']);
            });

            // 6. Generate PayHere hash (MUST be server-side)
            $merchantId     = config('services.payhere.merchant_id');
            $merchantSecret = config('services.payhere.merchant_secret');
            $hash           = strtoupper(md5(
                $merchantId .
                $order->id .
                number_format($total, 2, '.', '') .
                'LKR' .
                strtoupper(md5($merchantSecret))
            ));

            return response()->json([
                'data' => [
                    'order_id' => $order->id,
                    'hash'     => $hash,
                    'total'    => $total,
                    'customer' => [
                        'firstName' => $customer['first_name'],
                        'lastName'  => $customer['last_name'],
                        'email'     => $customer['email'],
                        'phone'     => $customer['phone'] ?? '',
                        'address'   => $customer['address'] ?? '',
                        'city'      => $customer['city'] ?? 'Colombo',
                    ],
                ],
            ], 201);
        });
    }

    // ── POST /api/payhere/notify  (PayHere server-to-server webhook) ───────────
    public function payhereNotify(Request $request): Response
    {
        Log::info('PayHere notify', $request->all());

        $merchantId     = config('services.payhere.merchant_id');
        $merchantSecret = config('services.payhere.merchant_secret');

        $localHash = strtoupper(md5(
            $merchantId .
            $request->order_id .
            $request->payhere_amount .
            $request->payhere_currency .
            $request->status_code .
            strtoupper(md5($merchantSecret))
        ));

        if ($localHash !== $request->md5sig) {
            Log::warning('PayHere hash mismatch', ['order' => $request->order_id]);
            return response('Hash mismatch', 400);
        }

        $order = Order::find($request->order_id);
        if (!$order) {
            return response('Order not found', 404);
        }

        $statusCode = (int) $request->status_code;

        $order->update([
            'payhere_payment_id'  => $request->payment_id,
            'payhere_status_code' => $statusCode,
            'payhere_message'     => $request->status_message,
            'payhere_order_id'    => $request->order_id,
            'payment_status'      => match (true) {
                $statusCode === 2  => 'paid',
                $statusCode === -1 => 'failed',
                $statusCode === -2 => 'failed',
                default            => 'pending',
            },
            'status' => $statusCode === 2 ? 'processing' : $order->status,
        ]);

        return response('OK', 200);
    }

    // ── GET /api/orders/{id} ───────────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $order = Order::with('items.product')->findOrFail($id);
        return response()->json(['data' => $order]);
    }

    // ── GET /api/orders/{id}/invoice  (PDF download) ──────────────────────────
    public function invoice(int $id)
    {
        $order = Order::with('items.product')->findOrFail($id);
        return $this->invoiceService->download($order);
    }
}
