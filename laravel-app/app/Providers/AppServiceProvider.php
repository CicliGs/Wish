<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Wish;
use App\Observers\WishObserver;
use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Wish::observe(WishObserver::class);

        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                $incomingRequestsCount = Auth::user()->incomingRequests()->where('status', 'pending')->count();
                $view->with('incomingRequestsCount', $incomingRequestsCount);
            }
        });
    }
}
