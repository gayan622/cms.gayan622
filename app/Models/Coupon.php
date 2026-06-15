<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'discount_type', 'discount_value',
        'min_order', 'max_discount', 'max_uses', 'used_count',
        'per_user_limit', 'starts_at', 'expires_at', 'is_active', 'description',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order'      => 'decimal:2',
        'max_discount'   => 'decimal:2',
        'is_active'      => 'boolean',
        'starts_at'      => 'datetime',
        'expires_at'     => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ── Validation ─────────────────────────────────────────────────────────────

    public function isValid(float $orderTotal): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'This coupon is inactive.'];
        }
        if ($this->starts_at && Carbon::now()->lt($this->starts_at)) {
            return ['valid' => false, 'message' => 'This coupon is not active yet.'];
        }
        if ($this->expires_at && Carbon::now()->gt($this->expires_at)) {
            return ['valid' => false, 'message' => 'This coupon has expired.'];
        }
        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return ['valid' => false, 'message' => 'This coupon has reached its usage limit.'];
        }
        if ($orderTotal < $this->min_order) {
            return [
                'valid'   => false,
                'message' => 'Minimum order of LKR ' . number_format($this->min_order, 2) . ' required.',
            ];
        }
        return ['valid' => true, 'message' => 'Coupon applied!'];
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percent') {
            $discount = $subtotal * ($this->discount_value / 100);
            if ($this->max_discount) {
                $discount = min($discount, $this->max_discount);
            }
            return round($discount, 2);
        }
        return min($this->discount_value, $subtotal);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────
    public function getDiscountPercentAttribute(): int
    {
        return $this->discount_type === 'percent' ? (int) $this->discount_value : 0;
    }
}
