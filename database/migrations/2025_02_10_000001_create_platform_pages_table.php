<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_platform_id');
            $table->string('page_id');  // Platform's unique identifier for the page
            $table->string('name');
            $table->string('type')->nullable(); // e.g., 'page', 'profile', 'business'
            $table->json('metadata')->nullable(); // Store additional page info
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_platform_id')->references('id')->on('user_platform');
            $table->unique(['user_platform_id', 'page_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_pages');
    }
}; 