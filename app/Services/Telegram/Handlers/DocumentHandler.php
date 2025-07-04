<?php

namespace App\Services\Telegram\Handlers;

use App\Services\Telegram\Progress\UserProgressService;
use App\Services\Telegram\TelegramService;
use Illuminate\Support\Facades\Storage;
use App\Services\AI\PromptService;
use App\Services\User\UserService;
use App\Models\Files\File;
use Telegram\Bot\Api;

class DocumentHandler
{

    public function __construct(
        protected Api $telegram,
        protected PromptService $promptService,
        protected UserProgressService $progressService,
        protected UserService $userService,
    ) {
    }

    public function handle(array $message, TelegramService $telegramService): void
    {
        $chatId = $message['from']['id'];
        $document = $message['document'];
        $fileId = $document['file_id'];
        $file = $this->telegram->getFile(['file_id' => $fileId]);
        $filePath = $file->file_path;

        $fileContent = file_get_contents("https://api.telegram.org/file/bot" . config('telegram.bots.mybot.token') . "/{$filePath}");
        $relativePath = "uploads/{$fileId}.pdf";
        Storage::put($relativePath, $fileContent);

        $user = $this->userService->getOrCreateByChatId($chatId, $message['from']);

        $subsection_id = $user->state?->subsection_id ?? null;

        File::create([
            'user_id' => $user->id,
            'subsection_id' => $subsection_id,
            'path' => $relativePath,
        ]);

        $this->progressService->addProgress($user->id, $subsection_id);
        // $this->promptService->storePromptContext($chatId, $localPath);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ Faylingiz qabul qilindi va mentor kontekstiga qo‘shildi.",
        ]);

        $telegramService->showMainMenu($chatId, $user, resetState: true, withProgress: true);
    }
}
