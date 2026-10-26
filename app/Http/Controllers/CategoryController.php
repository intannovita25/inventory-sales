<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $categories = Category::withCount('products')
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create', ['category' => new Category()]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->withTrashed()->exists()) {
            return redirect()->route('categories.index')
                ->withErrors(['category' => "Kategori {$category->name} masih memiliki produk dan tidak dapat dihapus."]);
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
