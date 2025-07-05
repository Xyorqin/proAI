<?php

namespace App\Services\AI;

use App\Models\AI\Prompt;
use App\Models\Files\File;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

class PromptService
{
    public function storePromptContext(int $userId, string $filePath): void
    {
        $file = File::where('user_id', $userId)
            ->where('path', $filePath)
            ->latest()
            ->first();

        Prompt::create([
            'user_id' => $userId,
            'file_id' => $file->id,
            'context_json' => json_encode([
                'file_path' => $file->path,
                'uploaded_at' => now(),
            ]),
        ]);
    }



    public function readFile(int $userId)
    {
        $files = File::where('user_id', $userId)
            ->with('subsection')
            ->get();

        $text = '';
        foreach ($files as $file) {

            $text .=  "\n" . $file->subsection?->section?->name . ":\n" .
                $file->subsection?->name . ":\n";

            if ($file && strtolower(pathinfo($file->path, PATHINFO_EXTENSION)) === 'pdf') {
                if ($file->prompt) {
                    $text .= $file->prompt->context . "\n";
                } else {
                    $parser = new Parser();
                    $pdf = $parser->parseFile(storage_path('app/private/' . $file->path));
                    $result_text = $pdf->getText();

                    Prompt::create([
                        'user_id' => $userId,
                        'file_id' => $file->id,
                        'context' => $result_text,
                    ]);

                    $text .= $result_text . "\n";
                }
            }

            if ($file && strtolower(pathinfo($file->path, PATHINFO_EXTENSION)) === 'docx') {
                if ($file->prompt) {
                    $text .= $file->prompt->context . "\n";
                } else {

                    $phpWord = IOFactory::load(storage_path('app/private/' . $file->path));
                    $text = '';
                    foreach ($phpWord->getSections() as $section) {
                        foreach ($section->getElements() as $element) {
                            if (method_exists($element, 'getText')) {
                                Prompt::create([
                                    'user_id' => $userId,
                                    'file_id' => $file->id,
                                    'context' => $element->getText(),
                                ]);

                                $text .= $element->getText() . "\n";
                            }
                        }
                    }
                }
            }
        }

        return $text ?? '';
    }
}
