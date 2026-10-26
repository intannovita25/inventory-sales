@extends('layouts.app')

@section('title', 'Stok Rendah')

@section('content')
<h3 class="mb-1">Produk Stok Rendah</h3>
<p class="text-muted">Produk dengan stok kurang dari atau sama dengan stok minimum.</p>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>SKU</th><th>Nama</th><th>Kategori</th><th class="text-end">Stok</th><th class="text-end">Minimum</th><th class="text-end">Kekurangan</th></tr>
            </thead>
            <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td>
                    <td>{{ $product->category->name }}</td>
                    <td class="text-end text-danger fw-bold">{{ $product->stock }}</td>
                    <td class="text-end">{{ $product->minimum_stock }}</td>
                    <td class="text-end">{{ max(0, $product->minimum_stock - $product->stock) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Semua stok produk aman.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $products->links() }}</div>
@endsection
