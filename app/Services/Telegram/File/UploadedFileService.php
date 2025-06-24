<?php

namespace App\Services\Telegram\File;

use App\Models\Files\File;

class UploadedFileService
{
    public function store(int $userId, int $subsectionId, array $message): void
    {
        $path = $this->extractFilePath($message); 
        File::create([
            'user_id' => $userId,
            'subsection_id' => $subsectionId,
            'path' => $path,
        ]);
    }

    protected function extractFilePath(array $message): string
    {
        return '/fake/path/from/telegram.ext';
    }
}
