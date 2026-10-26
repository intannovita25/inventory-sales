@extends('layouts.app')

@section('title', 'Penjualan Baru')

@section('content')
<h3 class="mb-3">Penjualan Baru</h3>

<form method="POST" action="{{ route('sales.store') }}">
    @csrf
    <div class="card mb-3"><div class="card-body">
        @include('transactions._items', ['mode' => 'sale', 'productOptions' => $productOptions])
    </div></div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success">Simpan Penjualan</button>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
@endsection
