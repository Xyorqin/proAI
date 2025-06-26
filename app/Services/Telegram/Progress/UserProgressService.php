<?php

namespace App\Services\Telegram\Progress;

use App\Models\Progress\UserProgress;

class UserProgressService
{
    public function markCompleted(int $userId, int $subsectionId): void
    {
        UserProgress::create([
            'user_id' => $userId,
            'subsection_id' => $subsectionId,
            'status' => 'completed',
        ]);
    }

    public function getCurrentStep(int $userId): ?int
    {
        return UserProgress::where('user_id', $userId)->latest()->value('subsection_id');
    }

    public function addProgress(int $userId, int $subsectionId): void
    {
        UserProgress::firstOrCreate([
            'user_id' => $userId,
            'subsection_id' => $subsectionId,
        ]);
    }
}
