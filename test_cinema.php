<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cinemas = App\Models\Cinema::whereHas('rooms', function ($query) {
    $query->whereHas('showtimes', function ($q) {
        $q->whereIn('status', [\App\Models\Showtime::STATUS_SCHEDULED, \App\Models\Showtime::STATUS_ONGOING])
          ->where('start_time', '>', now());
    });
})->get();

echo "Count: " . $cinemas->count() . "\n";
foreach ($cinemas as $cinema) {
    echo $cinema->name . "\n";
}
