<?php

namespace Database\Factories;

use App\Products;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrdersFromOtherSeller>
 */
class OrdersFromOtherSellerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'customer_id' => User::inRandomOrder()->where('role_id', 3)->first()->id, // Generate random customer ID
            'seller_id' => User::inRandomOrder()->whereIn('role_id', [2, 5])->first()->id, // Generate random seller ID
            'product_id' => Products::inRandomOrder()->first()->id,
            'product_price' => Products::inRandomOrder()->first()->price,
            'product_qty' => fake()->numberBetween(1, 10),
            'order_total' => fake()->randomFloat(2, 10, 200), // Generate random order total between 10 and 200 with 2 decimal places
            'total_items' => fake()->numberBetween(1, 10), // Generate random number of items between 1 and 10
            'customer_lat' => fake()->latitude, // Generate random latitude
            'customer_lon' => fake()->longitude, // Generate random longitude
            'device' => fake()->randomElement(['iPhone', 'Android']), // Random device type
            'type' => fake()->randomElement(['delivery', 'self-pickup']), // Random order type
            'customer_name' => fake()->name, // Generate random customer name
            'phone_number' => fake()->phoneNumber, // Generate random phone number
            'address' => fake()->address, // Generate random address
            'house_no' => fake()->buildingNumber, // Generate random house number
            'flat' => fake()->optional()->word, // Random apartment number (nullable)
            'description' => 'Some description', // Generate random description (nullable)
            'payment_status' => fake()->randomElement(['paid', 'hidden']), // Random payment status
            'order_status' => fake()->randomElement(['pending', 'accepted', 'ready']), // Random order status
            'payment_intent' => null, // Set payment intent as null
            'driver_id' => null, // Set driver ID as null
            'driver_traveled_km' => 0.00, // Default driver traveled distance
            'driver_charges' => 0.00, // Default driver charges
            'driver_charges_cleared' => 0, // Default driver charges cleared flag
            'delivery_charges' => null, // Set delivery charges as null
            'service_charges' => null, // Set service charges as null
            'offloading' => fake()->boolean, // Random offloading flag (0 or 1)
            'offloading_charges' => fake()->randomFloat(2, 0, 10), // Random offloading charges
            'estimated_time' => null, // Set estimated time as null
            'is_viewed' => 0, // Default is_viewed flag
            'accepted' => 0, // Default accepted flag
            'times_rejected' => 0
        ];
    }
}
