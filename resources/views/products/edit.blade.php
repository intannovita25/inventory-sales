@extends('layouts.app')

@section('title', 'Ubah Produk')

@section('content')
<h3 class="mb-3">Ubah Produk</h3>
<div class="card"><div class="card-body">
    @include('products._form', ['action' => route('products.update', $product), 'method' => 'PUT'])
</div></div>
@endsection
