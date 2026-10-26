<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Minuman', 'Makanan Ringan', 'Kebutuhan Rumah', 'Alat Tulis', 'Elektronik Kecil'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
