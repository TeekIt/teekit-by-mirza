<?php

namespace Database\Factories;

use App\Orders;
use App\Products;
use App\Qty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class OrderItemsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'order_id' => Orders::inRandomOrder()->first()->id,
            'product_id' => Products::inRandomOrder()->first()->id,
            'product_price' => Products::inRandomOrder()->first()->price,
            'product_qty' => Qty::inRandomOrder()->first()->qty,
            'user_choice' => $this->faker->numberBetween(1, 5)
        ];
    }
}
