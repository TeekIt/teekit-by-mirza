<?php

namespace Database\Factories;

use App\Drivers;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverDocumentsFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'driver_id' => Drivers::inRandomOrder()->first()->id,
            'front_img' => $this->faker->imageUrl(),
            'back_image' => $this->faker->imageUrl(),
        ];
    }
}
