<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase, InteractsWithInventory;

    public function test_staff_cannot_open_admin_pages_by_direct_url(): void
    {
        $staff = $this->makeStaff();
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = $this->makeProduct();

        $this->actingAs($staff);

        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('users.create'))->assertForbidden();
        $this->get(route('categories.index'))->assertForbidden();
        $this->get(route('categories.edit', $category))->assertForbidden();
        $this->get(route('suppliers.index'))->assertForbidden();
        $this->get(route('suppliers.edit', $supplier))->assertForbidden();
        $this->get(route('products.create'))->assertForbidden();
        $this->get(route('products.edit', $product))->assertForbidden();
    }

    public function test_staff_cannot_modify_master_data_via_post(): void
    {
        $staff = $this->makeStaff();
        $product = $this->makeProduct();
        $category = Category::factory()->create();

        $this->actingAs($staff);

        $this->post(route('products.store'), ['sku' => 'X', 'name' => 'X'])->assertForbidden();
        $this->delete(route('products.destroy', $product))->assertForbidden();
        $this->post(route('categories.store'), ['name' => 'Hack'])->assertForbidden();
        $this->delete(route('categories.destroy', $category))->assertForbidden();
        $this->post(route('users.store'), [
            'name' => 'Hacker', 'email' => 'h@example.com', 'role' => 'admin',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertForbidden();

        $this->assertNotNull($product->fresh());
        $this->assertDatabaseMissing('users', ['email' => 'h@example.com']);
    }

    public function test_staff_can_view_products_and_create_transactions_pages(): void
    {
        $this->actingAs($this->makeStaff());

        $this->get(route('products.index'))->assertOk();
        $this->get(route('sales.create'))->assertOk();
        $this->get(route('purchases.create'))->assertOk();
        $this->get(route('products.low-stock'))->assertOk();
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_admin_can_open_admin_pages(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->get(route('users.index'))->assertOk();
        $this->get(route('categories.index'))->assertOk();
        $this->get(route('suppliers.index'))->assertOk();
        $this->get(route('products.create'))->assertOk();
    }

    public function test_staff_cannot_cancel_transactions(): void
    {
        $staff = $this->makeStaff();
        $product = $this->makeProduct(['stock' => 10]);

        $this->actingAs($staff)->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ]);

        $sale = Sale::first();

        $this->actingAs($staff)->post(route('sales.cancel', $sale))->assertForbidden();

        $this->assertSame(Sale::STATUS_COMPLETED, $sale->fresh()->status);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_staff_cannot_view_other_staff_transactions_but_admin_can(): void
    {
        $owner = $this->makeStaff();
        $other = $this->makeStaff();
        $product = $this->makeProduct(['stock' => 10]);

        $this->actingAs($owner)->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);
        $sale = Sale::first();

        $this->actingAs($owner)->get(route('sales.show', $sale))->assertOk();
        $this->actingAs($other)->get(route('sales.show', $sale))->assertForbidden();
        $this->actingAs($this->makeAdmin())->get(route('sales.show', $sale))->assertOk();
    }

    public function test_staff_sales_list_only_contains_their_own_sales(): void
    {
        $staffA = $this->makeStaff();
        $staffB = $this->makeStaff();
        $product = $this->makeProduct(['stock' => 20]);

        $this->actingAs($staffA)->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 1]]]);
        $this->actingAs($staffB)->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 1]]]);
        $this->actingAs($staffB)->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 1]]]);

        $this->assertSame(1, $this->actingAs($staffA)->get(route('sales.index'))->viewData('sales')->total());
        $this->assertSame(2, $this->actingAs($staffB)->get(route('sales.index'))->viewData('sales')->total());
        $this->assertSame(3, $this->actingAs($this->makeAdmin())->get(route('sales.index'))->viewData('sales')->total());
    }

    public function test_admin_cannot_delete_own_account_or_demote_self(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->delete(route('users.destroy', $admin))->assertSessionHasErrors('user');
        $this->assertNotNull(User::find($admin->id));

        $this->actingAs($admin)->put(route('users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'staff',
        ])->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->isAdmin());
    }
}
