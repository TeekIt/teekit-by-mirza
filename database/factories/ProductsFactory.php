<?php

namespace Database\Factories;

use App\Enums\IsFeaturedEnum;
use App\Models\User;
use App\Models\Categories;
use App\Enums\UserRoleEnum;
use App\Enums\ProductStatusEnum;
use App\Models\Products;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Products>
 */
class ProductsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id' => User::inRandomOrder()->where('role_id', '=', UserRoleEnum::SELLER->value)->first()->id,
            'category_id' => Categories::inRandomOrder()->first()->id,

            'product_name' => $this->faker->word(),
            'sku' => strtoupper($this->faker->bothify('SKU-###')),
            'price' => $this->faker->randomFloat(2, 50, 500),

            'featured' => $this->faker->randomElement(IsFeaturedEnum::cases())->value,
            'discount_percentage' => $this->faker->randomElement(['5', '50']),

            'weight' => $this->faker->randomFloat(2, 1, 5),
            'brand' => $this->faker->company(),
            'size' => $this->faker->randomElement(['S', 'M', 'L']),

            'status' => ProductStatusEnum::ENABLE->value,
            'contact' => '03170122465',

            'colors' => json_encode([$this->faker->safeColorName()]),

            'bike' => $this->faker->boolean(),
            'car' => $this->faker->boolean(),
            'van' => 1,

            'feature_img' => 'https://teekit-production-bucket.lon1.digitaloceanspaces.com/251_60bc01afade5f.webp',

            'height' => $this->faker->randomFloat(2, 5, 15),
            'width' => $this->faker->randomFloat(2, 3, 10),
            'length' => $this->faker->randomFloat(2, 10, 30),
        ];
    }
}
