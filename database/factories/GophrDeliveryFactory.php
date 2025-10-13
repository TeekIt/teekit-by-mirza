<?php

namespace Database\Factories;

use App\Models\Orders;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GophrDelivery>
 */
class GophrDeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'order_belongs_to_type' => (new Orders)->getMorphClass(),
            'order_belongs_to_id' => Orders::inRandomOrder()->first()->id,
            'job_id' => Str::uuid(),
        ];
    }
}
