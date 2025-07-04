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

Route::post('/webhook', [TelegramController::class, 'webhook']);
// Route::prefix('bot')->group(function () {
// });

Route::post('file', function (Request $request) {
    $file = File::latest()
        ->first();

    if ($file && strtolower(pathinfo($file->path, PATHINFO_EXTENSION)) === 'pdf') {
        $parser = new Parser();
        $pdf = $parser->parseFile(storage_path('app/' . $file->path));
        $text = $pdf->getText();
    }

    if ($file && strtolower(pathinfo($file->path, PATHINFO_EXTENSION)) === 'docx') {

        $phpWord = IOFactory::load(storage_path('app/' . $file->path));
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                }
            }
        }
    }

    return $text ?? '';
});

Route::prefix('admin')->group(function () {
    Route::resource('/sections', \App\Http\Controllers\Admin\SectionController::class);
    Route::resource('/subsections', \App\Http\Controllers\Admin\SubsectionController::class);
    Route::post('/subsection/upload-file', [\App\Http\Controllers\Admin\SubsectionController::class, 'uploadFile']);
});
