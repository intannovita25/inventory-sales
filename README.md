# Inventory & Sales Management System

Proyek **UTS Web Programming 2 — Integrated Laravel Project**.
Aplikasi web untuk mengelola produk, pemasok, pembelian stok (stock-in), dan penjualan dengan
**konsistensi stok**, **role-based access (Admin/Staff)**, validasi server-side, dan business logic transaksi berbasis
`DB::transaction`.

## 1. Project Overview

Sebuah usaha distribusi kecil mengelola produk, pemasok, pembelian, dan penjualan lewat beberapa staf.
Aplikasi ini menyelesaikan masalah: stok tidak sesuai transaksi, produk habis tetap terjual, riwayat sulit ditelusuri,
dan hak akses yang berbeda antara pemilik dan staf.

Teknologi: **Laravel (11+)**, **MySQL**, Eloquent ORM, Blade, Bootstrap 5 (CDN).

## 2. Requirements

| Kebutuhan | Versi |
|---|---|
| PHP | 8.2 atau lebih baru (ekstensi: mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, fileinfo) |
| Composer | 2.x |
| MySQL / MariaDB | 8.x / 10.x |
| Node.js + npm | 18+ (untuk `npm install` dan `npm run build`) |
| Koneksi internet | Untuk `composer install`, `npm install`, dan memuat Bootstrap dari CDN |

## 3. Installation

```bash
git clone <repository-url>
cd inventory-sales

composer install
cp .env.example .env          # Windows CMD: copy .env.example .env
php artisan key:generate

# Configure DB credentials in .env  (lihat bagian 4)

php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Buka <http://127.0.0.1:8000>.

> Tampilan memakai Bootstrap 5 dari CDN, sehingga aplikasi tetap berjalan walau `npm run build` hanya
> membangun aset bawaan Laravel. Langkah npm tetap dijalankan agar setup identik dengan standar mata kuliah.

## 4. Environment Setup

1. Buat database kosong, contoh: `inventory_sales` (collation `utf8mb4_unicode_ci`).
2. Edit `.env`:

```env
APP_NAME="Inventory Sales"
APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_sales
DB_USERNAME=root
DB_PASSWORD=
```

> Zona waktu diatur otomatis lewat `APP_TIMEZONE` (default `Asia/Jakarta`, lihat `config/inventory.php`).
> File `.env` **tidak** di-commit; hanya `.env.example`.
> Untuk server publik gunakan `APP_ENV=production` dan `APP_DEBUG=false` agar stack trace tidak tampil.

## 5. Migration & Seeding

```bash
php artisan migrate --seed
# atau mengulang dari nol (menghapus semua tabel lalu membuat ulang):
php artisan migrate:fresh --seed
```

Seeder membuat: 3 akun demo, 5 kategori, 3 pemasok, 14 produk (beberapa stok rendah), serta beberapa contoh
transaksi pembelian dan penjualan (dibuat lewat service yang sama dengan aplikasi, sehingga stok tetap konsisten).

## 6. Demo Account

| Role | Email | Password |
|---|---|---|
| Admin / Owner | `admin@example.com` | `password` |
| Staff | `staff@example.com` | `password` |
| Staff | `staff2@example.com` | `password` |

Akun di atas hanya data demo. **Ganti password** bila aplikasi dipublikasikan.

## 7. Features

- **Authentication**: login/logout, password di-hash (bcrypt), route terproteksi, rate-limit login, regenerasi session.
- **Role-based access** (server-side, memakai `Gate` + middleware `can:`):
  - Admin: semua fitur, kelola user/role, CRUD kategori/produk/pemasok, melihat semua transaksi, membatalkan transaksi.
  - Staff: melihat produk, membuat pembelian & penjualan, hanya melihat transaksinya sendiri.
- **Master data**: kategori, produk (SKU unik, harga, stok, stok minimum), pemasok, pengguna.
- **Pembelian (stock-in)**: header + banyak item; stok bertambah setelah sukses.
- **Penjualan**: header + banyak item; cek stok; stok berkurang hanya jika sukses; harga & total dihitung server.
- **Pembatalan transaksi (Admin)**: stok dipulihkan secara konsisten; pembatalan ganda ditolak; pembelian tidak bisa
  dibatalkan bila stoknya sudah terpakai.
- **Dashboard**: total produk, stok rendah, penjualan hari ini/bulan ini, jumlah transaksi — semua dari query agregat.
- **Search, filter, pagination**: SKU/nama, kategori, stok rendah, nomor transaksi, status, rentang tanggal; filter
  tetap terbawa saat pindah halaman.
- **Keamanan dasar**: CSRF, escaping output Blade, mass-assignment protection (`$fillable`), validasi server-side,
  FK + unique index di database, tidak ada kredensial di repository.

### Business rules yang ditegakkan

| Aturan | Lokasi di kode |
|---|---|
| SKU unik | `ProductRequest` (validasi) + `unique` index di migration products |
| Qty > 0, harga valid | `SaleRequest`, `PurchaseRequest`, dicek ulang di Service |
| Penjualan tidak melebihi stok | `SaleService::create()` |
| Stok berubah hanya jika transaksi sukses | `DB::transaction` di `SaleService` / `PurchaseService` |
| Total dihitung server | `SaleService`, `PurchaseService` (input harga/total dari browser diabaikan) |
| Cegah overselling bersamaan | `lockForUpdate()` pada baris produk |
| Transaksi selesai tidak diedit; hanya dibatalkan | Tidak ada route edit/update transaksi; ada `cancel` |
| Produk terpakai tidak di-hard-delete | `Product::hasTransactions()` + `SoftDeletes` + FK `restrictOnDelete` |

### Arsitektur

```
Browser → routes/web.php → Middleware (auth, can:...) → Controller
        → FormRequest (validasi) → Service (business logic, DB::transaction)
        → Eloquent Model → MySQL → Blade view
