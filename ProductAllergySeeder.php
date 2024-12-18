<?php

namespace Database\Seeders;

use App\Models\ProductAllergy;
use Illuminate\Database\Seeder;

class ProductAllergySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        ProductAllergy::factory(30)->create();
    }
}
