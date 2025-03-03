<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class EloquentRelationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::enforceMorphMap([
            'User' => \App\User::class,
            'GuestBuyer' => \App\Models\GuestBuyer::class,
            'Product' => \App\Products::class,
            'ProductsByBuyer' => \App\Models\ProductsByBuyer::class,
            'Order' => \App\Orders::class,
            'OrdersFromOtherSeller' => \App\Models\OrdersFromOtherSeller::class,
        ]);
    }
}
