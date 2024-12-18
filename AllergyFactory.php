<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Allergy>
 */
class AllergyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $food_allergies = ['Milk', 'Eggs', 'Peanuts', 'Tree nuts', 'Soybeans', 'Wheat', 'Fish', 'Shellfish', 'Sesame'];

        return [
            'name' => fake()->unique()->randomElement($food_allergies),
        ];
    }
}
