<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function showRegistrationForm(): View
    {
        return view('register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $this->userService->registerUser($request->validated());
        
        return redirect()->route('wish-lists.index');
    }
}
