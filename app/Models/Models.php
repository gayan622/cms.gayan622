<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProjectOld extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','slug','short_description','description','screenshot',
        'screenshots','live_url','technologies','category','client',
        'year','emoji','gradient','featured','is_active','sort_order',
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
        static::creating(fn($p) => $p->slug = $p->slug ?: Str::slug($p->title));
    }

    public function scopeActive($q)   { return $q->where('is_active', true); }
    public function scopeFeatured($q) { return $q->where('featured', true); }
}
