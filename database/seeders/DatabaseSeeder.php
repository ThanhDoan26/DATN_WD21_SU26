<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserRoleSeeder::class,
            FullDemoDataSeeder::class,
            PostCategoryAndPostSeeder::class,
            MovieCinemaSeeder::class, // Added to seed Bookings as well
        ]);
    }
}
