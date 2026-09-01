<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;
use App\Models\Showtime;
use App\Models\Cinema;
use App\Models\Room;

echo "--- MOVIES & SHOWTIMES ---\n";
$movies = Movie::all();
foreach ($movies as $movie) {
    $showtimes = Showtime::where('movie_id', $movie->id)->get();
    echo "Movie ID {$movie->id}: {$movie->title} (Showtimes count: " . $showtimes->count() . ")\n";
    foreach ($showtimes as $st) {
        echo "  - Showtime ID {$st->id}: start_time = {$st->start_time}, status = {$st->status}, room_id = {$st->room_id}\n";
    }
}

echo "\n--- CINEMAS & ROOMS ---\n";
$cinemas = Cinema::all();
foreach ($cinemas as $c) {
    echo "Cinema ID {$c->id}: {$c->name}\n";
    $rooms = Room::where('cinema_id', $c->id)->get();
    foreach ($rooms as $r) {
        echo "  - Room ID {$r->id}: {$r->name}\n";
    }
}
