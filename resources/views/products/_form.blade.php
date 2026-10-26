<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="sku">SKU</label>
            <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" maxlength="50"
                   class="form-control @error('sku') is-invalid @enderror" required>
        </div>
        <div class="col-md-8">
            <label class="form-label" for="name">Nama Produk</label>
            <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" maxlength="150"
                   class="form-control @error('name') is-invalid @enderror" required>
        </div>

        <div class="col-md-4">
            <label class="form-label" for="category_id">Kategori</label>
            <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">-- pilih kategori --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="purchase_price">Harga Beli (Rp)</label>
            <input type="number" step="0.01" min="0" id="purchase_price" name="purchase_price"
                   value="{{ old('purchase_price', $product->purchase_price) }}"
                   class="form-control @error('purchase_price') is-invalid @enderror" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="selling_price">Harga Jual (Rp)</label>
            <input type="number" step="0.01" min="0" id="selling_price" name="selling_price"
                   value="{{ old('selling_price', $product->selling_price) }}"
                   class="form-control @error('selling_price') is-invalid @enderror" required>
        </div>

        <div class="col-md-4">
            <label class="form-label" for="stock">Stok {{ $product->exists ? '(hanya berubah lewat transaksi)' : 'Awal' }}</label>
            @if ($product->exists)
                <input type="number" id="stock" value="{{ $product->stock }}" class="form-control" disabled>
            @else
                <input type="number" min="0" step="1" id="stock" name="stock" value="{{ old('stock', $product->stock) }}"
                       class="form-control @error('stock') is-invalid @enderror" required>
            @endif
        </div>
        <div class="col-md-4">
            <label class="form-label" for="minimum_stock">Stok Minimum</label>
            <input type="number" min="0" step="1" id="minimum_stock" name="minimum_stock"
                   value="{{ old('minimum_stock', $product->minimum_stock) }}"
                   class="form-control @error('minimum_stock') is-invalid @enderror" required>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
