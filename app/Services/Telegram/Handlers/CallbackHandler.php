<?php

namespace App\Services\Telegram\Handlers;

use App\Models\Files\File;
use App\Models\Progress\UserProgress;
use App\Models\Structure\Subsection;
use Telegram\Bot\Api;
use App\Services\Section\SectionService;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Storage;

class CallbackHandler
{
    protected Api $telegram;
    protected UserService $userService;

    public function __construct(Api $telegram, UserService $userService)
    {
        $this->telegram = $telegram;
        $this->userService = $userService;
    }

    public function handle(array $message): void
    {
        $chatId = $message['from']['id'];
        $user = $this->userService->getOrCreateByChatId($chatId, $message['from']);

        if (!isset($message['document'])) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Iltimos fayl yuboring.",
            ]);
            return;
        }

        $fileInfo = $message['document'];
        $fileId = $fileInfo['file_id'];
        $fileName = $fileInfo['file_name'];

        $filePathData = $this->telegram->getFile(['file_id' => $fileId]);
        $filePath = $filePathData['file_path'];

        $telegramFileUrl = "https://api.telegram.org/file/bot" . config('telegram.bots.mybot.token') . "/" . $filePath;

        $savedPath = 'user_uploads/' . uniqid() . '_' . $fileName;
        Storage::disk('public')->put($savedPath, file_get_contents($telegramFileUrl));

        $progress = UserProgress::where('user_id', $user->id)->latest()->first();

        if (!$progress || !$progress->subsection) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Bo‘lim topilmadi. Iltimos menyudan qaytadan urinib ko‘ring.',
            ]);
            return;
        }

        $subsection = $progress->subsection;

        File::create([
            'user_id' => $user->id,
            'subsection_id' => $subsection->id,
            'path' => $savedPath,
        ]);

        $progress->update(['step' => 3]);

        // ProcessWithAI::dispatch($user, $subsection, $savedPath);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Faylingiz qabul qilindi. Endi keyingi bosqichga o'tamiz.",
        ]);

        $nextSubsection = Subsection::where('section_id', $subsection->section_id)
            ->where('id', '>', $subsection->id)
            ->orderBy('id')
            ->first();

        if ($nextSubsection) {
            UserProgress::create([
                'user_id' => $user->id,
                'subsection_id' => $nextSubsection->id,
                'step' => 0,
            ]);

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Keyingi bo‘lim: {$nextSubsection->name}.",
            ]);
        } else {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Tugadi. Bosh menyuga qaytishingiz mumkin.",
            ]);
        }
    }
}
