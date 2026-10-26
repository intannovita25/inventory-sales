<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::in([Purchase::STATUS_COMPLETED, Purchase::STATUS_CANCELLED])],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $purchases = Purchase::with(['supplier', 'user'])
            ->withCount('items')
            ->visibleTo($request->user())
            ->filter($filters)
            ->latest('purchased_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create(): View
    {
        $productOptions = Product::orderBy('name')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'label' => "{$p->sku} - {$p->name} (stok {$p->stock})",
                'price' => (float) $p->purchase_price,
                'stock' => $p->stock,
            ])
            ->values();

        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.create', compact('productOptions', 'suppliers'));
    }

    public function store(PurchaseRequest $request, PurchaseService $service): RedirectResponse
    {
        $purchase = $service->create(
            $request->user(),
            (int) $request->validated('supplier_id'),
            $request->validated('items')
        );

        return redirect()->route('purchases.show', $purchase)
            ->with('success', "Pembelian {$purchase->purchase_number} berhasil disimpan. Stok telah bertambah.");
    }

    public function show(Request $request, Purchase $purchase): View
    {
        abort_unless($request->user()->isAdmin() || $purchase->user_id === $request->user()->id, 403);

        $purchase->load(['supplier', 'user', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Purchase $purchase, PurchaseService $service): RedirectResponse
    {
        $service->cancel($purchase->id);

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Pembelian dibatalkan dan stok telah ditarik kembali.');
    }
}
