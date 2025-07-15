<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Services\UserService;

class RegisterController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function showRegistrationForm(): \Illuminate\View\View
    {
        return view('register');
    }

    public function register(RegisterRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->userService->registerUser($request->validated());
        return redirect()->route('wishlists.index');
    }
}
