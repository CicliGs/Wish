<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\AchievementRepositoryInterface;

class UserService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected CacheManagerService $cacheManager,
        private readonly UserRepositoryInterface $userRepository,
        private readonly AchievementRepositoryInterface $achievementRepository
    ) {}

    /**
     * Register a new user and grant first achievement.
     */
    public function registerNewUser(array $data): User
    {
        $user = $this->userRepository->create($data);

        if (!($user instanceof User)) {
            throw new \RuntimeException('Failed to create user');
        }

        $this->grantRegistrationAchievement($user);

        return $user;
    }

    /**
     * Clear user cache on logout.
     */
    public function clearUserCacheOnLogout(int $userId): void
    {
        $this->cacheManager->clearUserCache($userId);
    }

    /**
     * Grant registration achievement to new user.
     */
    private function grantRegistrationAchievement(User $user): void
    {
        $this->achievementRepository->createForUser($user, 'register');
    }
}
