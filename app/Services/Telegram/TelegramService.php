<?php

namespace App\Services\Telegram;

use App\Enums\UserStateLevelEnum;
use App\Models\Progress\UserProgress;
use App\Models\Structure\Section;
use App\Models\User;
use App\Models\UserState;
use App\Services\Telegram\Handlers\CallbackHandler;
use App\Services\Telegram\Handlers\DocumentHandler;
use App\Services\Telegram\Handlers\MessageHandler;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class TelegramService
{
    // public function __construct(
    //     protected Api $telegram,
    //     protected MessageHandler $messageHandler,
    //     protected CallbackHandler $callbackHandler,
    //     protected DocumentHandler $documentHandler
    // ) {}

    /**
     * Handle incoming updates from Telegram.
     *
     * @param array $update
     */
    public function handleUpdate(array $update)
    {
        return response('OK', 200);
        // try {
        //     if (isset($update['message'])) {
        //         $this->handleMessage($update['message']);
        //     } elseif (isset($update['callback_query'])) {
        //         $this->handleCallback($update['callback_query']);
        //     } else {
        //         Log::warning('Unknown Telegram update type', ['update' => $update]);
        //     }
        // } catch (\Throwable $e) {
        //     Log::error('TelegramService error: ' . $e->getMessage(), [
        //         'trace' => $e->getTraceAsString(),
        //         'update' => $update,
        //     ]);
        // }
    }

    protected function handleMessage(array $message): void
    {
        if (isset($message['text'])) {
            $this->messageHandler->handle($message);
        } elseif (isset($message['document'])) {
            $this->documentHandler->handle($message);
        } else {
            Log::info('Unhandled message type', ['message' => $message]);
        }
    }

    protected function handleCallback(array $callback): void
    {
        $this->callbackHandler->handle($callback);
    }

    public function showMainMenu(int $chatId, $user, $resetState = false, $withProgress = false): void
    {
        if ($resetState) {
            UserState::where('user_id', $user->id)->delete();

            UserState::updateOrCreate(
                ['user_id' => $user->id],
                ['level' => UserStateLevelEnum::MENU_LEVEL, 'step' => 0]
            );
        }

        if ($withProgress) {
            $buttons = Section::with('subsections')->get()->map(function ($section) use ($user) {
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
        } else {
            $buttons = Section::with('subsections')->get()->map(fn($section) => [
                [
                    'text' => $section->name,
                    'callback_data' => 'section_' . $section->id,
                ]
            ])->toArray();
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Asosiy bo‘limni tanlang:',
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }

    public function updateState(int $userId, UserStateLevelEnum $level, ?int $subsection_id = null): void
    {
        UserState::updateOrCreate(
            ['user_id' => $userId],
            ['level' => $level, 'subsection_id' => $subsection_id]
        );
    }

    public function updateStep(int $userId, int $subsectionId, int $nextStep): void
    {
        UserProgress::updateOrCreate(
            ['user_id' => $userId, 'subsection_id' => $subsectionId],
            ['step' => $nextStep, 'is_ready' => $nextStep >= 3]
        );
    }
}
