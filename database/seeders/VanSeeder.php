<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Van;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Van::factory(30)->forCompany(User::find(767))->create();
    }
}
