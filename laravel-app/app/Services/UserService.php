<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Services\CacheManagerService;

class UserService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected CacheManagerService $cacheManager
    ) {}
    /**
     * Register a new user and grant first achievement.
     */
    public function registerNewUser(array $data): User
    {
        $user = User::create($data);

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
        $user->achievements()->create([
            'achievement_key' => 'register',
            'received_at' => now(),
        ]);
    }
}
