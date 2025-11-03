<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\RegisterRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Routing\Controller as BaseController;

final class RegisterController extends BaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected UserService $userService) {}

    /**
     * Show the registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('register');
    }

    /**
     * Handle user registration.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $this->userService->registerNewUser($request->validated());
        
        return redirect()->route('wish-lists.index');
    }
}
