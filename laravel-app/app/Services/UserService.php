<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Services\CacheManagerService;
use Illuminate\Support\Facades\Auth;

class UserService
{
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
     * Process user login.
     */
    public function authenticateUser(array $credentials, bool $remember = false): bool
    {
        if (Auth::attempt($credentials, $remember)) {
            request()->session()->regenerate();
            return true;
        }

        return false;
    }

    /**
     * Logout user with proper cleanup.
     */
    public function logoutUser(): void
    {
        if (Auth::user() instanceof User) {
            $this->cacheManager->clearUserCache(Auth::user()->id);
        }

        Auth::logout();
        
        request()->session()->regenerate();
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
