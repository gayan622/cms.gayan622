<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use App\Filament\Widgets\MovieAnalyticsOverview;

class Analytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.analytics';
    protected static ?string $navigationGroup = 'Reports';

    // Keeps properties for filter synchronization
    public $period = 'monthly';
    public $from;

    /**
     * Integrates widget directly into standard layout hooks
     */
    protected function getHeaderWidgets(): array
    {
        return [
            MovieAnalyticsOverview::class,
        ];
    }

    /**
     * Fallback mock for shop revenue (Replace logic if reading from an orders table)
     */
    public function getRevenueSummary(): array
    {
        return [
            'total_revenue' => 0.00,
            'total_orders' => 0,
            'total_discounts' => 0.00,
            'avg_order' => 0.00,
        ];
    }

    public function getChartData(): array
    {
        return [
            ['label' => 'No Data', 'revenue' => 0]
        ];
    }

    public function getTopProducts(): array
    {
        return [];
    }

    public function getStockReport(): array
    {
        return [];
    }

    /**
     * Live metrics for movie tracking summary components
     */
    public function getMovieViewsSummary(): array
    {
        return [
            'total_views' => DB::table('movie_views')->count(),
            'unique_viewers' => DB::table('movie_views')->distinct('ip_address')->count('ip_address'),
        ];
    }

    /**
     * Top-performing movie records grouped using valid SQL construction
     */
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
