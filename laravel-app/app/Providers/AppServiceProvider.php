<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WishListRepositoryInterface;
use App\Repositories\Contracts\WishRepositoryInterface;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use App\Repositories\Contracts\FriendRequestRepositoryInterface;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\AchievementRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\WishListRepository;
use App\Repositories\WishRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\FriendRequestRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\AchievementRepository;
use App\Models\User;
use App\Models\WishList;
use App\Models\Wish;
use App\Models\Reservation;
use App\Models\FriendRequest;
use App\Models\Notification;
use App\Exceptions\InvalidAuthGuardException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\ConnectionInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new UserRepository(
                $app->make(User::class),
                $app->make(ConnectionInterface::class),
                $app
            );
        });

        $this->app->bind(WishListRepositoryInterface::class, function ($app) {
            return new WishListRepository(
                $app->make(WishList::class),
                $app->make(UserRepositoryInterface::class),
                $app->make(WishRepositoryInterface::class)
            );
        });

        $this->app->bind(WishRepositoryInterface::class, function ($app) {
            return new WishRepository($app->make(Wish::class));
        });

        $this->app->bind(ReservationRepositoryInterface::class, function ($app) {
            return new ReservationRepository(
                $app->make(Reservation::class),
                $app->make(WishRepositoryInterface::class)
            );
        });

        $this->app->bind(FriendRequestRepositoryInterface::class, function ($app) {
            return new FriendRequestRepository($app->make(FriendRequest::class));
        });

        $this->app->bind(NotificationRepositoryInterface::class, function ($app) {
            return new NotificationRepository($app->make(Notification::class));
        });

        $this->app->bind(AchievementRepositoryInterface::class, function ($app) {
            return new AchievementRepository();
        });

        $this->app->bind(StatefulGuard::class, function ($app) {
            $guard = $app->make(AuthFactory::class)->guard();
            if (!$guard instanceof StatefulGuard) {
                throw new InvalidAuthGuardException();
            }
            return $guard;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(ViewFactory $view, Guard $auth, FriendRequestRepositoryInterface $friendRequestRepository): void
    {
        if (env('APP_ENV') === 'production') {
            \URL::forceScheme('https');
        }

        $view->composer('layouts.app', function ($view) use ($auth, $friendRequestRepository) {
            if ($auth->check()) {
                $user = $auth->user();
                if ($user) {
                    $incomingRequestsCount = $friendRequestRepository->countPendingIncomingForReceiver($user);
                    $view->with('incomingRequestsCount', $incomingRequestsCount);
                }
            }
        });
    }
}
