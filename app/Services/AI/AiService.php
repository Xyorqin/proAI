<?php

namespace App\Services\AI;

use OpenAI;

class AiService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openai.api_key'));
    }

    public function generateText(string $prompt, array $options = []): string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-4o', // gpt-3.5-turbo
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Hi there',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        return $response->choices[0]->message->content;
    }
}
