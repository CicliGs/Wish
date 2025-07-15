<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class UserService
{
    public function registerUser(array $data): User
    {
        return User::register($data);
    }

    public function processLogin(array $credentials, $request, bool $remember = false): bool
    {
        return User::tryLogin($credentials, $request, $remember);
    }

    public function logout($request): void
    {
        User::logout($request);
    }
}
