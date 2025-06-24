<?php

namespace App\Services\Telegram\Handlers;

use Telegram\Bot\Api;
use App\Services\Section\SectionService;

class CallbackHandler
{
    protected Api $telegram;
    protected SectionService $sectionService;

    public function __construct(Api $telegram, SectionService $sectionService)
    {
        $this->telegram = $telegram;
        $this->sectionService = $sectionService;
    }

    public function handle(array $callback): void
    {
        $chatId = $callback['message']['chat']['id'];
        $data = $callback['data']; // Masalan: section_1, subsection_12

        if (str_starts_with($data, 'section_')) {
            $sectionId = str_replace('section_', '', $data);
            $this->sectionService->showSubsections($chatId, $sectionId);
            return;
        }

        if (str_starts_with($data, 'subsection_')) {
            $subId = str_replace('subsection_', '', $data);
            $this->sectionService->showSubsectionFiles($chatId, $subId);
            return;
        }

        $this->telegram->answerCallbackQuery([
            'callback_query_id' => $callback['id'],
            'text' => "Tugma noto‘g‘ri ishlamoqda",
            'show_alert' => true
        ]);
    }
}
