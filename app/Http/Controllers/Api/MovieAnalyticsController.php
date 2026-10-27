<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MovieAnalyticsController extends Controller
{
    public function getViewStats()
    {
        // 1. Total overview metrics
        $totalViews = DB::table('movie_views')->count();
        $uniqueViewers = DB::table('movie_views')->distinct('ip_address')->count('ip_address');

        // Calculate average views per distinct machine IP
        $avgViewsPerUser = $uniqueViewers > 0 ? round($totalViews / $uniqueViewers, 1) : 0;

        // 2. Daily View Trends (Last 7 Days)
        $dailyTrends = DB::table('movie_views')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total_views'))
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'ASC')
            ->get()
            ->map(function($row) {
                return [
                    'day' => Carbon::parse($row->date)->format('M d'),
                    'views' => $row->total_views
                ];
            });

        // 3. Top Performing Movies Layout (Cross-referenced with titles)
        $topMovies = DB::table('movie_views')
            ->join('movies', 'movie_views.tmdb_id', '=', 'movies.tmdb_id')
            ->select(
                'movies.title',
                'movies.poster_path',
                'movie_views.tmdb_id',
                DB::raw('COUNT(*) as total_views'),
                DB::raw('COUNT(DISTINCT movie_views.ip_address) as unique_viewers')
            )
            ->groupBy('movies.title', 'movies.poster_path', 'movie_views.tmdb_id')
            ->orderBy('total_views', 'DESC')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_views' => $totalViews,
                'unique_users' => $uniqueViewers,
                'average_per_user' => $avgViewsPerUser
            ],
            'daily_trends' => $dailyTrends,
            'top_movies' => $topMovies
        ]);
    }
}
