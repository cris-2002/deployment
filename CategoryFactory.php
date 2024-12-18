<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $UserIds = User::pluck('id')->toArray();

        $category = [
            'Beverages',
            'Food',
            'Specials',
            'Desserts',
            'Healthy',
            'Combo Meals',
        ];

        return [
            'name' => fake()->unique()->randomElement($category),
            'created_by' => fake()->randomElement($UserIds),
            'updated_by' => fake()->randomElement($UserIds),
        ];
    }
}
