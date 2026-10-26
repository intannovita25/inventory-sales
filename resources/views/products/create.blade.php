@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
<h3 class="mb-3">Tambah Produk</h3>
<div class="card"><div class="card-body">
    @include('products._form', ['action' => route('products.store'), 'method' => 'POST'])
</div></div>
@endsection
