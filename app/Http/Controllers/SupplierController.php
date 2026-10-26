<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $suppliers = Supplier::query()
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create', ['supplier' => new Supplier()]);
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());

        return redirect()->route('suppliers.index')->with('success', 'Pemasok berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')->with('success', 'Pemasok berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchases()->exists()) {
            return redirect()->route('suppliers.index')
                ->withErrors(['supplier' => "Pemasok {$supplier->name} sudah memiliki transaksi pembelian dan tidak dapat dihapus."]);
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Pemasok berhasil dihapus.');
    }
}
