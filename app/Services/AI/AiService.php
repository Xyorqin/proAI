<?php

namespace App\Services\AI;

use App\Models\AI\UserConversation;
use OpenAI;

class AiService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openai.api_key'));
    }

    public function generateText(string $prompt, $user = null)
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo', // gpt-4o
            'messages' => [
                [
                    'role' => 'system',
                    'content' =>
                    "Siz foydalanuvchining marketing strategiyasi va biznes rivojlantirish bo‘yicha AI maslahatchisiz.
Sizga foydalanuvchi tomonidan bir nechta bo‘limlarga oid matnlar yuboriladi.
Har bir matn alohida bo‘lim/subsection sifatida belgilangan.
Siz quyidagilarni qilishingiz kerak:
1. Har bir bo‘lim/subsection bo‘yicha chuqur tahlil va tushunarli izoh bering.
2. Agar matn sifatli bo‘lsa, uni rivojlantirish bo‘yicha tavsiyalar bering.
3. Har bo‘limni alohida sarlavha qilib, unga aniq va qisqa javob yozing.
4. Hech qachon matnni chalkashtirmang, qaysi matn qaysi bo‘limga tegishli ekanini aniq ko‘rsating.
5. Javobingiz strukturali va professional bo‘lishi kerak.
6. Foydalanuvchi kontekstini inobatga olib shaxsiylashtirilgan maslahatlar bering.
",
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        if ($prompt and $response) {

            UserConversation::create([
                'user_id' => $user?->id,
                'prompt' => $prompt,
                'answer' => $response->choices[0]->message->content,
                'result' => json_encode($response),
            ]);
        }

        return $response->choices[0]->message->content;
    }
}
