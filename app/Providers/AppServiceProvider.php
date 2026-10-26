<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Zona waktu aplikasi (default WIB), sehingga "penjualan hari ini" sesuai waktu lokal.
        $timezone = config('inventory.timezone', 'Asia/Jakarta');
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);

        // Pagination memakai tampilan Bootstrap 5
        Paginator::useBootstrapFive();

        // ---- Authorization (Gate) ----
        // Admin/Owner: kelola master data, user, pembatalan transaksi, laporan lengkap.
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        // Admin dan Staff boleh membuat transaksi pembelian & penjualan.
        Gate::define('manage-purchases', fn (User $user) => in_array($user->role, [User::ROLE_ADMIN, User::ROLE_STAFF], true));
        Gate::define('manage-sales', fn (User $user) => in_array($user->role, [User::ROLE_ADMIN, User::ROLE_STAFF], true));
    }
}
