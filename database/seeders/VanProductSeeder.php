<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\VanProduct;

class VanProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /* For Localhost */
        // VanProduct::factory(1000)->forCompany(User::find(767))->create();
        
        /* For Staging Server */
        VanProduct::factory(1000)->forCompany(
            User::inRandomOrder()->where('role_id', '=', UserRoleEnum::COMPANY->value)->first()
        )->create();
    }
}