<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tmdb_id')->unique();
            $table->string('title');
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable(); // ➕ Added field
            $table->date('release_date')->nullable();
            $table->integer('runtime')->nullable();       // ➕ Added field
            $table->decimal('vote_average', 3, 1)->nullable();
            $table->integer('vote_count')->default(0);    // ➕ Added field
            $table->decimal('popularity', 8, 3)->default(0); // ➕ Added field
            $table->string('trailer_key')->nullable();
            $table->string('video_url')->nullable();
            $table->tinyInteger('status')->default(1);    // ➕ Added field
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
