@extends('layouts.app')

@section('title', 'Pemasok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Pemasok</h3>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">+ Tambah Pemasok</a>
</div>

<form method="GET" action="{{ route('suppliers.index') }}" class="card card-body mb-3">
    <div class="row g-2">
        <div class="col-md-6"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama pemasok"></div>
        <div class="col-md-6 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Nama</th><th>Telepon</th><th>Email</th><th></th></tr></thead>
            <tbody>
            @forelse ($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->phone ?? '-' }}</td>
                    <td>{{ $supplier->email ?? '-' }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus pemasok ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pemasok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $suppliers->links() }}</div>
@endsection
