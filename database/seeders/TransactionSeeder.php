<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Database\Seeder;

/**
 * Contoh transaksi agar dashboard & laporan tidak kosong.
 * Dibuat lewat Service yang sama dengan aplikasi, sehingga stok ikut konsisten.
 */
class TransactionSeeder extends Seeder
{
    public function run(PurchaseService $purchases, SaleService $sales): void
    {
        // Jangan menggandakan transaksi bila seeder dijalankan ulang.
        if (Purchase::exists() || Sale::exists()) {
            return;
        }

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $staff = User::where('email', 'staff@example.com')->firstOrFail();
        $supplier = Supplier::orderBy('id')->firstOrFail();

        $id = fn (string $sku) => Product::where('sku', $sku)->value('id');

        // Pembelian (stok bertambah)
        $purchases->create($admin, $supplier->id, [
            ['product_id' => $id('MNM-001'), 'quantity' => 48, 'unit_cost' => 2000],
            ['product_id' => $id('SNK-003'), 'quantity' => 2, 'unit_cost' => 4000],
        ]);

        $purchases->create($staff, $supplier->id, [
            ['product_id' => $id('ELK-002'), 'quantity' => 10, 'unit_cost' => 22000],
        ]);

        // Penjualan (stok berkurang)
        $sales->create($staff, [
            ['product_id' => $id('MNM-001'), 'quantity' => 6],
            ['product_id' => $id('SNK-001'), 'quantity' => 3],
            ['product_id' => $id('ATK-001'), 'quantity' => 10],
        ]);

        $sales->create($staff, [
            ['product_id' => $id('RMH-002'), 'quantity' => 2],
            ['product_id' => $id('RMH-001'), 'quantity' => 2],
        ]);

        $sales->create($admin, [
            ['product_id' => $id('ELK-001'), 'quantity' => 4],
        ]);
    }
}
