<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use App\Models\Movie;

class Actor extends Model
{
    //protected $fillable = ['tmdb_id', 'name', 'profile_path'];

    // public function movies()
    // {
    //     return $this->belongsToMany(Movie::class, 'movie_actor')
    //                 ->withPivot('character_name', 'order');
    // }

    protected $fillable = ['tmdb_id', 'name', 'profile_path'];

    public function movies()
{
    // Change 'actor_movie' back to 'movie_actor'
    return $this->belongsToMany(Movie::class, 'movie_actor')
                ->withPivot('character_name', 'order')
                ->withTimestamps();
}
}
