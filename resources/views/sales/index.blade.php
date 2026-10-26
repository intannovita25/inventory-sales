@extends('layouts.app')

@section('title', 'Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Penjualan</h3>
    <a href="{{ route('sales.create') }}" class="btn btn-primary">+ Penjualan Baru</a>
</div>

<form method="GET" action="{{ route('sales.index') }}" class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1" for="q">No. transaksi</label>
            <input type="text" id="q" name="q" value="{{ request('q') }}" class="form-control" placeholder="SL-2026...">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="status">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="">Semua</option>
                <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="date_from">Dari tanggal</label>
            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="date_to">Sampai tanggal</label>
            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="form-control">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Filter</button>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>No. Transaksi</th><th>Tanggal</th><th>Kasir</th><th class="text-end">Item</th><th class="text-end">Total</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse ($sales as $sale)
                <tr>
                    <td>{{ $sale->sale_number }}</td>
                    <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->user->name }}</td>
                    <td class="text-end">{{ $sale->items_count }}</td>
                    <td class="text-end">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</td>
                    <td>@include('partials.status-badge', ['status' => $sale->status])</td>
                    <td class="text-end"><a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada penjualan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $sales->links() }}</div>
@endsection
