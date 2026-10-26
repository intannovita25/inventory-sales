@extends('layouts.app')

@section('title', 'Pengguna & Role')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Pengguna &amp; Role</h3>
    <a href="{{ route('users.create') }}" class="btn btn-primary">+ Tambah Pengguna</a>
</div>

<form method="GET" action="{{ route('users.index') }}" class="card card-body mb-3">
    <div class="row g-2">
        <div class="col-md-6"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama / email"></div>
        <div class="col-md-6 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Nama</th><th>Email</th><th>Role</th><th></th></tr></thead>
            <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge {{ $user->isAdmin() ? 'bg-primary' : 'bg-secondary' }} text-uppercase">{{ $user->role }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                        @if ($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus pengguna ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada pengguna.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
