<?php

namespace Database\Factories;

use App\Enums\OrderTypeEnum;
use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Orders>
 */
class OrdersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = $this->faker->randomFloat(2, 10, 500);

        return [
            'created_by_type' => User::class,
            'created_by_id' => User::where('role_id', UserRoleEnum::BUYER->value)->inRandomOrder()->first()?->id ??
                User::factory()->buyer()->create()->id,
            'seller_id' => User::where('role_id', UserRoleEnum::SELLER->value)->inRandomOrder()->first()?->id ??
                User::factory()->create()->id,
            'initial_total' => $total,
            'current_total' => $total,
            'total_items' => $this->faker->numberBetween(1, 10),
            'customer_lat' => $this->faker->latitude(),
            'customer_lon' => $this->faker->longitude(),
            'device' => $this->faker->randomElement(['iPhone', 'Android']),
            'type' => OrderTypeEnum::SAME_DAY_DELIVERY->value,
            'customer_name' => $this->faker->name(),
            'country_code' => $this->faker->countryCode(),
            'phone_number' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'house_no' => $this->faker->buildingNumber(),
            'flat' => $this->faker->randomElement(['A', 'B', 'C', null]),
            'country' => $this->faker->country(),
            'state' => $this->faker->state(),
            'city' => $this->faker->city(),
            'postcode' => $this->faker->postcode(),
            'description' => $this->faker->sentence(),
            'payment_status' => 'paid',
            'order_status' => $this->faker->randomElement(['pending', 'accepted', 'ready', 'stuartDelivery', 'onTheWay', 'delivered', 'complete', 'cancelled']),
            'delivery_status' => null,
            'payment_intent_id' => $this->faker->uuid(),
            'driver_id' => null,
            'driver_traveled_km' => $this->faker->randomFloat(2, 0, 100),
            'driver_charges' => $this->faker->randomFloat(2, 0, 50),
            'driver_charges_cleared' => $this->faker->randomElement([0, 1]),
            'delivery_charges' => $this->faker->randomFloat(2, 0, 20),
            'service_charges' => $this->faker->randomFloat(2, 0, 10),
            'offloading' => $this->faker->randomElement([0, 1, null]),
            'offloading_charges' => $this->faker->randomFloat(2, 0, 15),
            'is_viewed' => $this->faker->randomElement([0, 1]),
            'moved_at' => null,
        ];
    }
}
