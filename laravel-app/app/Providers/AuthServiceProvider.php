<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Wish;
use App\Models\WishList;
use App\Policies\WishPolicy;
use App\Policies\WishListPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        WishList::class => WishListPolicy::class,
        Wish::class => WishPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
