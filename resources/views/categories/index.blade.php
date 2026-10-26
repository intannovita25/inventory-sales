@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Kategori</h3>
    <a href="{{ route('categories.create') }}" class="btn btn-primary">+ Tambah Kategori</a>
</div>

<form method="GET" action="{{ route('categories.index') }}" class="card card-body mb-3">
    <div class="row g-2">
        <div class="col-md-6"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama kategori"></div>
        <div class="col-md-6 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Nama</th><th class="text-end">Jumlah Produk</th><th></th></tr></thead>
            <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td class="text-end">{{ $category->products_count }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus kategori ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $categories->links() }}</div>
@endsection
