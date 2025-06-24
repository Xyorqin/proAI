<?php

namespace App\Services\AI;

use App\Models\AI\Prompt;
use App\Models\Files\File;

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
}
