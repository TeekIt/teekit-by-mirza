<?php

namespace Database\Factories;
use App\Models\Orders;
use App\Models\OrderItems;
use App\Models\Products;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryOrderItem>
 */
class InventoryOrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Orders::inRandomOrder()->value('id'),
            'order_item_id' => OrderItems::inRandomOrder()->value('id'),
            'product_id' => Products::inRandomOrder()->value('id'),
            'seller_id' => User::inRandomOrder()->value('id'),

            'van_id' => 2,

            'quantity' => $this->faker->numberBetween(1, 10),
            'price' => $this->faker->randomFloat(2, 100, 1000),

            'status' => 'pending',
        ];
    }
}
