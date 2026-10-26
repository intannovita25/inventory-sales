<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase, InteractsWithInventory;

    public function test_successful_purchase_increases_stock(): void
    {
        $staff = $this->makeStaff();
        $supplier = Supplier::factory()->create();
        $product = $this->makeProduct(['stock' => 10]);

        $this->actingAs($staff)
            ->post(route('purchases.store'), [
                'supplier_id' => $supplier->id,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 12000],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(15, $product->fresh()->stock);
        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('purchase_items', 1);

        $purchase = Purchase::first();
        $this->assertEquals(60000, (float) $purchase->total);
        $this->assertSame($staff->id, $purchase->user_id);
        $this->assertNotEmpty($purchase->purchase_number);
        $this->assertSame(Purchase::STATUS_COMPLETED, $purchase->status);
    }

    public function test_purchase_with_multiple_items_sums_total_on_server(): void
    {
        $supplier = Supplier::factory()->create();
        $a = $this->makeProduct(['stock' => 0]);
        $b = $this->makeProduct(['stock' => 0]);

        $this->actingAs($this->makeStaff())
            ->post(route('purchases.store'), [
                'supplier_id' => $supplier->id,
                'items' => [
                    ['product_id' => $a->id, 'quantity' => 2, 'unit_cost' => 1000],
                    ['product_id' => $b->id, 'quantity' => 3, 'unit_cost' => 500],
                ],
                'total' => 1, // diabaikan server
            ])
            ->assertSessionHasNoErrors();

        $this->assertEquals(3500, (float) Purchase::first()->total);
        $this->assertSame(2, $a->fresh()->stock);
        $this->assertSame(3, $b->fresh()->stock);
    }

    public function test_zero_quantity_and_negative_cost_are_rejected(): void
    {
        $supplier = Supplier::factory()->create();
        $product = $this->makeProduct(['stock' => 10]);

        $this->actingAs($this->makeStaff())
            ->post(route('purchases.store'), [
                'supplier_id' => $supplier->id,
                'items' => [['product_id' => $product->id, 'quantity' => 0, 'unit_cost' => -10]],
            ])
            ->assertSessionHasErrors(['items.0.quantity', 'items.0.unit_cost']);

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_supplier_is_required(): void
    {
        $product = $this->makeProduct();

        $this->actingAs($this->makeStaff())
            ->post(route('purchases.store'), [
                'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_cost' => 100]],
            ])
            ->assertSessionHasErrors('supplier_id');
    }

    public function test_admin_can_cancel_purchase_and_stock_is_reverted(): void
    {
        $supplier = Supplier::factory()->create();
        $product = $this->makeProduct(['stock' => 10]);

        $this->actingAs($this->makeStaff())->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 1000]],
        ]);
        $this->assertSame(15, $product->fresh()->stock);

        $purchase = Purchase::first();

        $this->actingAs($this->makeAdmin())
            ->post(route('purchases.cancel', $purchase))
            ->assertSessionHasNoErrors();

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(Purchase::STATUS_CANCELLED, $purchase->fresh()->status);
    }

    public function test_purchase_cannot_be_cancelled_when_stock_was_already_sold(): void
    {
        $supplier = Supplier::factory()->create();
        $product = $this->makeProduct(['stock' => 0]);
        $staff = $this->makeStaff();

        $this->actingAs($staff)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 10, 'unit_cost' => 1000]],
        ]);

        $this->actingAs($staff)->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 8]],
        ]);
        $this->assertSame(2, $product->fresh()->stock);

        $purchase = Purchase::first();

        $this->actingAs($this->makeAdmin())
            ->post(route('purchases.cancel', $purchase))
            ->assertSessionHasErrors('purchase');

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame(Purchase::STATUS_COMPLETED, $purchase->fresh()->status);
    }
}
