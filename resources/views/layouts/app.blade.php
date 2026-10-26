<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; }
        .navbar-brand { font-weight: 700; letter-spacing: .3px; }
        .card { border: 0; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .stat-value { font-size: 1.6rem; font-weight: 700; }
    </style>
</head>
<body>
@auth
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Inventory &amp; Sales</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.*') && ! request()->routeIs('products.low-stock') ? 'active' : '' }}" href="{{ route('products.index') }}">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.low-stock') ? 'active' : '' }}" href="{{ route('products.low-stock') }}">Stok Rendah</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">Pembelian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">Penjualan</a>
                    </li>
                    @can('admin')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Master Data</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('categories.index') }}">Kategori</a></li>
                                <li><a class="dropdown-item" href="{{ route('suppliers.index') }}">Pemasok</a></li>
                                <li><a class="dropdown-item" href="{{ route('users.index') }}">Pengguna &amp; Role</a></li>
                            </ul>
                        </li>
                    @endcan
                </ul>

                <div class="d-flex align-items-center gap-2 text-white">
                    <span>{{ auth()->user()->name }}</span>
                    <span class="badge bg-light text-primary text-uppercase">{{ auth()->user()->role }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
@endauth

<main class="container pb-5">
    @include('partials.alerts')
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
