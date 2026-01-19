<?php

use App\Http\Controllers\MainController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/connect', [MainController::class, 'connectToChatroom']);

Route::post('/send-message', [MainController::class, 'sendMessage']);
