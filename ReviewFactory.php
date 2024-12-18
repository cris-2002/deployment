<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ProductIds = Product::pluck('id')->toArray();
        $UserIds = User::pluck('id')->toArray();

        return [
            'comment' => fake()->sentence(),
            'product_id' => fake()->randomElement($ProductIds),
            'status' => fake()->randomElement(['draft', 'publish']),
            'rating' => fake()->numberBetween(1, 5), // Random calories between 50 and 1000
            'user_id' => fake()->randomElement($UserIds),
        ];
    }
}
