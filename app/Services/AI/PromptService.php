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

            $section = $file->subsection?->section?->name ?? '';
            $subsection = $file->subsection?->name ?? '';
            $allText = '';

            if ($file && strtolower(pathinfo($file->path, PATHINFO_EXTENSION)) === 'pdf') {
                if ($file->prompt) {
                    $text = $file->prompt->context;
                } else {
                    $parser = new Parser();
                    $pdf = $parser->parseFile(storage_path('app/private/' . $file->path));
                    $result_text = $pdf->getText();

                    Prompt::create([
                        'user_id' => $userId,
                        'file_id' => $file->id,
                        'context' => $result_text,
                    ]);

                    $text = $result_text;
                }
            }

            if ($file && strtolower(pathinfo($file->path, PATHINFO_EXTENSION)) === 'docx') {
                if ($file->prompt) {
                    $text   = $file->prompt->context;
                } else {

                    $phpWord = IOFactory::load(storage_path('app/private/' . $file->path));
                    $text = '';
                    foreach ($phpWord->getSections() as $section) {
                        foreach ($section->getElements() as $element) {
                            $text .= $this->extractTextFromElement($element);
                        }
                    }
                    Prompt::create([
                        'user_id' => $userId,
                        'file_id' => $file->id,
                        'context' => $text,
                    ]);
                }
            }

            $allText .= "=== Section: {$section} ===\n";
            $allText .= "=== Subsection: {$subsection} ===\n";
            $allText .= "{$text}\n\n";
        }

        return $allText ?? '';
    }

    private function extractTextFromElement($element)
    {
        $text = '';

        // Agar oddiy Text element bo‘lsa
        if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
            $text .= $element->getText() . "\n";
        }

        // Agar TextRun bo‘lsa
        if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
            foreach ($element->getElements() as $childElement) {
                $text .= $this->extractTextFromElement($childElement);
            }
        }

        // Agar Paragraph bo‘lsa
        if ($element instanceof \PhpOffice\PhpWord\Element\Paragraph) {
            foreach ($element->getElements() as $childElement) {
                $text .= $this->extractTextFromElement($childElement);
            }
            $text .= "\n";
        }

        // Agar Table bo‘lsa
        if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    foreach ($cell->getElements() as $cellElement) {
                        $text .= $this->extractTextFromElement($cellElement);
                    }
                    $text .= "\t";
                }
                $text .= "\n";
            }
        }

        return $text;
    }
}
