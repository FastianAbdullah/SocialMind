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
        Schema::create('media_attachment', function (Blueprint $table) {
            $table->id();
            // A single post can have multiple media attachments.
            $table->unsignedBigInteger('post_id');
            $table->string('media_type');
            $table->string('file_path');
            $table->string('Alt_text')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('post');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_attachment');
    }
};
