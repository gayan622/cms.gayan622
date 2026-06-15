<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();        // e.g. ORD-2025-00042
            $table->enum('status', [
                'pending', 'processing', 'paid', 'shipped', 'completed', 'cancelled', 'refunded'
            ])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');

            // Customer details (denormalised for invoice permanence)
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('customer_city')->nullable();
            $table->string('customer_country')->default('Sri Lanka');

            // Pricing
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('LKR');

            // Coupon
            $table->string('coupon_code')->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();

            // PayHere
            $table->string('payhere_order_id')->nullable()->unique();
            $table->string('payhere_payment_id')->nullable();
            $table->string('payhere_status_code')->nullable();
            $table->text('payhere_message')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Snapshot at time of purchase
            $table->string('product_name');
            $table->string('product_sku');
            $table->decimal('unit_price', 10, 2);           // actual price charged
            $table->decimal('original_price', 10, 2);       // before discount
            $table->unsignedInteger('qty');
            $table->decimal('total', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
