<?php

namespace Database\Factories;

use App\Models\Categories;
use App\Models\Products;
use App\Models\User;
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
    public function definition(): array
    {
        return [
            'seller_id' => User::inRandomOrder()->first()->id,
            'product_id' => Products::inRandomOrder()->first()->id,
            'category_id' => Categories::inRandomOrder()->first()->id,
            'qty' => $this->faker->randomDigit(),
        ];
    }
}
