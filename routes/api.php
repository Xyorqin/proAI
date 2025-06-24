<?php

use App\Http\Controllers\Telegram\TelegramController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::prefix('bot')->group(function () {
    Route::post('/webhook', [TelegramController::class, 'webhook']);
});