```

Struktur penting:

```
app/Http/Controllers   # Auth, Dashboard, Product, Category, Supplier, User, Purchase, Sale
app/Http/Requests      # Validasi server-side
app/Services           # SaleService, PurchaseService (logika transaksi)
app/Models             # Eloquent + relasi + scope
app/Providers/AppServiceProvider.php   # Gate: admin, manage-sales, manage-purchases
database/migrations    # Seluruh skema tabel
database/seeders       # Data demo
resources/views        # Blade (layout reusable, partials, halaman)
tests/Feature          # Pengujian otomatis
```

## ERD dan relasi

Lihat [`docs/ERD.md`](docs/ERD.md)

## Checklist implementasi

Pemetaan tiap item checklist (slide 12) ke file dan test ada di [`docs/CHECKLIST_IMPLEMENTASI.md`](docs/CHECKLIST_IMPLEMENTASI.md).

## Pengujian

```bash
php artisan test
```

Cakupan: Auth, Product, Purchase, Sale, Insufficient stock, Atomicity (rollback), Authorization, Search/Pagination,
Dashboard, Relasi, Seeder, serta checklist implementasi (migration, N+1, CSRF, XSS, mass-assignment, `.env`). Template laporan hasil: [`docs/TEST_REPORT.md`](docs/TEST_REPORT.md).

> Test memakai `RefreshDatabase`. Secara default Laravel memakai SQLite in-memory untuk testing
> (`phpunit.xml`); aktifkan ekstensi `pdo_sqlite` pada PHP, atau arahkan test ke database MySQL terpisah.

## Screenshots

Letakkan tangkapan layar di `docs/screenshots/` (daftar halaman yang perlu diambil ada di
[`docs/SCREENSHOTS.md`](docs/SCREENSHOTS.md)).

## 8. Known Limitations

- Satu gudang; tanpa payment gateway, multi-currency, akuntansi penuh, atau integrasi ERP (di luar ruang lingkup UTS).
- Transaksi tidak dapat diedit setelah selesai (hanya dibatalkan).
- SKU produk yang di-soft-delete tetap dianggap terpakai (constraint unik database).
- Bootstrap dimuat dari CDN sehingga memerlukan koneksi internet.
- Tidak ada fitur lupa-password / verifikasi email.

## Atribusi dan integritas akademik

- **Framework/library:** Laravel (dokumentasi resmi laravel.com/docs), Bootstrap 5 (CDN).
- **Pola yang dipakai:** `DB::transaction` dan `lockForUpdate()` mengikuti contoh materi kuliah (slide "Contoh logika
  penjualan") dan dokumentasi Laravel (*Database: Transactions*, *Queries: Pessimistic Locking*).j


