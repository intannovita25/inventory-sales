@extends('layouts.app')

@section('title', 'Ubah Pengguna')

@section('content')
<h3 class="mb-3">Ubah Pengguna</h3>
<div class="card"><div class="card-body">
    @include('users._form', ['action' => route('users.update', $user), 'method' => 'PUT'])
</div></div>
@endsection
