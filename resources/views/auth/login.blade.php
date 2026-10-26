@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5 col-lg-4">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">Inventory &amp; Sales</h3>
            <p class="text-muted mb-0">Silakan login untuk melanjutkan</p>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Masuk</button>
                </form>
            </div>
        </div>

        <p class="text-center text-muted small mt-3">
            Akun demo: <code>admin@example.com</code> / <code>staff@example.com</code><br>
            Password: <code>password</code>
        </p>
    </div>
</div>
@endsection
