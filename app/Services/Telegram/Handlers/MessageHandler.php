<?php

namespace App\Services\Telegram\Handlers;

use App\Models\Progress\UserProgress;
use App\Models\Structure\Section;
use App\Models\Structure\Subsection;
use App\Services\User\UserService;
use App\Services\Telegram\File\UploadedFileService;
use Telegram\Bot\Api;

class MessageHandler
{
    public function __construct(
        protected Api $telegram,
        protected UserService $userService,
        protected UploadedFileService $uploadedFileService,
    ) {}

    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        $user = $this->userService->getOrCreateByChatId($chatId, $message['from']);
loggeR($user);
        if ($text === '/start') {
            $this->resetProgress($user->id);
            $this->sendWelcome($chatId, $user->username);
            $this->showMainMenu($chatId);
            return;
        }

        $progress = UserProgress::where('user_id', $user->id)->latest()->first();

        if (!$progress) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Iltimos, bo‘lim tanlang:',
            ]);
            $this->showMainMenu($chatId);
            return;
        }

        $step = $progress->step;
        $subsection = $progress->subsection;

        match ($step) {
            0 => $this->sendInstruction($chatId, $subsection, $user->id),
            1 => $this->sendSample($chatId, $subsection, $user->id),
            2 => $this->handleFileUpload($chatId, $message, $user->id, $subsection),
            3 => $this->informResultIsProcessing($chatId),
            default => $this->showMainMenu($chatId),
        };
    }

    protected function sendWelcome(int $chatId, ?string $username): void
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Salom, {$username}! Proactive AI botiga xush kelibsiz.",
        ]);
    }

    protected function sendInstruction(int $chatId, Subsection $subsection, int $userId): void
    {
        $instruction = $subsection->files()->where('type', 'text')->first()?->content ?? 'Yo‘riqnoma mavjud emas.';
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $instruction,
        ]);

        $this->updateStep($userId, $subsection->id, 1);
    }

    protected function sendSample(int $chatId, Subsection $subsection, int $userId): void
    {
        $sample = $subsection->files()->whereIn('type', ['pdf', 'excel'])->first();
        if ($sample) {
            $this->telegram->sendDocument([
                'chat_id' => $chatId,
                'document' => $sample->file_path,
                'caption' => 'Namuna fayl',
            ]);
        }

        $this->updateStep($userId, $subsection->id, 2);
    }

    protected function handleFileUpload(int $chatId, array $message, int $userId, Subsection $subsection): void
    {
        $this->uploadedFileService->store($userId, $subsection->id, $message);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Faylingiz qabul qilindi",
        ]);

        $this->updateStep($userId, $subsection->id, 3);
    }

    protected function informResultIsProcessing(int $chatId): void
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'AI natijasi tayyor emas. Keyinroq qayta urinib ko‘ring yoki monitoring bo‘limidan tekshiring.',
        ]);
    }

    protected function updateStep(int $userId, int $subsectionId, int $nextStep): void
    {
        UserProgress::updateOrCreate(
            ['user_id' => $userId, 'subsection_id' => $subsectionId],
            ['step' => $nextStep, 'is_ready' => $nextStep >= 3]
        );
    }

    protected function resetProgress(int $userId): void
    {
        UserProgress::where('user_id', $userId)->delete();
    }

    public function showMainMenu(int $chatId): void
    {
        $buttons = Section::all()->map(fn($section) => [
            [
                'text' => $section->name,
                'callback_data' => 'section_' . $section->id,
            ]
        ])->toArray();

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Asosiy bo‘limni tanlang:',
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }
}
