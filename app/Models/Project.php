<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'short_description', 'description',
        'screenshot', 'screenshots', 'live_url', 'technologies',
        'category', 'client', 'year', 'emoji', 'gradient',
        'featured', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'technologies' => 'array',
        'screenshots'  => 'array',
        'featured'     => 'boolean',
        'is_active'    => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });
    }

    public function scopeActive($query)   { return $query->where('is_active', true); }
    public function scopeFeatured($query) { return $query->where('featured', true); }
}
