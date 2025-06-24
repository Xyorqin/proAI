<?php

namespace App\Services\Telegram\Handlers;

use Telegram\Bot\Api;
use Illuminate\Support\Facades\Storage;
use App\Services\AI\PromptService;
use App\Services\Progress\UserProgressService;
use App\Models\Files\File;

class DocumentHandler
{
    protected Api $telegram;
    protected PromptService $promptService;
    protected UserProgressService $progressService;

    public function __construct(Api $telegram, PromptService $promptService, UserProgressService $progressService)
    {
        $this->telegram = $telegram;
        $this->promptService = $promptService;
        $this->progressService = $progressService;
    }

    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];
        $document = $message['document'];

        $fileId = $document['file_id'];
        $file = $this->telegram->getFile(['file_id' => $fileId]);
        $filePath = $file->file_path;

        // Faylni saqlash
        $localPath = Storage::put("uploads/{$fileId}.pdf", file_get_contents("https://api.telegram.org/file/bot" . config('telegram.bots.mybot.token') . "/{$filePath}"));

        // Faylni bazaga yozish
        // File::create([
        //     'user_id' => auth()->id(), // yoki UserService orqali aniqlang
        //     'subsection_id' => session('current_subsection_id'), // vaqtincha
        //     'path' => $localPath,
        // ]);

        // Progress va AI logika
        // $this->progressService->markCompleted($chatId);
        $this->promptService->storePromptContext($chatId, $localPath);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ Faylingiz qabul qilindi va mentor kontekstiga qo‘shildi.",
        ]);
    }
}
