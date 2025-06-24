<?php

namespace App\Services\Section;

use App\Models\Structure\Section;
use App\Models\Structure\Subsection;
use Telegram\Bot\Api;

class SectionService
{
    protected Api $telegram;

    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    public function showSections(int $chatId): void
    {
        $sections = Section::all();

        $buttons = [];
        foreach ($sections as $section) {
            $buttons[] = [
                ['text' => $section->name, 'callback_data' => "section_{$section->id}"]
            ];
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Bo‘limni tanlang:',
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }

    public function showSubsections(int $chatId, int $sectionId): void
    {
        $subs = Subsection::where('section_id', $sectionId)->get();

        $buttons = [];
        foreach ($subs as $sub) {
            $buttons[] = [
                ['text' => $sub->name, 'callback_data' => "subsection_{$sub->id}"]
            ];
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Bosqichni tanlang:',
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }

    public function showSubsectionFiles(int $chatId, int $subId): void
    {
        $sub = Subsection::with('files')->findOrFail($subId);

        foreach ($sub->files as $file) {
            if ($file->type === 'text') {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $file->content,
                ]);
            } else {
                $this->telegram->sendDocument([
                    'chat_id' => $chatId,
                    'document' => $file->file_path,
                ]);
            }
        }

        // Reply uchun prompt
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Iltimos, faylni to‘ldirib qayta yuklang:',
            'reply_markup' => json_encode(['force_reply' => true])
        ]);
    }
}
