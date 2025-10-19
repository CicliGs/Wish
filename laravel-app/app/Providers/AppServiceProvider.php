<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\NotificationService;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WishListRepositoryInterface;
use App\Repositories\Contracts\WishRepositoryInterface;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\WishListRepository;
use App\Repositories\WishRepository;
use App\Repositories\ReservationRepository;
use App\Models\User;
use App\Models\WishList;
use App\Models\Wish;
use App\Models\Reservation;
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
        $this->app->singleton(NotificationService::class, function () {
            return new NotificationService();
        });

        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new UserRepository($app->make(User::class));
        });

        $this->app->bind(WishListRepositoryInterface::class, function ($app) {
            return new WishListRepository($app->make(WishList::class));
        });

        $this->app->bind(WishRepositoryInterface::class, function ($app) {
            return new WishRepository($app->make(Wish::class));
        });

        $this->app->bind(ReservationRepositoryInterface::class, function ($app) {
            return new ReservationRepository($app->make(Reservation::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                $incomingRequestsCount = Auth::user()->incomingRequests()->where('status', 'pending')->count();
                $view->with('incomingRequestsCount', $incomingRequestsCount);
            }
        });
    }
}
