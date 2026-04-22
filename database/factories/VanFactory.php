<?php

namespace Database\Factories;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Van>
 */
class VanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => User::inRandomOrder()->where('role_id', '=', UserRoleEnum::SUPERADMIN->value)->first()->id,
            // 'company_id' => User::inRandomOrder()->first()->id,
            'user_name' => $this->faker->unique()->bothify('van_#####'),
            'operative' => $this->faker->name(),
            'number_plate' => $this->faker->unique()->bothify('??##???'),
            'payload' => $this->faker->numberBetween(500, 5000),
            'width' => $this->faker->randomFloat(2, 1, 20),
            'height' => $this->faker->randomFloat(2, 1, 20),
            'length' => $this->faker->randomFloat(2, 1, 20),
            'password' => Hash::make('van12345678'),
        ];
    }
}
