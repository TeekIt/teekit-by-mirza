<?php

namespace Database\Seeders;

use App\Models\ReferralCodeRelation;
use Illuminate\Database\Seeder;

class ReferralCodeRelationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ReferralCodeRelation::factory()->count(5)->create();
    }
}
