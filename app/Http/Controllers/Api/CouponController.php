<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * POST /api/coupons/validate
     * Body: { code: string, order_total?: number }
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code'        => 'required|string|max:50',
            'order_total' => 'nullable|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return response()->json([
                'valid'   => false,
                'message' => 'Invalid coupon code.',
            ], 422);
        }

        $orderTotal = (float) ($request->order_total ?? 0);
        $result     = $coupon->isValid($orderTotal);

        if (!$result['valid']) {
            return response()->json($result, 422);
        }

        $discountAmount = $coupon->calculateDiscount($orderTotal);

        return response()->json([
            'valid'           => true,
            'message'         => 'Coupon applied successfully!',
            'data' => [
                'code'             => $coupon->code,
                'discount_type'    => $coupon->discount_type,
                'discount_value'   => $coupon->discount_value,
                'discount_percent' => $coupon->discount_percent,
                'discount_amount'  => $discountAmount,
                'description'      => $coupon->description,
            ],
        ]);
    }
}
