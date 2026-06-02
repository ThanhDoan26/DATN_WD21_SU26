<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tạo roles trước users table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Tạo cinemas trước users table
        Schema::create('cinemas', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('address');
            $table->string('city');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();

            $table->index('city');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cinemas');
        Schema::dropIfExists('roles');
    }
};
