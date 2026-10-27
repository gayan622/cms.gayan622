<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Movie extends Model
{
    protected $fillable = [
        'tmdb_id',
        'title',
        'overview',
        'poster_path',
        'backdrop_path',
        'release_date',
        'runtime',
        'vote_average',
        'vote_count',
        'popularity',
        'trailer_key',
        'video_url',
        'last_synced_at',
        'status',
    ];

    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'movie_actor')
            ->withPivot('character_name', 'order')
            ->withTimestamps();
    }



    public static function syncWithTmdb(int $tmdbId): ?self
{
    $apiKey = config('services.tmdb.key');

    if (!$apiKey) {
        Log::error('TMDB API Key is missing from config/services.php');
        return null;
    }

    try {
        $response = Http::withoutVerifying()->get("https://api.themoviedb.org/3/movie/{$tmdbId}", [
            'api_key' => $apiKey,
            'append_to_response' => 'videos,credits',
        ]);

        if ($response->failed()) {
            Log::error("TMDB API Error for ID {$tmdbId}: Status {$response->status()} - Body: " . $response->body());
            return null;
        }

        $data = $response->json();

        $videoUrl = null;
        if (!empty($data['videos']['results'])) {
            $trailer = collect($data['videos']['results'])->first(function ($video) {
                return $video['site'] === 'YouTube' && $video['type'] === 'Trailer';
            }) ?? $data['videos']['results'][0];

            if (isset($trailer['key'])) {
                $videoUrl = "https://www.youtube.com/watch?v={$trailer['key']}";
            }
        }

        // 1. Create or update the movie record
        $movie = self::updateOrCreate(
            ['tmdb_id' => $tmdbId],
            [
                'title'          => $data['title'] ?? 'Unknown Title',
                'overview'       => $data['overview'] ?? null,
                'release_date'   => !empty($data['release_date']) ? $data['release_date'] : null,
                'runtime'        => $data['runtime'] ?? null,
                'poster_path'    => $data['poster_path'] ?? null,
                'backdrop_path'  => $data['backdrop_path'] ?? null,
                'video_url'      => $videoUrl,
                'vote_average'   => $data['vote_average'] ?? 0,
                'vote_count'     => $data['vote_count'] ?? 0,
                'popularity'     => $data['popularity'] ?? 0,
                'status'         => 1,
                'last_synced_at' => now(),
            ]
        );

        // 2. Process and Sync Cast/Actors
        if (!empty($data['credits']['cast'])) {
            $syncData = [];

            // Limit to top 15 actors to save database space and optimize performance
            $castList = array_slice($data['credits']['cast'], 0, 15);

            foreach ($castList as $castMember) {
                if (empty($castMember['id']) || empty($castMember['name'])) continue;

                // Create or update the actor profile in the actors table
                $actor = \App\Models\Actor::updateOrCreate(
                    ['tmdb_id' => $castMember['id']],
                    [
                        'name'         => $castMember['name'],
                        'profile_path' => $castMember['profile_path'] ?? null,
                    ]
                );

                // Map pivot table properties using the relational auto-incrementing ID
                $syncData[$actor->id] = [
                    'character_name' => $castMember['character'] ?? '',
                    'order'          => $castMember['order'] ?? 0,
                ];
            }

            // Sync structural associations (removes old actors, adds new ones)
            $movie->actors()->sync($syncData);
        }

        return $movie;

    } catch (\Exception $e) {
        Log::error("TMDB Connection Exception: " . $e->getMessage());
        return null;
    }
}
}
