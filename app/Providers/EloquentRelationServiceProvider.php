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
            'User' => \App\Models\User::class,
            'GuestBuyer' => \App\Models\GuestBuyer::class,
            'Product' => \App\Models\Products::class,
            'ProductsByBuyer' => \App\Models\ProductsByBuyer::class,
            'Order' => \App\Models\Orders::class,
            'OrdersFromOtherSeller' => \App\Models\OrdersFromOtherSeller::class,
        ]);
    }
}
