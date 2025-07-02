<?php

namespace App\Services\Telegram\Handlers;

use App\Services\Telegram\TelegramService;
use Telegram\Bot\FileUpload\InputFile;
use App\Models\Structure\Subsection;
use App\Services\User\UserService;
use App\Enums\UserStateLevelEnum;
use Telegram\Bot\Api;

class CallbackHandler
{

    public function __construct(
        protected Api $telegram,
        protected UserService $userService,
        protected TelegramService $telegramService
    ) {}

    public function handle(array $message): void
    {
        $chatId = $message['from']['id'];
        $user = $this->userService->getOrCreateByChatId($chatId, $message['from']);

        if ($message['data'] === 'back_to_menu') {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Asosiy menyuga qaytdingiz.",
            ]);
            $this->telegramService->showMainMenu($chatId, $user, true);
            return;
        }

        if ($user->state?->level == UserStateLevelEnum::MENU_LEVEL) {
            $this->sendSubSectionList($message, $user->id);
            return;
        }
        if ($user->state?->level == UserStateLevelEnum::SECTION_LEVEL) {
            $this->sendSubSectionDetails($message, $user->id);
            return;
        }
        if ($user->state?->level == UserStateLevelEnum::FILE_LEVEL) {
            $this->sendSubSectionList($message, $user->id);
            return;
        }
    }

    public function sendSubSectionList(array $message, int $userId): void
    {
        $section_id = explode('_', $message['data'])[1] ?? null;
        $subsections = Subsection::where('section_id', $section_id)->get();

        if ($subsections->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $message['from']['id'],
                'text' => "Bu bo‘limda hech qanday bo‘lim mavjud emas.",
            ]);
            return;
        }

        $keyboard = [];
        foreach ($subsections as $subsection) {
            $keyboard[] = [
                ['text' => $subsection->name, 'callback_data' => 'subsection_' . $subsection->id],
            ];
        }

        $keyboard[] = [
            [
                'text' => "⬅️ Orqaga",
                'callback_data' => 'back_to_menu'
            ]
        ];

        $this->telegram->editMessageText([
            'chat_id' => $message['from']['id'],
            'message_id' => $message['message']['message_id'],
            'text' => "Iltimos, bo‘lim tanlang:",
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
        $this->telegramService->updateState($userId, UserStateLevelEnum::SECTION_LEVEL);
    }

    public function sendSubSectionDetails($message, $userId)
    {
        $subsection_id = explode('_', $message['data'])[1] ?? null;

        $subsection = Subsection::find($subsection_id);

        if (!$subsection) {
            $this->telegram->sendMessage([
                'chat_id' => $message['from']['id'],
                'text' => "Bu bo‘lim topilmadi.",
            ]);
            return;
        }

        $files = $subsection->files;

        if (count($files)) {
            foreach ($files as $file) {
                if ($file->type === 'mp4')
                    $this->telegram->sendVideo([
                        'chat_id' => $message['from']['id'],
                        'video' => new InputFile(storage_path('app/public/' . $file->path)),
                        'caption' => $file->content ?? '',
                    ]);
                elseif ($file->type === 'pdf')
                    $this->telegram->sendDocument([
                        'chat_id' => $message['from']['id'],
                        'document' => new InputFile(storage_path('app/public/' . $file->path)),
                        'caption' => $file->content ?? '',
                    ]);
            }
        }
        if ($subsection->description) {
            $this->telegram->sendMessage([
                'chat_id' => $message['from']['id'],
                'text' => $subsection->description,
            ]);
        }

        $this->telegram->sendMessage([
            'chat_id' => $message['from']['id'],
            'text' => "Fayl yuboring 👇",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => '⬅️ Orqaga', 'callback_data' => 'section_' . $subsection->section_id]
                    ]
                ]
            ]),
        ]);
        $this->telegramService->updateState($userId, UserStateLevelEnum::FILE_LEVEL, subsection_id: $subsection_id);
    }
}
