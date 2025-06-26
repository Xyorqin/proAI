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

Route::prefix('admin')->group(function () {
    Route::resource('/sections', \App\Http\Controllers\Admin\SectionController::class);
    Route::resource('/subsections', \App\Http\Controllers\Admin\SubsectionController::class);
    Route::post('/subsection/{subsection}/upload-file', [\App\Http\Controllers\Admin\SubsectionController::class, 'uploadFile']);
    Route::post('/subsection/{subsection}/toggle-file', [\App\Http\Controllers\Admin\SubsectionController::class, 'toggleFile']);
});
