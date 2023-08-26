<?php

// namespace Database\Seeders;

use App\DriverDocuments;
use Illuminate\Database\Seeder;

class DriverDocumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DriverDocuments::factory()->count(5)->create();
    }
}
