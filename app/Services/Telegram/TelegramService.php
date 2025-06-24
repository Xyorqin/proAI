<?php

namespace App\Services\Telegram;

use App\Services\Telegram\Handlers\CallbackHandler;
use App\Services\Telegram\Handlers\DocumentHandler;
use App\Services\Telegram\Handlers\MessageHandler;
use Illuminate\Support\Facades\Log;

class TelegramService
{

    protected MessageHandler $messageHandler;
    protected CallbackHandler $callbackHandler;
    protected DocumentHandler $documentHandler;

    public function __construct(
        MessageHandler $messageHandler,
        CallbackHandler $callbackHandler,
        DocumentHandler $documentHandler
    ) {
        $this->messageHandler = $messageHandler;
        $this->callbackHandler = $callbackHandler;
        $this->documentHandler = $documentHandler;
    }

    /**
     * Handle incoming updates from Telegram.
     *
     * @param array $update
     * @return void
     */
    public function handleUpdate(array $update): void
    {
        // try {
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->handleCallback($update['callback_query']);
            } else {
                Log::warning('Unknown Telegram update type', ['update' => $update]);
            }
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
}
