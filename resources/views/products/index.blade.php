@extends('layouts.app')

@section('title', 'Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Produk</h3>
    @can('admin')
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ Tambah Produk</a>
    @endcan
</div>

<form method="GET" action="{{ route('products.index') }}" class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-1" for="q">Cari SKU / nama</label>
            <input type="text" id="q" name="q" value="{{ request('q') }}" class="form-control" placeholder="Contoh: MNM-001 atau Air">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1" for="category_id">Kategori</label>
            <select id="category_id" name="category_id" class="form-select">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="low_stock" id="low_stock" value="1" @checked(request()->boolean('low_stock'))>
                <label class="form-check-label" for="low_stock">Stok rendah</label>
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>SKU</th><th>Nama</th><th>Kategori</th>
                <th class="text-end">Harga Beli</th><th class="text-end">Harga Jual</th>
                <th class="text-end">Stok</th><th class="text-end">Min.</th><th></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td>
                    <td>{{ $product->category->name }}</td>
                    <td class="text-end">{{ number_format((float) $product->purchase_price, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format((float) $product->selling_price, 0, ',', '.') }}</td>
                    <td class="text-end">
                        <span class="{{ $product->isLowStock() ? 'text-danger fw-bold' : '' }}">{{ $product->stock }}</span>
                    </td>
                    <td class="text-end">{{ $product->minimum_stock }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                        @can('admin')
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada produk ditemukan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $products->links() }}</div>
@endsection
