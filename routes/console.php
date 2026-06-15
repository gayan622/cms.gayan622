<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Scheduled tasks ────────────────────────────────────────────────────────────

Schedule::call(function () {
    // Deactivate expired coupons automatically every hour
    \App\Models\Coupon::where('is_active', true)
        ->whereNotNull('expires_at')
        ->where('expires_at', '<', now())
        ->update(['is_active' => false]);
})->hourly()->name('deactivate-expired-coupons');

Schedule::call(function () {
    // Clean up old "pending" orders (older than 24h that were never paid)
    \App\Models\Order::where('payment_status', 'pending')
        ->where('created_at', '<', now()->subHours(24))
        ->each(function ($order) {
            // Restore stock
            foreach ($order->items as $item) {
                \App\Models\Product::find($item->product_id)?->increment('stock', $item->qty);
            }
            $order->update(['status' => 'cancelled']);
        });
})->dailyAt('02:00')->name('cancel-stale-orders');
