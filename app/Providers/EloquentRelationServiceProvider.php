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
            'User' => 'App\User',
            'GuestBuyer' => 'App\Models\GuestBuyer',
            'Product' => 'App\Products',
            'ProductsByBuyer' => 'App\Models\ProductsByBuyer',
            'Order' => 'App\Orders',
            'OrdersFromOtherSeller' => 'App\Models\OrdersFromOtherSeller',
        ]);
    }
}
