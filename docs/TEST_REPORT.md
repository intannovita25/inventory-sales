# Laporan Pengujian

Jalankan:

```bash
php artisan test
```

Salin ringkasan hasilnya (jumlah test, assertion, durasi) atau tempel screenshot terminal ke bagian **Bukti Hasil**.

## Pemetaan skenario minimum → test otomatis

| Skenario (slide UTS) | Test | File |
|---|---|---|
| Guest tidak dapat membuka dashboard | `test_guest_cannot_open_dashboard` | `AuthTest` |
| Password salah ditolak | `test_wrong_password_is_rejected` | `AuthTest` |
| SKU duplikat ditolak | `test_duplicate_sku_is_rejected`, `test_database_constraint_also_protects_unique_sku` | `ProductTest` |
| Harga negatif ditolak | `test_negative_price_is_rejected` | `ProductTest` |
| Pembelian sukses menambah stok | `test_successful_purchase_increases_stock` | `PurchaseTest` |
| Penjualan sukses mengurangi stok | `test_successful_sale_reduces_stock` | `SaleTest` |
| Stok tidak cukup ditolak tanpa perubahan data | `test_sale_with_insufficient_stock_is_rejected_without_any_change` | `SaleTest` |
| Satu item gagal → seluruh transaksi rollback | `test_atomicity_if_one_item_fails_whole_transaction_is_rolled_back` | `SaleTest` |
| Staff tidak dapat mengakses fungsi Admin | `test_staff_cannot_open_admin_pages_by_direct_url`, `test_staff_cannot_modify_master_data_via_post`, `test_staff_cannot_cancel_transactions` | `AuthorizationTest` |
| Filter & pagination sesuai | `test_product_list_is_paginated`, `test_search_by_sku_and_name`, `test_filter_by_category`, `test_sales_filter_by_status_and_date`, `test_filter_is_kept_in_pagination_links` | `SearchPaginationTest` |

Checklist implementasi (slide 12): `ChecklistTest` (migration, pesan sukses/error, N+1, CSRF, XSS, mass-assignment, `.env`, halaman error).

Tambahan: total dihitung server (`test_total_and_price_are_calculated_by_server_not_browser`), qty 0/negatif ditolak,
pembatalan memulihkan stok, pembatalan pembelian ditolak bila stok sudah terjual, dashboard dari database, relasi Eloquent,
seeder.

## Bukti Hasil

```
php artisan test
Tests:    60 passed (241 assertions)
Duration: 4.00s
```

## Pengujian manual (untuk demo)

| # | Langkah | Hasil yang diharapkan | Lulus? |
|---|---|---|---|
| 1 | Buka `/dashboard` tanpa login | Diarahkan ke halaman login | |
| 2 | Login `staff@example.com`, buka `/users` | 403 Forbidden | |
| 3 | Login admin, tambah produk dengan SKU yang sudah ada | Pesan "SKU sudah digunakan" | |
| 4 | Buat pembelian 10 unit produk X | Stok X bertambah 10 | |
| 5 | Buat penjualan 3 unit produk X | Stok X berkurang 3 | |
| 6 | Buat penjualan melebihi stok | Ditolak, stok & data tidak berubah | |
| 7 | Penjualan 2 item: item 1 cukup, item 2 tidak | Ditolak, stok item 1 tidak berubah | |
| 8 | Admin membatalkan penjualan | Status "Dibatalkan", stok kembali | |
| 9 | Cari produk, filter kategori, pindah halaman | Filter tetap terbawa | |
