<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface AchievementRepositoryInterface
{
    /**
     * Create achievement for user
     */
    public function createForUser(object $user, string $achievementKey): void;

    /**
     * Check if user has achievement
     */
    public function userHasAchievement(object $user, string $achievementKey): bool;
}

