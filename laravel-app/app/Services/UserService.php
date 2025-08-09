<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class UserService
{
    /**
     * Register a new user.
     */
    public function registerUser(array $data): User
    {
        return User::register($data);
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
}
