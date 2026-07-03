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
        if (!Schema::hasColumn('ticket_prices', 'status')) {
            Schema::table('ticket_prices', function (Blueprint $table) {
                $table->string('status')->default('ACTIVE')->after('price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_prices', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
