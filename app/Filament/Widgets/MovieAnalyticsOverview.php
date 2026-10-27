<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MovieAnalyticsOverview extends BaseWidget
{

    protected static ?int $sort = -2; // ➕ Lower numbers push it to the top of the dashboard
    // Automatically polling-refresh data every 30 seconds for live updates
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // 1. Calculate main metrics
        $totalViews = DB::table('movie_views')->count();
        $uniqueViewers = DB::table('movie_views')->distinct('ip_address')->count('ip_address');

        // 2. Extract views from the past 24 hours to track velocity
        $viewsToday = DB::table('movie_views')
            ->where('created_at', '>=', Carbon::today())
            ->count();
        //->where('created_at', '>=', Carbon::now()->subDay(2))
        // 3. Gather sparkline trend points (last 7 days of raw tracking metrics)
        $sparklineData = DB::table('movie_views')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'ASC')
            ->pluck('total')
            ->toArray();

        return [
            Stat::make('Total Movie Views', number_format($totalViews))
                ->description('All-time anonymous stream clicks')
                ->descriptionIcon('heroicon-m-play')
                ->chart(!empty($sparklineData) ? $sparklineData : [0, 0])
                ->color('success'),

            Stat::make('Unique Streamers (IP Address)', number_format($uniqueViewers))
                ->description('Distinct client connections logged')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Views Past 24 Hours', number_format($viewsToday))
                ->description('Velocity metric of user streams')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($viewsToday > 0 ? 'warning' : 'gray'),
        ];
    }
}
