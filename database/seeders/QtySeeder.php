<?php

use App\Qty;
use Illuminate\Database\Seeder;

class QtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Qty::factory()->count(5)->create();
    }
}
