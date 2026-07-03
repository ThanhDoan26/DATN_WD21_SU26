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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('movie_id')->constrained('movies')->onDelete('cascade');
            $table->tinyInteger('rating')->comment('Rating from 1 to 5');
            $table->text('comment')->nullable();
            $table->enum('status', ['ACTIVE', 'HIDDEN'])->default('ACTIVE');
            $table->timestamps();

            // Mỗi người dùng chỉ được phép đánh giá một phim 1 lần
            $table->unique(['user_id', 'movie_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
