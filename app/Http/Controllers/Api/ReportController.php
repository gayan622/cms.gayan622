<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // ── GET /api/reports/dashboard ─────────────────────────────────────────────
    public function dashboard(): JsonResponse
    {
        $today     = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $totalRevenue      = Order::paid()->sum('total');
        $revenueThisMonth  = Order::paid()->where('created_at', '>=', $thisMonth)->sum('total');
        $revenueLastMonth  = Order::paid()->whereBetween('created_at', [$lastMonth, $thisMonth])->sum('total');

        $totalOrders       = Order::paid()->count();
        $ordersThisMonth   = Order::paid()->where('created_at', '>=', $thisMonth)->count();
        $ordersToday       = Order::paid()->where('created_at', '>=', $today)->count();

        $pendingOrders     = Order::where('status', 'processing')->count();
        $lowStockProducts  = Product::lowStock()->count();
        $outOfStockProducts= Product::where('stock', 0)->active()->count();

        $revenueGrowth = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : 100;

        return response()->json([
            'data' => [
                'revenue' => [
                    'total'        => round($totalRevenue, 2),
                    'this_month'   => round($revenueThisMonth, 2),
                    'last_month'   => round($revenueLastMonth, 2),
                    'growth_pct'   => $revenueGrowth,
                ],
                'orders' => [
                    'total'        => $totalOrders,
                    'this_month'   => $ordersThisMonth,
                    'today'        => $ordersToday,
                    'pending'      => $pendingOrders,
                ],
                'stock' => [
                    'low_stock'    => $lowStockProducts,
                    'out_of_stock' => $outOfStockProducts,
                ],
            ],
        ]);
    }

    // ── GET /api/reports/sales ─────────────────────────────────────────────────
    // ?period=daily|weekly|monthly&from=&to=
    public function sales(Request $request): JsonResponse
    {
        $period = $request->get('period', 'monthly');
        $from   = $request->filled('from') ? Carbon::parse($request->from) : Carbon::now()->subMonths(6);
        $to     = $request->filled('to')   ? Carbon::parse($request->to)   : Carbon::now();

        $groupBy = match ($period) {
            'daily'   => DB::raw('DATE(created_at)'),
            'weekly'  => DB::raw('YEARWEEK(created_at, 1)'),
            default   => DB::raw('DATE_FORMAT(created_at, "%Y-%m")'),
        };

        $label = match ($period) {
            'daily'  => DB::raw('DATE_FORMAT(created_at, "%d %b")'),
            'weekly' => DB::raw('DATE_FORMAT(MIN(created_at), "%d %b")'),
            default  => DB::raw('DATE_FORMAT(created_at, "%b %Y")'),
        };

        $rows = Order::paid()
            ->whereBetween('created_at', [$from, $to])
            ->groupBy($groupBy)
            ->select([
                $label->as('label'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('SUM(discount_amount) as discounts'),
            ])
            ->orderBy(DB::raw('MIN(created_at)'))
            ->get();

        return response()->json(['data' => $rows]);
    }

    // ── GET /api/reports/top-products ─────────────────────────────────────────
    public function topProducts(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $products = OrderItem::with('product:id,name,slug,image,emoji')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->select([
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.qty) as total_sold'),
                DB::raw('SUM(order_items.total) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count'),
            ])
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        return response()->json(['data' => $products]);
    }

    // ── GET /api/reports/stock ─────────────────────────────────────────────────
    public function stock(Request $request): JsonResponse
    {
        $query = Product::with(['category:id,name', 'manufacturer:id,name'])
            ->select('id', 'name', 'sku', 'stock', 'low_stock_alert', 'price',
                     'sale_price', 'category_id', 'manufacturer_id', 'is_active');

        if ($request->get('status') === 'low') {
            $query->lowStock();
        } elseif ($request->get('status') === 'out') {
            $query->where('stock', 0);
        }

        $products = $query->orderBy('stock')->paginate(20);

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'total'     => $products->total(),
                'low_stock' => Product::lowStock()->count(),
                'out_stock' => Product::where('stock', 0)->count(),
            ],
        ]);
    }

    // ── GET /api/reports/coupons ───────────────────────────────────────────────
    public function coupons(): JsonResponse
    {
        $coupons = DB::table('coupons')
            ->select([
                'id', 'code', 'discount_type', 'discount_value',
                'used_count', 'max_uses', 'expires_at', 'is_active',
                DB::raw('(SELECT SUM(discount_amount) FROM orders WHERE coupon_code = coupons.code AND payment_status = "paid") as total_discount_given'),
            ])
            ->orderByDesc('used_count')
            ->get();

        return response()->json(['data' => $coupons]);
    }

    // ── GET /api/reports/movie-views ──────────────────────────────────────────
    // ?period=daily|weekly|monthly&from=&to=
    public function movieViews(Request $request): JsonResponse
    {
        $period = $request->get('period', 'monthly');
        $from   = $request->filled('from') ? Carbon::parse($request->from) : Carbon::now()->subMonths(6);
        $to     = $request->filled('to')   ? Carbon::parse($request->to)   : Carbon::now();

        // 1. Determine group and label configurations based on selected period
        $groupBy = match ($period) {
            'daily'   => DB::raw('DATE(created_at)'),
            'weekly'  => DB::raw('YEARWEEK(created_at, 1)'),
            default   => DB::raw('DATE_FORMAT(created_at, "%Y-%m")'),
        };

        $label = match ($period) {
            'daily'  => DB::raw('DATE_FORMAT(created_at, "%d %b")'),
            'weekly' => DB::raw('DATE_FORMAT(MIN(created_at), "%d %b")'),
            default  => DB::raw('DATE_FORMAT(created_at, "%b %Y")'),
        };

        // 2. Aggregate timeline trend data (Total views vs Unique IPs)
        $timelineData = DB::table('movie_views')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy($groupBy)
            ->select([
                $label->as('label'),
                DB::raw('COUNT(*) as total_views'),
                DB::raw('COUNT(DISTINCT ip_address) as unique_viewers'),
            ])
            ->orderBy(DB::raw('MIN(created_at)'))
            ->get();

        // 3. Extract top-performing movies by total streams
        $topMovies = DB::table('movie_views')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('tmdb_id')
            ->select([
                'tmdb_id',
                DB::raw('COUNT(*) as total_views'),
                DB::raw('COUNT(DISTINCT ip_address) as unique_viewers'),
            ])
            ->orderByDesc('total_views')
            ->limit($request->get('limit', 10))
            ->get();

        // 4. Global overview summary indicators for quick reading
        $totalViewsAllTime  = DB::table('movie_views')->count();
        $uniqueViewersAllTime = DB::table('movie_views')->distinct('ip_address')->count('ip_address');

        return response()->json([
            'data' => [
                'summary' => [
                    'all_time_total_views'    => $totalViewsAllTime,
                    'all_time_unique_viewers' => $uniqueViewersAllTime,
                ],
                'timeline'   => $timelineData,
                'top_movies' => $topMovies,
            ],
        ]);
    }
}
