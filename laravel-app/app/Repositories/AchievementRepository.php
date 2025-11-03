<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Models\UserAchievement;
use App\Repositories\Contracts\AchievementRepositoryInterface;

final class AchievementRepository implements AchievementRepositoryInterface
{
    public function createForUser(object $user, string $achievementKey): void
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }
        UserAchievement::create([
            'user_id' => $user->id,
            'achievement_key' => $achievementKey,
            'received_at' => now(),
        ]);
    }

    public function userHasAchievement(object $user, string $achievementKey): bool
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }
        return UserAchievement::where('user_id', $user->id)
            ->where('achievement_key', $achievementKey)
            ->exists();
    }
}

