<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class RelationshipTest extends TestCase
{
    use RefreshDatabase, InteractsWithInventory;

    public function test_category_has_many_products_and_product_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($category->products->contains($product));
        $this->assertTrue($product->category->is($category));
    }

    public function test_sale_and_purchase_relationships(): void
    {
        $user = $this->makeAdmin();
        $supplier = Supplier::factory()->create();
        $product = $this->makeProduct(['stock' => 10]);

        $sale = app(SaleService::class)->create($user, [['product_id' => $product->id, 'quantity' => 2]]);
        $purchase = app(PurchaseService::class)->create($user, $supplier->id, [
            ['product_id' => $product->id, 'quantity' => 1, 'unit_cost' => 1000],
        ]);

        $this->assertCount(1, $sale->items);
        $this->assertTrue($sale->items->first()->product->is($product));
        $this->assertTrue($sale->user->is($user));

        $this->assertCount(1, $purchase->items);
        $this->assertTrue($purchase->supplier->is($supplier));
        $this->assertTrue($user->sales->contains($sale));
        $this->assertTrue($user->purchases->contains($purchase));
        $this->assertCount(1, $product->saleItems);
        $this->assertCount(1, $product->purchaseItems);
    }

    public function test_seeders_run_and_create_consistent_demo_data(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', ['email' => 'admin@example.com', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'staff@example.com', 'role' => 'staff']);
        $this->assertGreaterThan(0, Category::count());
        $this->assertGreaterThan(0, Product::count());
        $this->assertGreaterThan(0, Supplier::count());
        $this->assertSame(0, Product::where('stock', '<', 0)->count());
    }
}
