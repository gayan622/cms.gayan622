<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'description', 'image', 'is_active', 'sort_order'];

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
