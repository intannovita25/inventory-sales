<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $users = User::query()
            ->when($filters['q'] ?? null, function ($q, $v) {
                $q->where(fn ($w) => $w->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%"));
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create', ['user' => new User(['role' => User::ROLE_STAFF])]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create($request->validated()); // password otomatis di-hash oleh cast 'hashed'

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // Password kosong = tidak diubah
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Cegah admin mengunci dirinya sendiri
        if ($user->id === $request->user()->id && $data['role'] !== User::ROLE_ADMIN) {
            return back()
                ->withErrors(['role' => 'Anda tidak dapat menurunkan role akun Anda sendiri.'])
                ->withInput();
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('users.index')
                ->withErrors(['user' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        if ($user->sales()->exists() || $user->purchases()->exists()) {
            return redirect()->route('users.index')
                ->withErrors(['user' => "Pengguna {$user->name} memiliki riwayat transaksi dan tidak dapat dihapus."]);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
