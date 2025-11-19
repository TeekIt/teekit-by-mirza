<?php

namespace Database\Seeders;

use App\Models\Qty;
use Illuminate\Database\Seeder;

class QtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Qty::factory()->count(5)->create();
    }
}
