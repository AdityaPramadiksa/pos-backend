<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Expense; // 🔥 Tambahkan model Expense
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Setup Range Tanggal (Default WITA)
        $tz = 'Asia/Makassar';
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date, $tz)->startOfDay()
            : Carbon::now($tz)->subDays(7)->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date, $tz)->endOfDay()
            : Carbon::now($tz)->endOfDay();

        // 2. Query Utama (Hanya yang PAID dan TIDAK VOID)
        $query = Order::where('status', 'paid')
                      ->whereBetween('created_at', [$startDate, $endDate]);

        // 3. Rekap Penjualan per Tipe (Dine In vs Online)
        $salesByType = (clone $query)
            ->select('order_type', DB::raw('SUM(total_price) as total_amount'), DB::raw('count(*) as total_orders'))
            ->groupBy('order_type')
            ->get();

        // 4. Rekap Penjualan per Metode Bayar
        $salesByPayment = (clone $query)
            ->select('payment_method', DB::raw('SUM(total_price) as total_amount'))
            ->groupBy('payment_method')
            ->get();

        // 5. Menu Terlaris (Top 5)
        $topMenus = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('menus', 'order_items.menu_id', '=', 'menus.id')
            ->select('menus.name',
                DB::raw('SUM(order_items.qty) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'))
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('menus.name')
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();

        // 6. HITUNG TOTAL PENGELUARAN (Petty Cash) 🔥
        $totalExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // 7. Summary Total
        $totalRevenue = $query->sum('total_price');
        $summary = [
            'total_revenue' => $totalRevenue, // Omzet kotor
            'total_expenses' => $totalExpenses, // Total biaya keluar 🔥
            'net_profit' => $totalRevenue - $totalExpenses, // Keuntungan Bersih 🔥
            'total_orders' => $query->count(),
            'total_items_sold' => DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 'paid')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->sum('qty'),
        ];

        return view('admin.report.index', compact(
            'topMenus',
            'salesByType',
            'salesByPayment',
            'summary',
            'startDate',
            'endDate'
        ));
    }
}
