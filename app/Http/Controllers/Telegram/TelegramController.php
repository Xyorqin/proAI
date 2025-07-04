<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    // protected TelegramService $telegramService;

    // public function __construct(TelegramService $telegramService)
    // {
    //     $this->telegramService = $telegramService;
    // }

    public function webhook(Request $request)
    {
        return response('OK', 200);
        // try {
        // $this->telegramService->handleUpdate($request->all());
        // } catch (\Throwable $e) {
        //     Log::error('TelegramController Error: ' . $e->getMessage(), [
        //         'trace' => $e->getTraceAsString(),
        //         'request' => $request->all()
        //     ]);
        // }

        return response('OK', 200);
    }
}
