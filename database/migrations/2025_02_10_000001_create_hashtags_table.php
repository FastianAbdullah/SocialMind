<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // Store without # prefix for consistency
            $table->string('seed_hashtag')->nullable(); // Store the parent/seed hashtag
            $table->unsignedBigInteger('platform_id');
            $table->integer('count')->default(0);  // Store the count from API
            $table->boolean('is_seed')->default(false); // Flag to identify if it's a seed hashtag
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamps();

            $table->foreign('platform_id')->references('id')->on('platform');
            $table->unique(['name', 'platform_id', 'seed_hashtag']);
            $table->index(['platform_id', 'seed_hashtag', 'count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hashtags');
    }
}; 