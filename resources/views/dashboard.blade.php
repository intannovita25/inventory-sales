@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h3 class="mb-1">Dashboard</h3>
<p class="text-muted">
    @can('admin') Ringkasan seluruh transaksi. @else Ringkasan transaksi milik Anda. @endcan
</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Total Produk</div>
            <div class="stat-value">{{ number_format($stats['products'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Produk Stok Rendah</div>
            <div class="stat-value {{ $stats['low_stock'] > 0 ? 'text-danger' : '' }}">{{ number_format($stats['low_stock'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Penjualan Hari Ini</div>
            <div class="stat-value">Rp {{ number_format((float) $stats['sales_today'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Penjualan Bulan Ini</div>
            <div class="stat-value">Rp {{ number_format((float) $stats['sales_month'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Transaksi Penjualan</div>
            <div class="stat-value">{{ number_format($stats['sales_count'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Transaksi Pembelian</div>
            <div class="stat-value">{{ number_format($stats['purchases_count'], 0, ',', '.') }}</div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Produk Stok Rendah</strong>
                <a href="{{ route('products.low-stock') }}" class="small">Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>SKU</th><th>Nama</th><th class="text-end">Stok</th><th class="text-end">Min.</th></tr></thead>
                    <tbody>
                    @forelse ($lowStockProducts as $product)
                        <tr>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->name }}</td>
                            <td class="text-end text-danger fw-bold">{{ $product->stock }}</td>
                            <td class="text-end">{{ $product->minimum_stock }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Semua stok aman.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Penjualan Terbaru</strong>
                <a href="{{ route('sales.index') }}" class="small">Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>No.</th><th>Kasir</th><th class="text-end">Total</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse ($recentSales as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}">{{ $sale->sale_number }}</a></td>
                            <td>{{ $sale->user->name }}</td>
                            <td class="text-end">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</td>
                            <td>@include('partials.status-badge', ['status' => $sale->status])</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Belum ada penjualan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
