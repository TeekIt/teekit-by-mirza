<?php

namespace Database\Seeders;

use App\Models\ReferralCodeRelation;
use Illuminate\Database\Seeder;

class ReferralCodeRelationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ReferralCodeRelation::factory()->count(5)->create();
    }
}
