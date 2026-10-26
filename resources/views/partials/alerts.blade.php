@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Terjadi kesalahan:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
