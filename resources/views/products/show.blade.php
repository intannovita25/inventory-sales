@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">{{ $product->name }}</h3>
    <div class="d-flex gap-2">
        @can('admin')
            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary">Ubah</a>
        @endcan
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>
</div>

<div class="card mb-3"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3">SKU</dt><dd class="col-sm-9">{{ $product->sku }}</dd>
        <dt class="col-sm-3">Kategori</dt><dd class="col-sm-9">{{ $product->category->name }}</dd>
        <dt class="col-sm-3">Harga Beli</dt><dd class="col-sm-9">Rp {{ number_format((float) $product->purchase_price, 0, ',', '.') }}</dd>
        <dt class="col-sm-3">Harga Jual</dt><dd class="col-sm-9">Rp {{ number_format((float) $product->selling_price, 0, ',', '.') }}</dd>
        <dt class="col-sm-3">Stok</dt>
        <dd class="col-sm-9">
            <span class="{{ $product->isLowStock() ? 'text-danger fw-bold' : '' }}">{{ $product->stock }}</span>
            @if ($product->isLowStock()) <span class="badge bg-danger ms-1">Stok rendah</span> @endif
        </dd>
        <dt class="col-sm-3">Stok Minimum</dt><dd class="col-sm-9">{{ $product->minimum_stock }}</dd>
    </dl>
</div></div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white"><strong>Penjualan Terakhir</strong></div>
            <ul class="list-group list-group-flush">
                @forelse ($recentSales as $item)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $item->sale->sale_number }}</span>
                        <span>{{ $item->quantity }} pcs</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Belum ada.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white"><strong>Pembelian Terakhir</strong></div>
            <ul class="list-group list-group-flush">
                @forelse ($recentPurchases as $item)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $item->purchase->purchase_number }}</span>
                        <span>{{ $item->quantity }} pcs</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Belum ada.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
