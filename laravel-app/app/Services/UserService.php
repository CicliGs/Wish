<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class UserService
{
    /**
     * Register a new user and grant first achievement.
     */
    public function registerUser(array $data): User
    {
        $user = User::register($data);
        
        // Автоматически выдаём достижение за регистрацию
        $this->grantRegistrationAchievement($user);
        
        return $user;
    }

    /**
     * Process user login.
     */
    public function processLogin(array $credentials, Request $request, bool $remember = false): bool
    {
        return User::tryLogin($credentials, $request, $remember);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): void
    {
        User::logout($request);
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
