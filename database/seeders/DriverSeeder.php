<?php

// namespace App\Database\Seeders;

use App\Drivers;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Drivers::factory()->count(5)->create();
    }
}
