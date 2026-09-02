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
        if (Schema::hasTable('cinemas')) {
            $indexes = collect(Schema::getIndexes('cinemas'));
            $hasUniqueName = $indexes->contains(function ($index) {
                return in_array('name', $index['columns'] ?? []) && ($index['unique'] ?? false);
            });

            if ($hasUniqueName) {
                Schema::table('cinemas', function (Blueprint $table) {
                    $table->dropUnique(['name']);
                    $table->index('name');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
