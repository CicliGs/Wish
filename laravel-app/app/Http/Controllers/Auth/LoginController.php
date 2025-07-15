<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function showLoginForm(): \Illuminate\View\View
    {
        return view('login');
    }

    public function login(LoginRequest $request): \Illuminate\Http\RedirectResponse
    {
        $credentials = $request->validated();
        if ($this->userService->processLogin($credentials, $request, $request->boolean('remember'))) {
            return redirect()->intended(route('wish-lists.index'));
        }

        return back()->withErrors([
            'email' => 'Неверный email или пароль',
        ])->onlyInput('email');
    }

    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->userService->logout($request);

        return redirect('/');
    }
}
