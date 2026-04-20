<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VanProduct>
 */
class VanProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
    
            'seller_id' => $this->faker->randomElement([13, 14, 15]),
            'category_id' => $this->faker->randomElement([2, 4, 6]),
            'van_id' => 2,

            'product_name' => $this->faker->word(),
            'sku' => strtoupper($this->faker->bothify('SKU-###')),
            'price' => $this->faker->randomFloat(2, 50, 500),

            'featured' => $this->faker->boolean(),
            'discount_percentage' => $this->faker->randomElement(['0%', '5%', '10%']),

            'weight' => $this->faker->randomFloat(2, 1, 5),
            'brand' => $this->faker->company(),
            'size' => $this->faker->randomElement(['S', 'M', 'L']),

            'status' => 'active',
            'contact' => '03001234567',

            'colors' => json_encode([$this->faker->safeColorName()]),

            'bike' => $this->faker->boolean(),
            'car' => $this->faker->boolean(),
            'van' => 1,

            'feature_img' => 'path/to/image.jpg',

            'height' => $this->faker->numberBetween(5, 15),
            'width' => $this->faker->numberBetween(3, 10),
            'length' => $this->faker->numberBetween(10, 30),

            'job_reference' => strtoupper($this->faker->bothify('JOB###')),

            'quantity' => $this->faker->numberBetween(0, 100),
            'min_threshold' => 50,
        ];
        
    }
}
