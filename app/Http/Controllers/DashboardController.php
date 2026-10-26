<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Admin melihat angka seluruh transaksi, Staff hanya transaksi miliknya.
        $sales = Sale::query()->visibleTo($user)->completed();
        $purchases = Purchase::query()->visibleTo($user)->completed();

        // Semua angka dihitung dengan query agregat dari database (bukan nilai statis).
        $stats = [
            'products' => Product::count(),
            'low_stock' => Product::lowStock()->count(),
            'sales_count' => (clone $sales)->count(),
            'purchases_count' => (clone $purchases)->count(),
            'sales_today' => (clone $sales)->whereDate('sold_at', today())->sum('total'),
            'sales_month' => (clone $sales)
                ->whereBetween('sold_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total'),
        ];

        $lowStockProducts = Product::with('category')->lowStock()->orderBy('stock')->limit(5)->get();
        $recentSales = Sale::with('user')->visibleTo($user)->latest('sold_at')->latest('id')->limit(5)->get();

        return view('dashboard', compact('stats', 'lowStockProducts', 'recentSales'));
    }
}
