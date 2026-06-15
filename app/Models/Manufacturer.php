<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Manufacturer extends Model
{
    protected $fillable = ['name', 'slug', 'logo', 'website', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($m) => $m->slug = $m->slug ?: Str::slug($m->name));
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
