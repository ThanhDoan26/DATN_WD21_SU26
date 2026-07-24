<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$movies = App\Models\Movie::all();
foreach ($movies as $movie) {
    echo "Movie: {$movie->title} (ID: {$movie->id})\n";
    $cinemas = App\Models\Cinema::whereHas('rooms', function ($query) use ($movie) {
        $query->whereHas('showtimes', function ($q) use ($movie) {
            $q->where('movie_id', $movie->id)
              ->whereIn('status', [\App\Models\Showtime::STATUS_SCHEDULED, \App\Models\Showtime::STATUS_ONGOING])
              ->where('start_time', '>', now());
        });
    })->get();
    echo "Cinemas found: " . $cinemas->count() . "\n";
}
