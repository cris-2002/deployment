<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $CategoryIds = Category::pluck('id')->toArray();
        $UserIds = User::pluck('id')->toArray();

        return [
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 1, 100), // Random price between 1 and 1000
            'category_id' => fake()->randomElement($CategoryIds),
            'calories' => fake()->numberBetween(50, 1000), // Random calories between 50 and 1000
            'images' => 'https://picsum.photos/100', // Random food image
            'stock' => fake()->numberBetween(0, 20), // Random stock between 0 and 100
            'sku' => fake()->unique()->bothify('??###'),
            'barcode' => fake()->unique()->numberBetween(1000000000, 9999999999),
            'created_by' => fake()->randomElement($UserIds),
            'updated_by' => fake()->randomElement($UserIds),
            'status' => fake()->randomElement(['draft', 'publish']), // Random status
        ];
    }
}
