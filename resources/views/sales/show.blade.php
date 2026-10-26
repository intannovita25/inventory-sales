@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Penjualan {{ $sale->sale_number }}</h3>
    <div class="d-flex gap-2">
        @can('admin')
            @if ($sale->status === 'completed')
                <form action="{{ route('sales.cancel', $sale) }}" method="POST"
                      onsubmit="return confirm('Batalkan penjualan ini? Stok akan dipulihkan.')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">Batalkan Transaksi</button>
                </form>
            @endif
        @endcan
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>
</div>

<div class="card mb-3"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3">Tanggal</dt><dd class="col-sm-9">{{ $sale->sold_at->format('d/m/Y H:i') }}</dd>
        <dt class="col-sm-3">Kasir</dt><dd class="col-sm-9">{{ $sale->user->name }}</dd>
        <dt class="col-sm-3">Status</dt><dd class="col-sm-9">@include('partials.status-badge', ['status' => $sale->status])</dd>
        <dt class="col-sm-3">Total</dt><dd class="col-sm-9 fw-bold">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</dd>
    </dl>
</div></div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light"><tr><th>SKU</th><th>Produk</th><th class="text-end">Jumlah</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $item->product->sku }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
