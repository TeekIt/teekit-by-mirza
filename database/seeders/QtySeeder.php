<?php

use App\Qty;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class QtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        foreach (range(1, 20) as $index) {
            Qty::create([
                'users_id' => $faker->numberBetween(1, 5000000),
                'products_id' => $faker->numberBetween(1, 1000000),
                'qty' => $faker->randomDigit(),
            ]);
        }
    }
}
