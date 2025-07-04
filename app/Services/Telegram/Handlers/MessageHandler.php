<?php

namespace App\Services\Telegram\Handlers;

use App\Services\Telegram\File\UploadedFileService;
use App\Services\Telegram\TelegramService;
use App\Models\Structure\Subsection;
use App\Services\User\UserService;
use Telegram\Bot\Api;

class MessageHandler
{
    public function __construct(
        protected Api $telegram,
        protected UserService $userService,
        protected UploadedFileService $uploadedFileService,
    ) {}

    public function handle(array $message, TelegramService $telegramService): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        $user = $this->userService->getOrCreateByChatId($chatId, $message['from']);

        if ($text === '/start') {
            $this->sendWelcome($chatId, $user->username);
            $telegramService->showMainMenu($chatId, $user, resetState: true);
            return;
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Iltimos, bo‘lim tanlang:',
        ]);
        $telegramService->showMainMenu($chatId, $user);
        return;
    }

    protected function sendWelcome(int $chatId, ?string $username): void
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Salom, {$username}! Proactive AI botiga xush kelibsiz.",
        ]);
    }

    protected function sendInstruction(int $chatId, Subsection $subsection, int $userId, TelegramService $telegramService): void
    {
        $instruction = $subsection->files()->where('type', 'text')->first()?->content ?? 'Yo‘riqnoma mavjud emas.';
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $instruction,
        ]);

        $telegramService->updateStep($userId, $subsection->id, 1);
    }

    protected function sendSample(int $chatId, Subsection $subsection, int $userId, TelegramService $telegramService): void
    {
        $sample = $subsection->files()->whereIn('type', ['pdf', 'excel'])->first();
        if ($sample) {
            $this->telegram->sendDocument([
                'chat_id' => $chatId,
                'document' => $sample->file_path,
                'caption' => 'Namuna fayl',
            ]);
        }

        $telegramService->updateStep($userId, $subsection->id, 2);
    }

    protected function handleFileUpload(int $chatId, array $message, int $userId, Subsection $subsection, TelegramService $telegramService): void
    {
        $this->uploadedFileService->store($userId, $subsection->id, $message);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Faylingiz qabul qilindi",
        ]);

        $telegramService->updateStep($userId, $subsection->id, 3);
    }
}
