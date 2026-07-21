<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/ai/chat', [ChatController::class, 'chat'])
        ->name('api.ai.chat');
});
