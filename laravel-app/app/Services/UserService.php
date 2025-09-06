<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class UserService
{
    /**
     * Register a new user and grant first achievement.
     */
    public function registerUser(array $data): User
    {
        $user = User::register($data);

        $this->grantRegistrationAchievement($user);

        return $user;
    }

    /**
     * Process user login.
     */
    public function processLogin(array $credentials, bool $remember = false): bool
    {
        return User::tryLogin($credentials, $remember);
    }

    /**
     * Logout user.
     */
    public function logout(): void
    {
        User::logout();
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
