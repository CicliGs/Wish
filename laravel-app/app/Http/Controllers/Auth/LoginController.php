<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\View\View;

final class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected UserService $userService,
        private readonly StatefulGuard $auth
    ) {}

    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('login');
    }

    /**
     * Handle user login.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if ($this->auth->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('wish-lists.index'));
        }

        return back()->withErrors([
            'email' => __('messages.invalid_credentials'),
        ])->onlyInput('email');
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        $userId = $this->auth->id();

        if ($userId !== null) {
            $this->userService->clearUserCacheOnLogout((int) $userId);
        }

        $this->auth->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
 
}
