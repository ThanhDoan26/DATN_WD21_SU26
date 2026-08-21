<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cinema_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cinema_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');
            $table->tinyInteger('rating')->default(5);
            $table->text('comment')->nullable();
            $table->enum('status', ['ACTIVE', 'HIDDEN'])->default('HIDDEN');
            $table->timestamps();

            $table->unique(['user_id', 'cinema_id'], 'cinema_reviews_user_cinema_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cinema_reviews');
    }
};
