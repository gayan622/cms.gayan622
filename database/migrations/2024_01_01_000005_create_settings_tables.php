<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page')->unique();   // home, about, blog, shop, contact
            $table->string('title');
            $table->text('description');
            $table->text('keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('ip_address', 45)->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_replied')->default(false);
            $table->text('reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('seo_settings');
    }
};
