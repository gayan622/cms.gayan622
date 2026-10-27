<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Support\Facades\Http;

class MovieApiController extends Controller
{
    public function showOrSync($tmdb_id)
    {
        $movie = Movie::where('tmdb_id', $tmdb_id)->first();

        if ($movie) {
            return response()->json($movie);
        }

        $apiKey = env('TMDB_API_KEY');

        $response = Http::get("https://api.themoviedb.org/3/movie/{$tmdb_id}", [
            'api_key' => $apiKey,
            'append_to_response' => 'credits,videos,images',
        ]);

        if (!$response->successful()) {
            return response()->json([
                'message' => 'Movie not found on TMDB'
            ], 404);
        }

        $data = $response->json();

        $movie = Movie::create([
            'tmdb_id' => $data['id'],
            'title' => $data['title'] ?? null,
            'overview' => $data['overview'] ?? null,
            'poster_path' => $data['poster_path'] ?? null,
            'backdrop_path' => $data['backdrop_path'] ?? null,
            'release_date' => $data['release_date'] ?? null,
            'runtime' => $data['runtime'] ?? null,
            'vote_average' => $data['vote_average'] ?? 0,
            'vote_count' => $data['vote_count'] ?? 0,
            'popularity' => $data['popularity'] ?? 0,
            'status' => 1,
        ]);

        return response()->json($movie);
    }
}