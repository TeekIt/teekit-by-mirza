<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Qty;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class QtyFactory extends Factory
{
   /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Qty::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id' => $this->faker->numberBetween(1, 5000000),
            'product_id' => $this->faker->numberBetween(1, 1000000),
            'category_id' => $this->faker->numberBetween(1, 100),
            'qty' => $this->faker->randomDigit(),
        ];
    }
}
