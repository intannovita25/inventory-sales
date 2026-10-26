<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // [sku, nama, kategori, harga beli, harga jual, stok awal, stok minimum]
        $products = [
            ['MNM-001', 'Air Mineral 600ml', 'Minuman', 2000, 3500, 120, 24],
            ['MNM-002', 'Teh Botol 450ml', 'Minuman', 3500, 5500, 80, 20],
            ['MNM-003', 'Kopi Sachet Hitam', 'Minuman', 1200, 2000, 200, 50],
            ['SNK-001', 'Keripik Singkong Balado', 'Makanan Ringan', 6000, 9000, 60, 15],
            ['SNK-002', 'Biskuit Cokelat 130g', 'Makanan Ringan', 7500, 11000, 45, 10],
            ['SNK-003', 'Wafer Vanila', 'Makanan Ringan', 4000, 6500, 8, 12], // stok rendah
            ['RMH-001', 'Sabun Cuci Piring 800ml', 'Kebutuhan Rumah', 14000, 19500, 30, 8],
            ['RMH-002', 'Deterjen Bubuk 1kg', 'Kebutuhan Rumah', 18000, 25000, 25, 8],
            ['RMH-003', 'Tisu Wajah 250 lembar', 'Kebutuhan Rumah', 9000, 13500, 5, 10], // stok rendah
            ['ATK-001', 'Pulpen Hitam 0.5mm', 'Alat Tulis', 2500, 4000, 150, 30],
            ['ATK-002', 'Buku Tulis 38 Lembar', 'Alat Tulis', 3500, 5500, 100, 25],
            ['ATK-003', 'Penghapus Putih', 'Alat Tulis', 1000, 2000, 90, 20],
            ['ELK-001', 'Baterai AA (isi 2)', 'Elektronik Kecil', 8000, 12500, 40, 10],
            ['ELK-002', 'Kabel USB Type-C 1m', 'Elektronik Kecil', 22000, 35000, 18, 5],
        ];

        $categories = Category::pluck('id', 'name');

        foreach ($products as [$sku, $name, $category, $buy, $sell, $stock, $min]) {
            Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => $name,
                    'category_id' => $categories[$category],
                    'purchase_price' => $buy,
                    'selling_price' => $sell,
                    'stock' => $stock,
                    'minimum_stock' => $min,
                ]
            );
        }
    }
}
