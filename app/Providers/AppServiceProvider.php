<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        Gate::before(function ($user, $ability) {
            Gate::define($ability, function ($user) use ($ability) {
                if ($user->role->name == $ability) {
                    return true;
                }
                return false;
            });
        });

        Schema::defaultStringLength(191);
    }
}