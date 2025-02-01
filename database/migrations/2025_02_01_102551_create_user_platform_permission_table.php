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
        Schema::create('user_platform_permission', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_platform_id');
            $table->unsignedBigInteger('platform_permission_id');
            $table->unique(['user_platform_id', 'platform_permission_id'], 'user_platform_permission_unique');
            $table->boolean('granted')->default(false);
            $table->timestamps();

            $table->foreign('user_platform_id')->references('id')->on('user_platform');
            $table->foreign('platform_permission_id')->references('id')->on('platform_permission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_platform_permission');
    }
};
