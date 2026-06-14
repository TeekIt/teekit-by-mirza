<?php

namespace Database\Factories;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => UserRoleEnum::SELLER->value,
        ];
    }

    public function buyer(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'role_id' => UserRoleEnum::BUYER->value,
            ];
        });
    }
    public function company(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => 767,
                'name' => 'Test Company',
                'email' => 'company@test.com',
                'password' => Hash::make('password123'),
                'role_id' => UserRoleEnum::COMPANY->value,
                'business_name' => 'Test Company Ltd',
                'country' => 'United Kingdom',
                'state' => 'England',
                'city' => 'London',
                'user_img' => '365_Tool station_62f621c9edc33.png',
                'is_active' => 1,
            ];
        });
    }
}
