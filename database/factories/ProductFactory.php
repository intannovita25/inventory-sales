<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-????-####')),
            'name' => fake()->unique()->words(3, true),
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'stock' => 10,
            'minimum_stock' => 5,
        ];
    }
}
