@extends('layouts.app')

@section('title', 'Tambah Pemasok')

@section('content')
<h3 class="mb-3">Tambah Pemasok</h3>
<div class="card"><div class="card-body">
    @include('suppliers._form', ['action' => route('suppliers.store'), 'method' => 'POST'])
</div></div>
@endsection
