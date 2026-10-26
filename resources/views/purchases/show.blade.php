@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Pembelian {{ $purchase->purchase_number }}</h3>
    <div class="d-flex gap-2">
        @can('admin')
            @if ($purchase->status === 'completed')
                <form action="{{ route('purchases.cancel', $purchase) }}" method="POST"
                      onsubmit="return confirm('Batalkan pembelian ini? Stok akan ditarik kembali.')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">Batalkan Transaksi</button>
                </form>
            @endif
        @endcan
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>
</div>

<div class="card mb-3"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3">Tanggal</dt><dd class="col-sm-9">{{ $purchase->purchased_at->format('d/m/Y H:i') }}</dd>
        <dt class="col-sm-3">Pemasok</dt><dd class="col-sm-9">{{ $purchase->supplier->name }}</dd>
        <dt class="col-sm-3">Dibuat oleh</dt><dd class="col-sm-9">{{ $purchase->user->name }}</dd>
        <dt class="col-sm-3">Status</dt><dd class="col-sm-9">@include('partials.status-badge', ['status' => $purchase->status])</dd>
        <dt class="col-sm-3">Total</dt><dd class="col-sm-9 fw-bold">Rp {{ number_format((float) $purchase->total, 0, ',', '.') }}</dd>
    </dl>
</div></div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light"><tr><th>SKU</th><th>Produk</th><th class="text-end">Jumlah</th><th class="text-end">Harga Beli</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody>
            @foreach ($purchase->items as $item)
                <tr>
                    <td>{{ $item->product->sku }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">Rp {{ number_format((float) $item->unit_cost, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
