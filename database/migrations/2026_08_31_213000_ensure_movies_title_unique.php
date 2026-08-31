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
        if (Schema::hasTable('movies')) {
            $indexes = collect(Schema::getIndexes('movies'));
            $hasUniqueTitle = $indexes->contains(function ($index) {
                return in_array('title', $index['columns'] ?? []) && ($index['unique'] ?? false);
            });

            if (!$hasUniqueTitle) {
                $hasTitleIndex = $indexes->contains(function ($index) {
                    return $index['name'] === 'movies_title_index' || (in_array('title', $index['columns'] ?? []) && !($index['unique'] ?? false));
                });

                Schema::table('movies', function (Blueprint $table) use ($hasTitleIndex) {
                    if ($hasTitleIndex) {
                        $table->dropIndex('movies_title_index');
                    }
                    $table->unique('title');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('movies')) {
            $indexes = collect(Schema::getIndexes('movies'));
            $hasUniqueTitle = $indexes->contains(function ($index) {
                return in_array('title', $index['columns'] ?? []) && ($index['unique'] ?? false);
            });

            if ($hasUniqueTitle) {
                Schema::table('movies', function (Blueprint $table) {
                    $table->dropUnique(['title']);
                    $table->index('title');
                });
            }
        }
    }
};
