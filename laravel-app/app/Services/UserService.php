<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Services\CacheManagerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        Log::info('UserService: Registering new user', ['email' => $data['email'] ?? 'unknown']);
        
        $user = User::create($data);

        $this->grantRegistrationAchievement($user);

        Log::info('UserService: User registered successfully', ['user_id' => $user->id, 'email' => $user->email]);
        
        return $user;
    }

    /**
     * Process user login.
     */
    public function authenticateUser(array $credentials, bool $remember = false): bool
    {
        Log::info('UserService: Attempting user authentication', ['email' => $credentials['email'] ?? 'unknown']);
        
        if (Auth::attempt($credentials, $remember)) {
            request()->session()->regenerate();
            Log::info('UserService: User authenticated successfully', ['user_id' => Auth::id()]);
            return true;
        }

        Log::warning('UserService: Authentication failed', ['email' => $credentials['email'] ?? 'unknown']);
        return false;
    }

    /**
     * Logout user with proper cleanup.
     */
    public function logoutUser(): void
    {
        $userId = Auth::id();
        Log::info('UserService: Logging out user', ['user_id' => $userId]);
        
        if (Auth::user() instanceof User) {
            $this->cacheManager->clearUserCache(Auth::user()->id);
        }

        Auth::logout();
        
        request()->session()->regenerate();
        
        Log::info('UserService: User logged out successfully', ['user_id' => $userId]);
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
