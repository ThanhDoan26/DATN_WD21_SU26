<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename existing column format to format_old
        Schema::table('movies', function (Blueprint $table) {
            $table->renameColumn('format', 'format_old');
        });

        // 2. Add format column as JSON
        Schema::table('movies', function (Blueprint $table) {
            $table->json('format')->nullable()->after('age_rating');
        });

        // 3. Migrate data from format_old to format
        $movies = DB::table('movies')->select('id', 'format_old')->get();
        foreach ($movies as $movie) {
            $oldFormat = $movie->format_old;
            $newFormat = [];

            if (!empty($oldFormat)) {
                $decoded = json_decode($oldFormat, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $newFormat = $decoded;
                } else {
                    $newFormat = array_map('trim', explode(',', $oldFormat));
                }
            }

            DB::table('movies')
                ->where('id', $movie->id)
                ->update(['format' => json_encode($newFormat)]);
        }

        // 4. Drop format_old column
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('format_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Rename format to format_old
        Schema::table('movies', function (Blueprint $table) {
            $table->renameColumn('format', 'format_old');
        });

        // 2. Add format column as string
        Schema::table('movies', function (Blueprint $table) {
            $table->string('format', 255)->nullable()->after('age_rating');
        });

        // 3. Migrate data back from JSON to comma-separated string
        $movies = DB::table('movies')->select('id', 'format_old')->get();
        foreach ($movies as $movie) {
            $oldFormat = $movie->format_old;
            $newStringFormat = null;

            if (!empty($oldFormat)) {
                $decoded = json_decode($oldFormat, true);
                if (is_array($decoded)) {
                    $newStringFormat = implode(', ', $decoded);
                } else {
                    $newStringFormat = $oldFormat;
                }
            }

            DB::table('movies')
                ->where('id', $movie->id)
                ->update(['format' => $newStringFormat]);
        }

        // 4. Drop format_old column
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('format_old');
        });
    }
};
