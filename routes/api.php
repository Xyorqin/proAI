<?php

use App\Http\Controllers\Telegram\TelegramController;
use App\Models\Files\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('bot')->group(function () {
    Route::post('/webhook', [TelegramController::class, 'webhook']);
});

Route::post('ask', [\App\Http\Controllers\AI\AIController::class, 'ask']);

Route::post('file', function (Request $request) {

});

Route::prefix('admin')->group(function () {
    Route::resource('/sections', \App\Http\Controllers\Admin\SectionController::class);
    Route::resource('/subsections', \App\Http\Controllers\Admin\SubsectionController::class);
    Route::post('/subsection/upload-file', [\App\Http\Controllers\Admin\SubsectionController::class, 'uploadFile']);
});
