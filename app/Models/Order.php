<?php
// ── Order ─────────────────────────────────────────────────────────────────────
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number', 'status', 'payment_status',
        'customer_name', 'customer_email', 'customer_phone',
        'customer_address', 'customer_city', 'customer_country',
        'subtotal', 'discount_amount', 'total', 'currency',
        'coupon_code', 'coupon_id',
        'payhere_order_id', 'payhere_payment_id', 'payhere_status_code', 'payhere_message',
        'notes',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total'           => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . date('Y') . '-' . str_pad(
                    Order::withTrashed()->count() + 1, 5, '0', STR_PAD_LEFT
                );
            }
        });
    }

    public function items(): HasMany        { return $this->hasMany(OrderItem::class); }
    public function coupon(): BelongsTo     { return $this->belongsTo(Coupon::class); }

    public function scopePaid($q)           { return $q->where('payment_status', 'paid'); }
    public function scopeByStatus($q, $s)   { return $q->where('status', $s); }
}
