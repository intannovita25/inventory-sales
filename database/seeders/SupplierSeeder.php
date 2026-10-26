<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'PT Sumber Makmur', 'phone' => '0812-1111-2222', 'email' => 'sales@sumbermakmur.test'],
            ['name' => 'CV Aneka Jaya', 'phone' => '0813-3333-4444', 'email' => 'order@anekajaya.test'],
            ['name' => 'UD Berkah Abadi', 'phone' => '0821-5555-6666', 'email' => 'info@berkahabadi.test'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['name' => $supplier['name']], $supplier);
        }
    }
}
