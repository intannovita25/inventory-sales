<?php

namespace Tests\Feature;

use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase, InteractsWithInventory;

    public function test_successful_sale_reduces_stock(): void
    {
        $staff = $this->makeStaff();
        $product = $this->makeProduct(['stock' => 10, 'selling_price' => 15000]);

        $this->actingAs($staff)
            ->post(route('sales.store'), [
                'items' => [['product_id' => $product->id, 'quantity' => 3]],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseCount('sale_items', 1);

        $sale = Sale::first();
        $this->assertSame($staff->id, $sale->user_id);
        $this->assertSame(Sale::STATUS_COMPLETED, $sale->status);
        $this->assertNotEmpty($sale->sale_number);
    }

    public function test_total_and_price_are_calculated_by_server_not_browser(): void
    {
        $product = $this->makeProduct(['stock' => 10, 'selling_price' => 15000]);

        $this->actingAs($this->makeStaff())
            ->post(route('sales.store'), [
                'total' => 1,
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_price' => 1,   // dikirim browser, harus diabaikan
                    'subtotal' => 1,
                ]],
            ])
            ->assertSessionHasNoErrors();

        $sale = Sale::with('items')->first();
        $this->assertEquals(45000, (float) $sale->total);
        $this->assertEquals(15000, (float) $sale->items->first()->unit_price);
        $this->assertEquals(45000, (float) $sale->items->first()->subtotal);
    }

    public function test_multiple_items_in_one_sale(): void
    {
        $a = $this->makeProduct(['stock' => 10, 'selling_price' => 1000]);
        $b = $this->makeProduct(['stock' => 10, 'selling_price' => 2500]);

        $this->actingAs($this->makeStaff())
            ->post(route('sales.store'), [
                'items' => [
                    ['product_id' => $a->id, 'quantity' => 2],
                    ['product_id' => $b->id, 'quantity' => 4],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertEquals(12000, (float) Sale::first()->total);
        $this->assertSame(8, $a->fresh()->stock);
        $this->assertSame(6, $b->fresh()->stock);
        $this->assertDatabaseCount('sale_items', 2);
    }

    public function test_sale_with_insufficient_stock_is_rejected_without_any_change(): void
    {
        $product = $this->makeProduct(['stock' => 4]);

        $this->actingAs($this->makeStaff())
            ->post(route('sales.store'), [
                'items' => [['product_id' => $product->id, 'quantity' => 6]],
            ])
            ->assertSessionHasErrors('items');

        $this->assertSame(4, $product->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
    }

    public function test_duplicate_lines_of_same_product_are_merged_before_stock_check(): void
    {
        $product = $this->makeProduct(['stock' => 5]);

        // 3 + 3 = 6 > 5 -> harus ditolak
        $this->actingAs($this->makeStaff())
            ->post(route('sales.store'), [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 3],
                    ['product_id' => $product->id, 'quantity' => 3],
                ],
            ])
            ->assertSessionHasErrors('items');

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_atomicity_if_one_item_fails_whole_transaction_is_rolled_back(): void
    {
        $a = $this->makeProduct(['stock' => 10]);
        $b = $this->makeProduct(['stock' => 1]); // id lebih besar -> diproses setelah A

        $this->actingAs($this->makeStaff())
            ->post(route('sales.store'), [
                'items' => [
                    ['product_id' => $a->id, 'quantity' => 2], // valid, sempat mengurangi stok
                    ['product_id' => $b->id, 'quantity' => 5], // gagal: stok tidak cukup
                ],
            ])
            ->assertSessionHasErrors('items');

        $this->assertSame(10, $a->fresh()->stock, 'Stok A harus kembali (rollback).');
        $this->assertSame(1, $b->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
    }

    public function test_zero_and_negative_quantity_are_rejected(): void
    {
        $product = $this->makeProduct(['stock' => 10]);
        $staff = $this->makeStaff();

        $this->actingAs($staff)
            ->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 0]]])
            ->assertSessionHasErrors('items.0.quantity');

        $this->actingAs($staff)
            ->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => -3]]])
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_sale_without_items_is_rejected(): void
    {
        $this->actingAs($this->makeStaff())
            ->post(route('sales.store'), ['items' => []])
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('sales', 0);
    }

    public function test_admin_can_cancel_sale_and_stock_is_restored(): void
    {
        $product = $this->makeProduct(['stock' => 10]);

        $this->actingAs($this->makeStaff())->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
        ]);
        $this->assertSame(6, $product->fresh()->stock);

        $sale = Sale::first();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('sales.cancel', $sale))->assertSessionHasNoErrors();

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(Sale::STATUS_CANCELLED, $sale->fresh()->status);

        // Pembatalan kedua tidak boleh menambah stok lagi
        $this->actingAs($admin)->post(route('sales.cancel', $sale))->assertSessionHasErrors('sale');
        $this->assertSame(10, $product->fresh()->stock);
    }
}
