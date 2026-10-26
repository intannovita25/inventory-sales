# Checklist Implementasi Laravel (Slide 12) — Pemetaan Bukti

| # | Item checklist | Status | Di mana / bagaimana dipenuhi | Cara verifikasi |
|---|---|---|---|---|
| 1 | Project dapat dijalankan dari README pada environment bersih | Terpenuhi | `README.md` (instalasi, env, migrate, seed, serve) + `.env.example` siap MySQL tanpa kredensial | Langkah 13 di panduan: clone ke folder baru lalu ikuti README |
| 2 | Migration membuat seluruh tabel; tidak bergantung database manual | Terpenuhi | `database/migrations/*` (users+role, categories, suppliers, products, purchases, purchase_items, sales, sale_items) | `php artisan migrate:fresh --seed`; test `ChecklistTest::test_migrations_create_all_required_tables_and_columns` |
| 3 | Seeder menyiapkan kategori, produk, supplier, akun demo | Terpenuhi | `database/seeders/` (User, Category, Supplier, Product, Transaction) | `RelationshipTest::test_seeders_run_and_create_consistent_demo_data` |
| 4 | Blade layout reusable; navigasi; pesan sukses/error | Terpenuhi | `resources/views/layouts/app.blade.php`, `partials/alerts.blade.php`, `partials/status-badge.blade.php`, `_form` partials | `ChecklistTest::test_pages_have_navigation_and_success_message` |
| 5 | Validasi server dengan pesan mudah dipahami | Terpenuhi | `app/Http/Requests/*`, `lang/id/validation.php` (Bahasa Indonesia) | `ChecklistTest::test_validation_error_message_is_shown_in_form_page` dan test Product/Sale/Purchase |
| 6 | Eloquent relationship; hindari query duplikat | Terpenuhi | `app/Models/*` (hasMany/belongsTo); `with()`, `withCount()` pada controller | `RelationshipTest`, `ChecklistTest::test_product_list_does_not_run_n_plus_one_queries`, `test_sales_list_does_not_run_n_plus_one_queries` |
| 7 | Pagination untuk daftar data | Terpenuhi | `paginate(10)->withQueryString()` di semua index; `Paginator::useBootstrapFive()` | `SearchPaginationTest` |
| 8 | Middleware / policy / gate untuk authorization | Terpenuhi | `AppServiceProvider` (Gate `admin`, `manage-sales`, `manage-purchases`), `routes/web.php` (`auth`, `can:`), `FormRequest::authorize()` | `AuthorizationTest` |
| 9 | CSRF, escaping output, password hashing | Terpenuhi | `@csrf` di setiap form; Blade `{{ }}` (escape otomatis); cast `'password' => 'hashed'`; `$fillable` | `ChecklistTest::test_forms_contain_csrf_token`, `test_output_is_escaped_against_xss`, `test_mass_assignment_...`; `AuthTest::test_password_is_stored_hashed` |
| 10 | Kredensial di `.env`; commit hanya `.env.example` | Terpenuhi | `.env` diabaikan `.gitignore` Laravel; `.env.example` berisi `APP_KEY=` dan `DB_PASSWORD=` kosong | `ChecklistTest::test_env_is_gitignored_and_example_has_no_secrets`; `git status` tidak menampilkan `.env` |

## Tambahan terkait slide lain

| Slide | Persyaratan | Pemenuhan |
|---|---|---|
| 17 | Jangan tampilkan stack trace ke user di production | Halaman error khusus (`resources/views/errors/403,404,419,429,500,503`). Saat deploy set `APP_ENV=production` dan `APP_DEBUG=false` di `.env` |
| 17 | Pertahankan input valid saat validasi gagal | `old()` pada seluruh form, termasuk baris item transaksi |
| 18 | Angka dashboard dari database | `DashboardController` memakai query agregat |
| 13 | Responsive layout | Bootstrap 5 (grid + `table-responsive`) |

## Catatan keamanan saat demo

- `APP_DEBUG=true` hanya untuk pengembangan. Jangan dipakai di server publik.
- Ganti password akun demo bila aplikasi dipublikasikan.
