<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('showtimes', 'deleted_at')) {
            Schema::table('showtimes', function (Blueprint $table) {
                $table->softDeletes()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('showtimes', 'deleted_at')) {
            Schema::table('showtimes', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
