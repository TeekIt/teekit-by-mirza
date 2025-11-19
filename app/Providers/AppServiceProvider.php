<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Date::use(CarbonImmutable::class);

        Paginator::useBootstrap();

        Schema::defaultStringLength(191);

        Gate::before(function ($user, $ability) {
            Gate::define($ability, function ($user) use ($ability) {
                if ($user->role->name == $ability) {
                    return true;
                }

                return false;
            });
        });
    }
}
