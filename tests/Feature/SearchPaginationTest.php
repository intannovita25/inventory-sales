<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class SearchPaginationTest extends TestCase
{
    use RefreshDatabase, InteractsWithInventory;

    public function test_product_list_is_paginated(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(25)->create(['category_id' => $category->id]);

        $this->actingAs($this->makeStaff());

        $page1 = $this->get(route('products.index'))->viewData('products');
        $this->assertSame(25, $page1->total());
        $this->assertCount(10, $page1->items());

        $page3 = $this->get(route('products.index', ['page' => 3]))->viewData('products');
        $this->assertCount(5, $page3->items());
    }

    public function test_search_by_sku_and_name(): void
    {
        Product::factory()->count(5)->create();
        Product::factory()->create(['sku' => 'FIND-ME-1', 'name' => 'Barang Biasa']);
        Product::factory()->create(['sku' => 'ZZZ-9', 'name' => 'Kopi Spesial Gayo']);

        $this->actingAs($this->makeStaff());

        $bySku = $this->get(route('products.index', ['q' => 'FIND-ME']))->viewData('products');
        $this->assertSame(1, $bySku->total());

        $byName = $this->get(route('products.index', ['q' => 'Gayo']))->viewData('products');
        $this->assertSame(1, $byName->total());
    }

    public function test_filter_by_category(): void
    {
        $cat1 = Category::factory()->create();
        $cat2 = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $cat1->id]);
        Product::factory()->count(2)->create(['category_id' => $cat2->id]);

        $this->actingAs($this->makeStaff());

        $result = $this->get(route('products.index', ['category_id' => $cat2->id]))->viewData('products');
        $this->assertSame(2, $result->total());
    }

    public function test_low_stock_filter_and_page(): void
    {
        $this->makeProduct(['stock' => 2, 'minimum_stock' => 5]);
        $this->makeProduct(['stock' => 5, 'minimum_stock' => 5]);
        $this->makeProduct(['stock' => 50, 'minimum_stock' => 5]);

        $this->actingAs($this->makeStaff());

        $this->assertSame(2, $this->get(route('products.index', ['low_stock' => 1]))->viewData('products')->total());
        $this->assertSame(2, $this->get(route('products.low-stock'))->viewData('products')->total());
    }

    public function test_filter_is_kept_in_pagination_links(): void
    {
        Product::factory()->count(12)->create(['name' => 'Kopi Enak']);
        Product::factory()->count(3)->create();

        $this->actingAs($this->makeStaff());

        $this->get(route('products.index', ['q' => 'Kopi']))->assertSee('q=Kopi', false);
    }

    public function test_invalid_filter_input_is_rejected(): void
    {
        $this->actingAs($this->makeStaff());

        $this->get(route('products.index', ['category_id' => 'bukan-angka']))
            ->assertSessionHasErrors('category_id');
    }

    public function test_sales_filter_by_status_and_date(): void
    {
        $admin = $this->makeAdmin();

        $make = fn (string $no, string $status, $date) => Sale::create([
            'user_id' => $admin->id,
            'sale_number' => $no,
            'total' => 1000,
            'status' => $status,
            'sold_at' => $date,
        ]);

        $make('SL-OLD-1', 'completed', now()->subDays(20));
        $make('SL-NEW-1', 'completed', now()->subDay());
        $make('SL-NEW-2', 'cancelled', now()->subDay());

        $this->actingAs($admin);

        $this->assertSame(3, $this->get(route('sales.index'))->viewData('sales')->total());

        $this->assertSame(1, $this->get(route('sales.index', ['status' => 'cancelled']))->viewData('sales')->total());

        $recent = $this->get(route('sales.index', ['date_from' => now()->subDays(3)->toDateString()]))->viewData('sales');
        $this->assertSame(2, $recent->total());

        $byNumber = $this->get(route('sales.index', ['q' => 'SL-OLD']))->viewData('sales');
        $this->assertSame(1, $byNumber->total());
    }

    public function test_dashboard_numbers_come_from_database(): void
    {
        $admin = $this->makeAdmin();
        $this->makeProduct(['stock' => 1, 'minimum_stock' => 5]); // stok rendah
        $this->makeProduct(['stock' => 50, 'minimum_stock' => 5]);

        Sale::create([
            'user_id' => $admin->id, 'sale_number' => 'SL-T-1', 'total' => 50000,
            'status' => 'completed', 'sold_at' => now(),
        ]);
        Sale::create([ // dibatalkan: tidak dihitung
            'user_id' => $admin->id, 'sale_number' => 'SL-T-2', 'total' => 99999,
            'status' => 'cancelled', 'sold_at' => now(),
        ]);

        $stats = $this->actingAs($admin)->get(route('dashboard'))->viewData('stats');

        $this->assertSame(2, $stats['products']);
        $this->assertSame(1, $stats['low_stock']);
        $this->assertSame(1, $stats['sales_count']);
        $this->assertEquals(50000, (float) $stats['sales_today']);
        $this->assertEquals(50000, (float) $stats['sales_month']);
    }
}
