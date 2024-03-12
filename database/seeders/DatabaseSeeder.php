<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use QtySeeder;
use DriverDocumentsSeeder;
use DriverSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            QtySeeder::class,
            ReferralCodeRelationSeeder::class,
            DriverSeeder::class,
            DriverDocumentsSeeder::class
        ]);
    }
}
