<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Menu;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Statistik Hari Ini
        $today = Carbon::today();

        $todayRevenue = Order::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->sum('total_price');

        $todayOrders = Order::whereDate('created_at', $today)->count();

        // 2. Cek Stok Tipis (Kurang dari 10)
        $lowStockMenus = Menu::where('stock', '<', 10)->get();

        // 3. Menu Terlaris (Berdasarkan jumlah qty di order_items)
        // Pastikan relasi 'items' sudah ada di Model Order
        $topMenus = Menu::withCount(['items as total_sold' => function($query) {
            $query->select(\DB::raw('sum(qty)'));
        }])->orderBy('total_sold', 'desc')->take(5)->get();

        // 4. Kirim data ke view dashboard
        return view('admin.dashboard', compact(
            'todayRevenue',
            'todayOrders',
            'lowStockMenus',
            'topMenus'
        ));
    }
}
