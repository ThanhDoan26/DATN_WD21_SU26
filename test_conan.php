<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo App\Models\Movie::where('title', 'like', '%Conan%')->first()->toJson(JSON_PRETTY_PRINT);
