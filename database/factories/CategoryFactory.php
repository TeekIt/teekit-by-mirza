<?php

namespace Database\Factories;

use App\Models\Categories;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CategoryFactory extends Factory
{
    protected $model = Categories::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_name' => ucfirst($this->faker->unique()->word()),
            'category_image' => "https://teekit-production-bucket.lon1.digitaloceanspaces.com/Category_Spares_6707b7bf7e276.jpg",
        ];
    }
}
