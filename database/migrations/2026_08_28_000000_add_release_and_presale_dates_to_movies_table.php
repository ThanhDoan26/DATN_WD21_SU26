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
        Schema::table('movies', function (Blueprint $table) {
            $table->dateTime('release_date')->nullable()->after('age_rating');
            $table->dateTime('presale_date')->nullable()->after('release_date');

            $table->index('release_date');
            $table->index('presale_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropIndex(['release_date']);
            $table->dropIndex(['presale_date']);
            $table->dropColumn(['release_date', 'presale_date']);
        });
    }
};
