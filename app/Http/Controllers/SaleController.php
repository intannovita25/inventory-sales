<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::in([Sale::STATUS_COMPLETED, Sale::STATUS_CANCELLED])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $sales = Sale::with('user')
            ->withCount('items')
            ->visibleTo($request->user())
            ->filter($filters)
            ->latest('sold_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create(): View
    {
        $productOptions = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'label' => "{$p->sku} - {$p->name} (stok {$p->stock})",
                'price' => (float) $p->selling_price,
                'stock' => $p->stock,
            ])
            ->values();

        return view('sales.create', compact('productOptions'));
    }

    public function store(SaleRequest $request, SaleService $service): RedirectResponse
    {
        // Hanya product_id & quantity yang dipakai. Harga & total dihitung server.
        $sale = $service->create($request->user(), $request->validated('items'));

        return redirect()->route('sales.show', $sale)
            ->with('success', "Penjualan {$sale->sale_number} berhasil disimpan. Stok telah dikurangi.");
    }

    public function show(Request $request, Sale $sale): View
    {
        $this->ensureCanView($request, $sale);

        $sale->load(['user', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale, SaleService $service): RedirectResponse
    {
        $service->cancel($sale->id);

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Penjualan dibatalkan dan stok telah dipulihkan.');
    }

    private function ensureCanView(Request $request, Sale $sale): void
    {
        abort_unless($request->user()->isAdmin() || $sale->user_id === $request->user()->id, 403);
    }
}
