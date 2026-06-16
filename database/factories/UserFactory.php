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
            'name' => $this->faker->firstName(),
            'l_name' => $this->faker->lastName(),
            'email' => $this->faker->companyEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('seller12345678'),
            'country_code' => '+44',
            'phone' => $this->faker->numerify('07#########'),
            'business_name' => $this->faker->company(),
            'business_phone' => $this->faker->numerify('07#########'),
            'business_hours' => null,
            'full_address' => $this->faker->address(),
            'unit_address' => $this->faker->secondaryAddress(),
            'country' => 'United Kingdom',
            'state' => 'England',
            'city' => 'London',
            'postcode' => $this->faker->postcode(),
            'lat' => $this->faker->latitude(51.3, 51.7), // London latitude range
            'lon' => $this->faker->longitude(-0.3, 0.2),  // London longitude range
            'bank_details' => null,
            'settings' => null,
            'user_img' => '365_Tool station_62f621c9edc33.png',
            'is_active' => 1,
            'is_online' => 0,
            'remember_token' => Str::random(10),
            'role_id' => UserRoleEnum::SELLER->value,
            'pending_withdraw' => 0.00,
            'total_withdraw' => 0.00,
            'parent_store_id' => null,
            'vehicle_type' => null,
            'application_fee' => 0.00,
            'temp_code' => null,
            'referral_code' => null,
            'stripe_account_id' => null,
            'last_login' => null,
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
                'name' => 'Test Company User',
                'password' => Hash::make('company12345678'),
                'role_id' => UserRoleEnum::COMPANY->value,
            ];
        });
    }
}
