<?php

namespace App\Services\Telegram\Handlers;

use App\Models\Files\File;
use App\Models\Progress\UserProgress;
use App\Models\Structure\Section;
use App\Models\Structure\Subsection;
use Telegram\Bot\Api;
use App\Services\Section\SectionService;
use App\Services\User\UserService;
use App\UserStateLevelEnum;
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

        if ($message['data'] === 'back_to_menu') {
            $this->userService->updateState($user->id, UserStateLevelEnum::MENU_LEVEL);
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Asosiy menyuga qaytdingiz.",
            ]);
            $this->showMainMenu($chatId);
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
        $this->userService->updateState($userId, UserStateLevelEnum::SECTION_LEVEL);
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

        $file = $subsection->file;
        if ($file && $file->type === 'video') {
            $this->telegram->sendVideo([
                'chat_id' => $message['from']['id'],
                'video' => $file->path,
                'caption' => $subsection->context ?? '',
            ]);
            $this->telegram->sendFile([
                'chat_id' => $message['from']['id'],
                'document' => $file->path,
                'caption' => $subsection->context ?? '',
            ]);
            $this->userService->updateState($userId, UserStateLevelEnum::FILE_LEVEL);
        } else {
            $this->telegram->sendMessage([
                'chat_id' => $message['from']['id'],
                'text' => $subsection->context ?? 'Maʼlumot mavjud emas.',
            ]);
        }
    }

    // public function showMainMenu(int $chatId): void
    // {
    //     $user = $this->userService->getOrCreateByChatId($chatId);
    //     $progressSections = $user->progressSections()->pluck('section_id')->toArray();
    //     $sections = Section::all();

    //     $buttons = [];
    //     foreach ($sections as $section) {
    //         $emoji = in_array($section->id, $progressSections) ? '✅ ' : '';
    //         $buttons[] = [
    //             [
    //                 'text' => $emoji . $section->name,
    //                 'callback_data' => 'section_' . $section->id,
    //             ]
    //         ];
    //     }

    //     $this->telegram->sendMessage([
    //         'chat_id' => $chatId,
    //         'text' => 'Asosiy bo‘limni tanlang:',
    //         'reply_markup' => json_encode(['inline_keyboard' => $buttons])
    //     ]);
    // }
}
