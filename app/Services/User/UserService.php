<?php

namespace App\Services\User;

use App\Models\Progress\UserProgress;
use App\Models\User;
use App\Models\UserState;

class UserService
{
    public function getOrCreateByChatId(int $chatId, array $from): User
    {
        return User::query()->firstOrCreate(
            [
                'email' => $from['username'] . '@example.com',
            ],
            [
                'chat_id' => $chatId,
                'username' => $from['username'],
                'name' => $from['first_name'] . ' ' . ($from['last_name'] ?? ''),
                'password' => bcrypt('123456'), // default password
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

    public function updateState($userId, $level): void
    {
        UserState::updateOrCreate(
            ['user_id' => $userId],
            [
                'level' => $level,
                'step' => 0,
            ]
        );
    }
}
