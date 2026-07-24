<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('movies', 'format')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->string('format')->nullable()->after('age_rating');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('movies', 'format')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->dropColumn('format');
            });
        }
    }
};
