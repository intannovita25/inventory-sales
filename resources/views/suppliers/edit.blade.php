@extends('layouts.app')

@section('title', 'Ubah Pemasok')

@section('content')
<h3 class="mb-3">Ubah Pemasok</h3>
<div class="card"><div class="card-body">
    @include('suppliers._form', ['action' => route('suppliers.update', $supplier), 'method' => 'PUT'])
</div></div>
@endsection
