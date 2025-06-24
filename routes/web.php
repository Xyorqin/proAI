<?php

use App\Http\Controllers\Telegram\TelegramController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('bot')->group(function () {
    Route::post('/webhook', [TelegramController::class, 'webhook']);
});
