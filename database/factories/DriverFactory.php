<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'f_name' => $this->faker->firstName(),
            'l_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'password' => Hash::make('testdrivers'), 
            'profile_img' => null,
            'vehicle_type' => $this->faker->randomElement([1, 2, 3, 4]),
            'vehicle_number' => $this->faker->randomNumber(8),
            'area' => $this->faker->city(),
            'lat' => $this->faker->latitude(),
            'lon' => $this->faker->longitude(),
            'account_holders_name' => $this->faker->name(),
            'bank_name' => $this->faker->bank(),
            'sort_code' => $this->faker->randomNumber(8),
            'account_number' => $this->faker->randomNumber(10),
            'driving_licence_name' => $this->faker->name(),
            'dob' => $this->faker->date(),
            'driving_licence_number' => $this->faker->randomNumber(16),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
