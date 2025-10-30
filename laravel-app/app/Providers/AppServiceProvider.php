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
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\Auth\Guard;

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
    public function boot(ViewFactory $view, Guard $auth): void
    {
        $view->composer('layouts.app', function ($view) use ($auth) {
            if ($auth->check()) {
                $user = $auth->user();
                if ($user) {
                    $incomingRequestsCount = $user->incomingRequests()->where('status', 'pending')->count();
                    $view->with('incomingRequestsCount', $incomingRequestsCount);
                }
            }
        });
    }
}
