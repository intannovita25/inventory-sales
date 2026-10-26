<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="name">Nama</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="100"
                   class="form-control @error('name') is-invalid @enderror" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" maxlength="150"
                   class="form-control @error('email') is-invalid @enderror" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="role">Role</label>
            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin / Owner</option>
                <option value="staff" @selected(old('role', $user->role) === 'staff')>Staff</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="password">Password {{ $user->exists ? '(kosongkan jika tidak diubah)' : '' }}</label>
            <input type="password" id="password" name="password" autocomplete="new-password"
                   class="form-control @error('password') is-invalid @enderror" {{ $user->exists ? '' : 'required' }}>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="form-control">
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
