@extends('layouts.app')

@section('title', 'Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Pembelian / Stock-in</h3>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ Pembelian Baru</a>
</div>

<form method="GET" action="{{ route('purchases.index') }}" class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label small mb-1" for="q">No. transaksi</label>
            <input type="text" id="q" name="q" value="{{ request('q') }}" class="form-control" placeholder="PO-2026...">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="supplier_id">Pemasok</label>
            <select id="supplier_id" name="supplier_id" class="form-select">
                <option value="">Semua</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
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
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Filter</button>
            <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>No. Transaksi</th><th>Tanggal</th><th>Pemasok</th><th>Dibuat oleh</th><th class="text-end">Item</th><th class="text-end">Total</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse ($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->purchase_number }}</td>
                    <td>{{ $purchase->purchased_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $purchase->supplier->name }}</td>
                    <td>{{ $purchase->user->name }}</td>
                    <td class="text-end">{{ $purchase->items_count }}</td>
                    <td class="text-end">Rp {{ number_format((float) $purchase->total, 0, ',', '.') }}</td>
                    <td>@include('partials.status-badge', ['status' => $purchase->status])</td>
                    <td class="text-end"><a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada pembelian.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $purchases->links() }}</div>
@endsection
