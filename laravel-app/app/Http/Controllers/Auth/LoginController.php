<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function showLoginForm(): View
    {
        return view('login');
    }

    public function login(LoginRequest $request):RedirectResponse
    {
        $credentials = $request->validated();
        if ($this->userService->authenticateUser($credentials, $request->boolean('remember'))) {
            return redirect()->intended(route('wish-lists.index'));
        }

        return back()->withErrors([
            'email' => __('messages.invalid_credentials'),
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->userService->logoutUser();

        return redirect('/');
    }
}
