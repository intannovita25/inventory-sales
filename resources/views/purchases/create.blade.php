@extends('layouts.app')

@section('title', 'Pembelian Baru')

@section('content')
<h3 class="mb-3">Pembelian / Stock-in Baru</h3>

<form method="POST" action="{{ route('purchases.store') }}">
    @csrf
    <div class="card mb-3"><div class="card-body">
        <div class="mb-3 col-md-5 px-0">
            <label class="form-label" for="supplier_id">Pemasok</label>
            <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                <option value="">-- pilih pemasok --</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>

        @include('transactions._items', ['mode' => 'purchase', 'productOptions' => $productOptions])
    </div></div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success">Simpan Pembelian</button>
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
@endsection
