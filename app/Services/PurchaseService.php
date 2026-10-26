<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Business logic pembelian / stock-in.
 */
class PurchaseService
{
    /**
     * @param  array<int, array{product_id: mixed, quantity: mixed, unit_cost: mixed}>  $items
     */
    public function create(User $user, int $supplierId, array $items): Purchase
    {
        $lines = collect($items)
            ->map(fn ($item) => [
                'product_id' => (int) ($item['product_id'] ?? 0),
                'quantity' => (int) ($item['quantity'] ?? 0),
                'unit_cost' => (float) ($item['unit_cost'] ?? 0),
            ])
            ->sortBy('product_id')
            ->values();

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Tambahkan minimal satu item pembelian.',
            ]);
        }

        return DB::transaction(function () use ($user, $supplierId, $lines) {
            $purchase = Purchase::create([
                'supplier_id' => $supplierId,
                'user_id' => $user->id,
                'purchase_number' => $this->generateNumber(),
                'total' => 0,
                'status' => Purchase::STATUS_COMPLETED,
                'purchased_at' => now(),
            ]);

            $total = 0;

            foreach ($lines as $line) {
                $product = Product::lockForUpdate()->find($line['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => "Produk dengan ID {$line['product_id']} tidak ditemukan.",
                    ]);
                }

                if ($line['quantity'] < 1 || $line['unit_cost'] < 0) {
                    throw ValidationException::withMessages([
                        'items' => "Jumlah/harga untuk {$product->name} tidak valid.",
                    ]);
                }

                // Subtotal dihitung server.
                $subtotal = round($line['unit_cost'] * $line['quantity'], 2);

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $line['quantity'],
                    'unit_cost' => $line['unit_cost'],
                    'subtotal' => $subtotal,
                ]);

                $product->increment('stock', $line['quantity']);

                $total = round($total + $subtotal, 2);
            }

            $purchase->update(['total' => $total]);

            return $purchase;
        });
    }

    /**
     * Batalkan pembelian: stok dikurangi kembali. Ditolak jika stok sudah terpakai (akan negatif).
     */
    public function cancel(int $purchaseId): Purchase
    {
        return DB::transaction(function () use ($purchaseId) {
            $purchase = Purchase::lockForUpdate()->findOrFail($purchaseId);

            if ($purchase->status === Purchase::STATUS_CANCELLED) {
                throw ValidationException::withMessages([
                    'purchase' => 'Transaksi ini sudah dibatalkan sebelumnya.',
                ]);
            }

            // Total kuantitas per produk
            $perProduct = [];
            foreach ($purchase->items()->get() as $item) {
                $perProduct[$item->product_id] = ($perProduct[$item->product_id] ?? 0) + $item->quantity;
            }
            ksort($perProduct);

            foreach ($perProduct as $productId => $qty) {
                $product = Product::withTrashed()->lockForUpdate()->find($productId);

                if ($product && $product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'purchase' => "Pembelian tidak dapat dibatalkan: stok {$product->name} saat ini {$product->stock}, kurang dari {$qty} yang harus ditarik kembali.",
                    ]);
                }

                $product?->decrement('stock', $qty);
            }

            $purchase->update(['status' => Purchase::STATUS_CANCELLED]);

            return $purchase;
        });
    }

    private function generateNumber(): string
    {
        do {
            $number = 'PO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Purchase::where('purchase_number', $number)->exists());

        return $number;
    }
}
