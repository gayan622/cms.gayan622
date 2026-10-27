<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class MovieViewersReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-eye';

    // Matches the exact navigation group name from image_86b7cb.png
    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Movie Viewers';

    protected static string $view = 'filament.pages.movie-viewers-report';

    public array $topMovies = [];
    public array $recentViews = [];

    public function mount(): void
    {
        // 1. Get most watched movies grouped by TMDB ID
       $this->topMovies = DB::table('movie_views')
    ->join('movies', 'movie_views.tmdb_id', '=', 'movies.tmdb_id')
    ->select([
        'movies.tmdb_id',
        'movies.title',
        'movies.release_date',
        DB::raw('COUNT(*) as total_views'),
        DB::raw('COUNT(DISTINCT movie_views.ip_address) as unique_viewers')
    ])
    ->groupBy('movies.tmdb_id', 'movies.title', 'movies.release_date')
    ->orderByDesc('total_views')
    ->limit(50)
    ->get()
    ->toArray();

        // 2. Get recent live view activities
        $this->recentViews = DB::table('movie_views')
         ->join('movies', 'movie_views.tmdb_id', '=', 'movies.tmdb_id')
            ->select('movie_views.*', 'movies.title as movie_title')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->toArray();
    }
}
