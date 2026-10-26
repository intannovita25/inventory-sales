@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
<h3 class="mb-3">Tambah Kategori</h3>
<div class="card"><div class="card-body">
    @include('categories._form', ['action' => route('categories.store'), 'method' => 'POST'])
</div></div>
@endsection
