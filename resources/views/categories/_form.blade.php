<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="mb-3">
        <label class="form-label" for="name">Nama Kategori</label>
        <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" maxlength="100"
               class="form-control @error('name') is-invalid @enderror" required>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
