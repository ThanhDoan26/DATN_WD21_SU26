<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

/*
|--------------------------------------------------------------------------
| AI & Chatbot Routes
|--------------------------------------------------------------------------
|
| Here is where you can register AI/Chatbot routes for your application.
| These routes are loaded by bootstrap/app.php under the 'api/chatbot' prefix.
|
*/

Route::get('/test', function () {
    return response()->json([
        'message' => 'Chatbot routes are working!',
        'status' => 'success'
    ]);
});

// Route::post('/send', [ChatController::class, 'sendMessage']);
// Route::get('/conversations', [ChatController::class, 'getConversations']);
