<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description');
            $table->longText('description')->nullable();
            $table->string('screenshot')->nullable();
            $table->json('screenshots')->nullable();        // multiple images
            $table->string('live_url')->nullable();
            $table->json('technologies')->nullable();       // ["Laravel","MySQL"]
            $table->string('category')->default('Web Project');
            $table->string('client')->nullable();
            $table->string('year')->nullable();
            $table->string('emoji')->default('🌐');
            $table->string('gradient')->default('from-violet-600 to-indigo-600');
            $table->boolean('featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
