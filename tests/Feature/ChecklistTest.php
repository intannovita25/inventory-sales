<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

/**
 * Bukti otomatis untuk "Checklist implementasi Laravel" (slide 12).
 */
class ChecklistTest extends TestCase
{
    use RefreshDatabase, InteractsWithInventory;

    // Item 2: migration membuat seluruh tabel
    public function test_migrations_create_all_required_tables_and_columns(): void
    {
        foreach (['users', 'categories', 'products', 'suppliers', 'purchases', 'purchase_items', 'sales', 'sale_items'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} harus dibuat oleh migration.");
        }

        $this->assertTrue(Schema::hasColumns('products', [
            'category_id', 'sku', 'name', 'purchase_price', 'selling_price', 'stock', 'minimum_stock',
        ]));
        $this->assertTrue(Schema::hasColumn('users', 'role'));
        $this->assertTrue(Schema::hasColumns('sales', ['user_id', 'sale_number', 'total', 'status', 'sold_at']));
        $this->assertTrue(Schema::hasColumns('purchases', ['supplier_id', 'user_id', 'purchase_number', 'total', 'status', 'purchased_at']));
    }

    // Item 4: layout reusable, navigasi, pesan sukses/error
    public function test_pages_have_navigation_and_success_message(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Master Data')
            ->assertSee('Penjualan')
            ->assertSee('Keluar');

        $this->actingAs($admin)->followingRedirects()
            ->post(route('categories.store'), ['name' => 'Minuman'])
            ->assertSee('Kategori berhasil ditambahkan.');
    }

    // Item 5: validasi server dengan pesan yang mudah dipahami (Bahasa Indonesia)
    public function test_validation_error_message_is_shown_in_form_page(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->post(route('categories.store'), ['name' => 'Minuman']);

        $this->actingAs($admin)
            ->from(route('categories.create'))
            ->followingRedirects()
            ->post(route('categories.store'), ['name' => 'Minuman'])
            ->assertSee('sudah digunakan');

        $this->actingAs($admin)
            ->from(route('categories.create'))
            ->followingRedirects()
            ->post(route('categories.store'), ['name' => ''])
            ->assertSee('wajib diisi');
    }

    // Item 6: hindari query duplikat (N+1) pada daftar data
    public function test_product_list_does_not_run_n_plus_one_queries(): void
    {
        $this->actingAs($this->makeStaff());
        DB::enableQueryLog();

        Product::factory()->count(2)->create();
        DB::flushQueryLog();
        $this->get(route('products.index'))->assertOk();
        $few = count(DB::getQueryLog());

        Product::factory()->count(8)->create();
        DB::flushQueryLog();
        $this->get(route('products.index'))->assertOk();
        $many = count(DB::getQueryLog());

        $this->assertSame($few, $many, 'Jumlah query tidak boleh bertambah seiring jumlah baris.');
    }

    public function test_sales_list_does_not_run_n_plus_one_queries(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        DB::enableQueryLog();

        $make = fn (int $n) => Sale::create([
            'user_id' => $admin->id, 'sale_number' => 'SL-NP1-' . $n, 'total' => 1000,
            'status' => 'completed', 'sold_at' => now(),
        ]);

        foreach (range(1, 2) as $n) {
            $make($n);
        }
        DB::flushQueryLog();
        $this->get(route('sales.index'))->assertOk();
        $few = count(DB::getQueryLog());

        foreach (range(3, 8) as $n) {
            $make($n);
        }
        DB::flushQueryLog();
        $this->get(route('sales.index'))->assertOk();
        $many = count(DB::getQueryLog());

        $this->assertSame($few, $many);
    }

    // Item 9: CSRF, escaping output, mass-assignment
    public function test_forms_contain_csrf_token(): void
    {
        $this->get('/login')->assertSee('name="_token"', false);

        $this->actingAs($this->makeAdmin())
            ->get(route('products.create'))
            ->assertSee('name="_token"', false);
    }

    public function test_output_is_escaped_against_xss(): void
    {
        $this->makeProduct(['name' => '<script>alert("xss")</script>']);

        $this->actingAs($this->makeAdmin())
            ->get(route('products.index'))
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_mass_assignment_protection_ignores_non_fillable_columns(): void
    {
        $category = \App\Models\Category::factory()->create();

        $product = Product::create([
            'id' => 9999, // tidak ada di $fillable -> diabaikan
            'category_id' => $category->id,
            'sku' => 'MASS-1',
            'name' => 'Uji Mass Assignment',
            'purchase_price' => 1,
            'selling_price' => 2,
            'stock' => 1,
            'minimum_stock' => 1,
        ]);

        $this->assertNotSame(9999, $product->id);
    }

    // Item 10: kredensial di .env; hanya .env.example yang di-commit
    public function test_env_is_gitignored_and_example_has_no_secrets(): void
    {
        $gitignore = file_get_contents(base_path('.gitignore'));
        $this->assertMatchesRegularExpression('/^\/?\.env$/m', $gitignore);

        $examplePath = base_path('.env.example');
        $this->assertFileExists($examplePath);

        $example = file_get_contents($examplePath);
        $this->assertMatchesRegularExpression('/^APP_KEY=\s*$/m', $example);
        $this->assertMatchesRegularExpression('/^DB_PASSWORD=\s*$/m', $example);
    }

    // Slide 17: tidak menampilkan stack trace; halaman error khusus
    public function test_custom_error_pages_are_used(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');

        $this->actingAs($this->makeStaff())
            ->get(route('users.index'))
            ->assertForbidden()
            ->assertSee('Akses ditolak');
    }
}
