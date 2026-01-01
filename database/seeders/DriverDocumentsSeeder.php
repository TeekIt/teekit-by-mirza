<?php

namespace Database\Seeders;

use App\Models\DriverDocument;
use Illuminate\Database\Seeder;

class DriverDocumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DriverDocument::factory()->count(5)->create();
    }
}
