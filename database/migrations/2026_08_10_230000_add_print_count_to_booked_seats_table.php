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
        Schema::table('booked_seats', function (Blueprint $table) {
            $table->unsignedInteger('print_count')->default(0)->after('printed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booked_seats', function (Blueprint $table) {
            $table->dropColumn('print_count');
        });
    }
};
