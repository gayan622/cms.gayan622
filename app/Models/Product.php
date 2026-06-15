<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'manufacturer_id', 'name', 'slug', 'sku',
        'short_description', 'description', 'price', 'sale_price',
        'discount_percent', 'image', 'images', 'stock', 'low_stock_alert',
        'featured', 'is_active', 'emoji', 'gradient', 'sort_order',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'sale_price'      => 'decimal:2',
        'discount_percent'=> 'integer',
        'stock'           => 'integer',
        'featured'        => 'boolean',
        'is_active'       => 'boolean',
        'images'          => 'array',
    ];

    // ── Auto-generate slug ─────────────────────────────────────────────────────
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = 'SKU-' . strtoupper(Str::random(8));
            }
            // Calculate discount_percent automatically
            if ($product->sale_price && $product->price > 0) {
                $product->discount_percent = round((($product->price - $product->sale_price) / $product->price) * 100);
            }
        });

        static::updating(function ($product) {
            if ($product->sale_price && $product->price > 0) {
                $product->discount_percent = round((($product->price - $product->sale_price) / $product->price) * 100);
            }
        });
    }

    // ── Accessors ──────────────────────────────────────────────────────────────
    public function getDisplayPriceAttribute(): float
    {
        return $this->sale_price ?? $this->price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->low_stock_alert && $this->stock > 0;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->stock === 0;
    }

    // ── Relationships ──────────────────────────────────────────────────────────
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'low_stock_alert')->where('stock', '>', 0);
    }
}
