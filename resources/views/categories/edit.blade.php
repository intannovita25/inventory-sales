@extends('layouts.app')

@section('title', 'Ubah Kategori')

@section('content')
<h3 class="mb-3">Ubah Kategori</h3>
<div class="card"><div class="card-body">
    @include('categories._form', ['action' => route('categories.update', $category), 'method' => 'PUT'])
</div></div>
@endsection
