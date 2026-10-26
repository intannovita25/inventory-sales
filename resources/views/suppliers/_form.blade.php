<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-12">
            <label class="form-label" for="name">Nama Pemasok</label>
            <input type="text" id="name" name="name" value="{{ old('name', $supplier->name) }}" maxlength="150"
                   class="form-control @error('name') is-invalid @enderror" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="phone">Telepon</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $supplier->phone) }}" maxlength="30"
                   class="form-control @error('phone') is-invalid @enderror">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $supplier->email) }}" maxlength="150"
                   class="form-control @error('email') is-invalid @enderror">
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
