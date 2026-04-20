<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
          
        $this->call([
            UserSeeder::class,
            QtySeeder::class,
            ReferralCodeRelationSeeder::class,
            DriverSeeder::class,
            DriverDocumentsSeeder::class,
            OrdersFromOtherSellerSeeder::class,
            OrderItemsSeeder::class,
            GophrDeliverySeeder::class,
            VanProductSeeder::class,
            InventoryOrderItemSeeder::class,
        ]);
    }
}
