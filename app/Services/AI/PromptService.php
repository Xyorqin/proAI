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
        $file = File::where('user_id', $userId)
            ->latest()
            ->first();


        if ($file && strtolower(pathinfo($file->path, PATHINFO_EXTENSION)) === 'pdf') {
            $parser = new Parser();
            $absolutePath = storage_path('app/' . $file->path);

            if (file_exists($absolutePath)) {
                $pdf = $parser->parseFile($absolutePath);
                $text = $pdf->getText();
            } else {
                // Handle missing file
                $text = 'File not found.';
            }
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
    }
}
