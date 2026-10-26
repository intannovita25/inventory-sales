<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'low_stock' => ['nullable', 'boolean'],
        ]);

        // with('category') = eager loading, mencegah N+1 query.
        $products = Product::with('category')
            ->search($filters['q'] ?? null)
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->boolean('low_stock'), fn ($q) => $q->lowStock())
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString(); // filter tetap terbawa saat pindah halaman

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function lowStock(): View
    {
        $products = Product::with('category')
            ->lowStock()
            ->orderBy('stock')
            ->paginate(10);

        return view('products.low-stock', compact('products'));
    }

    public function create(): View
    {
        return view('products.create', [
            'product' => new Product(['stock' => 0, 'minimum_stock' => 5]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        Product::create($request->validated());

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        $product->load('category');

        $recentSales = $product->saleItems()->with('sale')->latest('id')->limit(5)->get();
        $recentPurchases = $product->purchaseItems()->with('purchase')->latest('id')->limit(5)->get();

        return view('products.show', compact('product', 'recentSales', 'recentPurchases'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        // Stok TIDAK diubah lewat form edit; stok hanya berubah lewat transaksi.
        $product->update($request->safe()->except('stock'));

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Produk yang sudah dipakai transaksi tidak boleh dihapus.
        if ($product->hasTransactions()) {
            return redirect()->route('products.index')
                ->withErrors(['product' => "Produk {$product->name} sudah dipakai dalam transaksi dan tidak dapat dihapus."]);
        }

        $product->delete(); // soft delete

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
