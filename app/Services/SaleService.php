<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Business logic penjualan.
 * Semua perubahan (header, detail, stok) berada dalam SATU database transaction.
 */
class SaleService
{
    /**
     * @param  array<int, array{product_id: mixed, quantity: mixed}>  $items
     */
    public function create(User $user, array $items): Sale
    {
        $lines = $this->mergeLines($items);

        if ($lines === []) {
            throw ValidationException::withMessages([
                'items' => 'Tambahkan minimal satu item penjualan.',
            ]);
        }

        return DB::transaction(function () use ($user, $lines) {
            $sale = Sale::create([
                'user_id' => $user->id,
                'sale_number' => $this->generateNumber(),
                'total' => 0,
                'status' => Sale::STATUS_COMPLETED,
                'sold_at' => now(),
            ]);

            $total = 0;

            foreach ($lines as $productId => $qty) {
                // lockForUpdate: baris produk dikunci sampai transaksi selesai,
                // sehingga dua penjualan bersamaan tidak membaca stok yang sama (cegah overselling).
                $product = Product::lockForUpdate()->find($productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => "Produk dengan ID {$productId} tidak ditemukan.",
                    ]);
                }

                if ($qty < 1) {
                    throw ValidationException::withMessages([
                        'items' => "Jumlah untuk {$product->name} harus lebih dari 0.",
                    ]);
                }

                if ($product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak cukup (tersedia {$product->stock}, diminta {$qty}).",
                    ]);
                }

                // Harga diambil dari DATABASE, bukan dari input browser.
                $unitPrice = (float) $product->selling_price;
                $subtotal = round($unitPrice * $qty, 2);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $qty);

                $total = round($total + $subtotal, 2);
            }

            $sale->update(['total' => $total]);

            return $sale;
        });
    }

    /**
     * Batalkan penjualan: stok dipulihkan, status menjadi 'cancelled'.
     */
    public function cancel(int $saleId): Sale
    {
        return DB::transaction(function () use ($saleId) {
            $sale = Sale::lockForUpdate()->findOrFail($saleId);

            if ($sale->status === Sale::STATUS_CANCELLED) {
                throw ValidationException::withMessages([
                    'sale' => 'Transaksi ini sudah dibatalkan sebelumnya.',
                ]);
            }

            $items = $sale->items()->orderBy('product_id')->get();

            foreach ($items as $item) {
                $product = Product::withTrashed()->lockForUpdate()->find($item->product_id);
                $product?->increment('stock', $item->quantity);
            }

            $sale->update(['status' => Sale::STATUS_CANCELLED]);

            return $sale;
        });
    }

    /**
     * Gabungkan baris dengan produk yang sama (jumlahnya dijumlahkan) agar
     * pengecekan stok akurat, lalu urutkan berdasarkan id produk (urutan lock konsisten).
     *
     * @return array<int, int> [product_id => quantity]
     */
    private function mergeLines(array $items): array
    {
        $lines = [];

        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 0);

            $lines[$productId] = ($lines[$productId] ?? 0) + $qty;
        }

        ksort($lines);

        return $lines;
    }

    private function generateNumber(): string
    {
        do {
            $number = 'SL-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Sale::where('sale_number', $number)->exists());

        return $number;
    }
}
