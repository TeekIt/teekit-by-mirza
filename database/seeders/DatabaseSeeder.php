<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use QtySeeder;
use Database\Seeders\ReferralCodeRelationSeeder;
use App\Database\Seeders\DriverSeeder;
use Database\Seeders\DriverDocumentsSeeder;

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
