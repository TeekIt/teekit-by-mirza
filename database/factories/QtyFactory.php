<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class QtyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'users_id' => $this->faker->numberBetween(1, 5000000),
            'products_id' => $this->faker->numberBetween(1, 1000000),
            'qty' => $this->faker->randomDigit()
        ];
    }
}