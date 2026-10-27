<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieView extends Model
{
    protected $table = 'movie_views';

    protected $fillable = [
        'tmdb_id',
        'ip_address',
    ];

    /**
     * Relationship back to the Movie if needed later
     */
    public function movie()
    {
        return $this->belongsTo(Movie::class, 'tmdb_id', 'tmdb_id');
    }
}
