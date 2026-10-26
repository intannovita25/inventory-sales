<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\SaleService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase, InteractsWithInventory;

    private function validPayload(array $override = []): array
    {
        return array_merge([
            'sku' => 'TST-001',
            'name' => 'Produk Uji',
            'category_id' => Category::factory()->create()->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'stock' => 5,
            'minimum_stock' => 2,
        ], $override);
    }

    public function test_admin_can_create_product(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('products.store'), $this->validPayload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['sku' => 'TST-001', 'stock' => 5]);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $existing = $this->makeProduct(['sku' => 'DUP-001']);

        $this->actingAs($this->makeAdmin())
            ->post(route('products.store'), $this->validPayload(['sku' => 'DUP-001', 'category_id' => $existing->category_id]))
            ->assertSessionHasErrors('sku');

        $this->assertDatabaseCount('products', 1);
    }

    public function test_database_constraint_also_protects_unique_sku(): void
    {
        $this->makeProduct(['sku' => 'DUP-002']);

        $this->expectException(QueryException::class);

        // Melewati validasi sengaja: constraint database harus tetap menolak.
        $this->makeProduct(['sku' => 'DUP-002']);
    }

    public function test_negative_price_is_rejected(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('products.store'), $this->validPayload(['selling_price' => -1, 'purchase_price' => -5]))
            ->assertSessionHasErrors(['selling_price', 'purchase_price']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_non_existing_category_is_rejected(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('products.store'), $this->validPayload(['category_id' => 99999]))
            ->assertSessionHasErrors('category_id');
    }

    public function test_stock_cannot_be_changed_from_edit_form(): void
    {
        $product = $this->makeProduct(['stock' => 10]);

        $this->actingAs($this->makeAdmin())
            ->put(route('products.update', $product), [
                'sku' => $product->sku,
                'name' => 'Nama Baru',
                'category_id' => $product->category_id,
                'purchase_price' => 1000,
                'selling_price' => 2000,
                'minimum_stock' => 3,
                'stock' => 999,
            ])
            ->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertSame('Nama Baru', $product->name);
        $this->assertSame(10, $product->stock);
        $this->assertSame(3, $product->minimum_stock);
    }

    public function test_unused_product_is_soft_deleted(): void
    {
        $product = $this->makeProduct();

        $this->actingAs($this->makeAdmin())
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_product_used_in_transaction_cannot_be_deleted(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['stock' => 10]);

        app(SaleService::class)->create($admin, [['product_id' => $product->id, 'quantity' => 1]]);

        $this->actingAs($admin)
            ->delete(route('products.destroy', $product))
            ->assertSessionHasErrors('product');

        $this->assertNotNull(Product::find($product->id));
    }
}
