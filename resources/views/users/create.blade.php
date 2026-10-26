@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
<h3 class="mb-3">Tambah Pengguna</h3>
<div class="card"><div class="card-body">
    @include('users._form', ['action' => route('users.store'), 'method' => 'POST'])
</div></div>
@endsection
