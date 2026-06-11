<?php

namespace Database\Factories;

use App\Enums\IsFeaturedEnum;
use App\Enums\UserRoleEnum;
use App\Enums\VanProductStatusEnum;
use App\Enums\VanProductTypeEnum;
use App\Models\Categories;
use App\Models\User;
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
            'seller_id' => User::inRandomOrder()->where('role_id', '=', UserRoleEnum::SELLER->value)->first()->id,
            // 'company_id' => User::inRandomOrder()->where('role_id', '=', UserRoleEnum::COMPANY->value)->first()->id,
            'company_id' => 767,
            'category_id' => Categories::inRandomOrder()->first()->id,
            'van_id' => 1,

            'product_name' => $this->faker->word(),
            'sku' => strtoupper($this->faker->bothify('SKU-###')),
            'price' => $this->faker->randomFloat(2, 50, 500),

            'featured' => $this->faker->randomElement(IsFeaturedEnum::cases())->value,
            'discount_percentage' => $this->faker->randomElement([null, '5', '50']),

            'weight' => $this->faker->randomFloat(2, 1, 5),
            'brand' => $this->faker->company(),
            'size' => $this->faker->randomElement(['S', 'M', 'L']),

            'status' => $this->faker->randomElement(VanProductStatusEnum::cases())->value,
            'contact' => '3001234567',

            'colors' => json_encode([$this->faker->safeColorName()]),

            'bike' => $this->faker->boolean(),
            'car' => $this->faker->boolean(),
            'van' => 1,

            'feature_img' => 'https://teekit-production-bucket.lon1.digitaloceanspaces.com/251_60bc01afade5f.webp',

            'height' => $this->faker->numberBetween(5, 15),
            'width' => $this->faker->numberBetween(3, 10),
            'length' => $this->faker->numberBetween(10, 30),

            'job_reference' => strtoupper($this->faker->bothify('JOB###')),

            'quantity' => $this->faker->numberBetween(0, 100),
            'min_threshold' => 5,
            // 'type' => $this->faker->randomElement(VanProductTypeEnum::cases())->value,
            'type' => VanProductTypeEnum::PAY_AS_YOU_GO->value,
        ];
    }
}
