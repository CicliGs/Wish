<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Foundation\Application;

readonly class SetLocale
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        private Session     $session,
        private Application $app
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     *
     * @param Closure(Request): (Response|RedirectResponse|JsonResponse) $next
     *
     * @return Response|RedirectResponse|JsonResponse
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        $locale = $this->session->get('locale', 'ru');

        $this->app->setLocale($locale);

        return $next($request);
    }
}
