<?php

namespace App\Services\User;

use App\Models\User;

class UserService
{
    public function getOrCreateByChatId(int $chatId, array $from): User
    {
        return User::firstOrCreate(
            ['chat_id' => $chatId],
            [
                'username'   => $from['username'] ?? null,
                'password'       => bcrypt('123456'), // default password
            ]
        );
    }

    public function getByChatId(int $chatId): ?User
    {
        return User::where('chat_id', $chatId)->first();
    }

    public function isTrialActive(User $user): bool
    {
        return $user->created_at->diffInDays(now()) <= 5;
    }

    public function deductToken(User $user, int $amount = 1): void
    {
        $user->decrement('token_balance', $amount);
    }
}
