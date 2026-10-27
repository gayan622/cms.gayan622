<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\DB;
use App\Filament\Widgets\MovieAnalyticsOverview;

class ReportsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Analytics';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view            = 'filament.pages.reports';

    public string $period = 'monthly';
    public ?string $from  = null;
    public ?string $until = null;

    public function mount(): void
    {
        $this->from  = Carbon::now()->subMonths(6)->format('Y-m-d');
        $this->until = Carbon::now()->format('Y-m-d');
    }

    /**
     * 🌟 Force hooks the Movie Overview cards widget right above the report layout
     */
    protected function getHeaderWidgets(): array
    {
        return [
            MovieAnalyticsOverview::class,
        ];
    }

    // ── Revenue summary ───────────────────────────────────────────────────────
    public function getRevenueSummary(): array
    {
        $from  = Carbon::parse($this->from);
        $until = Carbon::parse($this->until);

        $orders = Order::paid()->whereBetween('created_at', [$from, $until]);

        return [
            'total_revenue'   => round($orders->sum('total'), 2),
            'total_orders'    => $orders->count(),
            'total_discounts' => round($orders->sum('discount_amount'), 2),
            'avg_order'       => round($orders->avg('total') ?? 0, 2),
        ];
    }

    // ── Sales chart data ──────────────────────────────────────────────────────
    public function getSalesChartData(): array
    {
        $from  = Carbon::parse($this->from)->startOfDay();
        $until = Carbon::parse($this->until)->endOfDay();

        $timeGroup = match($this->period) {
            'daily'  => 'DATE(created_at)',
            'weekly' => 'YEARWEEK(created_at, 1)',
            default  => 'DATE_FORMAT(created_at, "%Y-%m")',
        };

        $labelExpr = match($this->period) {
            'daily'  => 'DATE_FORMAT(MIN(created_at), "%d %b")',
            'weekly' => 'DATE_FORMAT(MIN(created_at), "%d %b")',
            default  => 'DATE_FORMAT(MIN(created_at), "%b %Y")',
        };

        return Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $until])
            ->groupBy(DB::raw($timeGroup))
            ->selectRaw("
                {$labelExpr} as label,
                COUNT(*) as orders,
                ROUND(SUM(total), 2) as revenue
            ")
            ->orderBy(DB::raw($timeGroup), 'asc')
            ->get()
            ->toArray();
    }

    // ── Top products ──────────────────────────────────────────────────────────
    public function getTopProducts(): array
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [Carbon::parse($this->from), Carbon::parse($this->until)])
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->select([
                'order_items.product_name',
                DB::raw('SUM(order_items.qty) as total_sold'),
                DB::raw('ROUND(SUM(order_items.total), 2) as total_revenue'),
            ])
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->toArray();
    }

    // ── Stock report ──────────────────────────────────────────────────────────
    public function getStockReport(): array
    {
        return Product::with('category:id,name')
            ->select('id', 'name', 'sku', 'stock', 'low_stock_alert', 'price', 'category_id')
            ->orderBy('stock')
            ->limit(20)
            ->get()
            ->toArray();
    }

    // ── Chart Data Backup Fallback ────────────────────────────────────────────
    public function getChartData(): array
    {
        $query = Order::query()
            ->where('payment_status', 'paid')
            ->when($this->from, fn($q) => $q->where('created_at', '>=', $this->from));

        if ($this->period === 'daily') {
            $format = '%d %b';
            $sort = '%Y-%m-%d';
        } elseif ($this->period === 'weekly') {
            $format = 'Week %u';
            $sort = '%Y-%u';
        } else {
            $format = '%b %Y';
            $sort = '%Y-%m';
        }

        return $query
            ->select([
                DB::raw("DATE_FORMAT(created_at, '$format') as label"),
                DB::raw("SUM(total) as revenue"),
                DB::raw("DATE_FORMAT(created_at, '$sort') as sort_key"),
            ])
            ->groupBy('sort_key', 'label')
            ->orderBy('sort_key', 'asc')
            ->get()
            ->toArray();
    }

    // ── ➕ ADDED: MOVIE VIEW STATISTICS SUMMARY ───────────────────────────────
    public function getMovieViewsSummary(): array
    {
        return [
            'total_views' => DB::table('movie_views')->count(),
            'unique_viewers' => DB::table('movie_views')->distinct('ip_address')->count('ip_address'),
        ];
    }

    // ── ➕ ADDED: TOP STREAMED MOVIES REPORT ──────────────────────────────────
    public function getTopMovies(): array
    {
        return DB::table('movie_views')
            ->join('movies', 'movie_views.tmdb_id', '=', 'movies.tmdb_id')
            ->select([
                'movies.title',
                'movies.release_date',
                DB::raw('COUNT(movie_views.id) as total_views'),
                DB::raw('COUNT(DISTINCT movie_views.ip_address) as unique_ips')
            ])
            ->groupBy('movies.title', 'movies.release_date')
            ->orderBy('total_views', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }
}
