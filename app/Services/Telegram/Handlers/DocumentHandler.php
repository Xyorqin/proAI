<?php

namespace App\Services\Telegram\Handlers;

use App\Enums\UserStateLevelEnum;
use Telegram\Bot\Api;
use Illuminate\Support\Facades\Storage;
use App\Services\AI\PromptService;
use App\Models\Files\File;
use App\Models\Structure\Section;
use App\Models\UserState;
use App\Services\Telegram\Progress\UserProgressService;
use App\Services\User\UserService;

class DocumentHandler
{
    protected Api $telegram;
    protected PromptService $promptService;
    protected UserProgressService $progressService;
    protected UserService $userService;

    public function __construct(Api $telegram, PromptService $promptService, UserProgressService $progressService, UserService $userService)
    {
        $this->telegram = $telegram;
        $this->promptService = $promptService;
        $this->progressService = $progressService;
        $this->userService = $userService;
    }

    public function handle(array $message): void
    {
        $chatId = $message['from']['id'];
        $document = $message['document'];
        $fileId = $document['file_id'];
        $file = $this->telegram->getFile(['file_id' => $fileId]);
        $filePath = $file->file_path;

        $localPath = Storage::put("uploads/{$fileId}.pdf", file_get_contents("https://api.telegram.org/file/bot" . config('telegram.bots.mybot.token') . "/{$filePath}"));

        $user = $this->userService->getOrCreateByChatId($chatId, $message['from']);

        $subsection_id = $user->state?->subsection_id ?? null;

       
        File::create([
            'user_id' => $user->id,
            'subsection_id' => $subsection_id, 
            'path' => $localPath,
        ]);

        $this->progressService->addProgress($user->id, $subsection_id);
        // $this->promptService->storePromptContext($chatId, $localPath);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ Faylingiz qabul qilindi va mentor kontekstiga qo‘shildi.",
        ]);

        $this->resetState($user->id);
        $this->showMainMenu($chatId, $message);
    }

    protected function resetState(int $userId): void
    {
        UserState::where('user_id', $userId)->delete();

        UserState::updateOrCreate(
            ['user_id' => $userId],
            ['level' => UserStateLevelEnum::MENU_LEVEL, 'step' => 0]
        );
    }

    /**
     * Show main menu with section buttons and progress info.
     *
     * @param int $chatId
     */
    public function showMainMenu(int $chatId, $message): void
    {
        $user = $this->userService->getOrCreateByChatId($chatId, $message['from']);

        $sections = Section::with('subsections')->get();

        $buttons = $sections->map(function ($section) use ($user) {
            $totalSubsections = $section->subsections->count();

            $doneSubsections = $user->progress()
                ->whereIn('subsection_id', $section->subsections->pluck('id'))
                ->count();

            $text = "{$section->name}  ({$doneSubsections}/{$totalSubsections})";

            return [[
                'text' => $text,
                'callback_data' => 'section_' . $section->id,
            ]];
        })->toArray();

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Asosiy bo‘limni tanlang:',
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }
}
