<?php

namespace Database\Seeders;

use App\Models\DriverDocument;
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
        DriverDocument::factory()->count(5)->create();
    }
}
