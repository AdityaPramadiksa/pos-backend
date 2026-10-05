<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Settlement;
use App\Services\SalesStatsService;
use App\Services\ShiftReportService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = in_array($request->period, ['today', '7d', '30d']) ? $request->period : 'today';
        [$start, $end, $periodLabel] = SalesStatsService::period($period);

        $paid = SalesStatsService::paidOrders($start, $end);
        $summary = SalesStatsService::summary($paid, $start, $end);
        $comparison = SalesStatsService::comparison($period, $start, $end);
        $change = $comparison['previous'] > 0
            ? round(($summary['net_sales'] - $comparison['previous']) / $comparison['previous'] * 100)
            : null;

        $payments = ShiftReportService::paymentBreakdown($paid);
        $paymentMax = max(1, max(array_column($payments, 'total')));

        $hourly = SalesStatsService::hourly($paid);
        $topMenus = SalesStatsService::topMenus($paid);
        $threshold = SalesStatsService::lowStockThreshold();

        // Daftar hal yang perlu ditindaklanjuti admin
        $attention = [];
        foreach (SalesStatsService::stockAlerts() as $menu) {
            $attention[] = $menu->stock <= 0
                ? ['level' => 'bad', 'title' => "{$menu->name} habis", 'detail' => 'Tidak bisa dipesan di aplikasi kasir', 'link' => route('admin.menu.edit', $menu->id), 'action' => 'Isi stok']
                : ['level' => 'warn', 'title' => "{$menu->name} tinggal {$menu->stock} porsi", 'detail' => "Batas peringatan: {$threshold} porsi", 'link' => route('admin.menu.edit', $menu->id), 'action' => 'Isi stok'];
        }

        $pending = Order::where('status', 'pending')->get(['table_number', 'total_price']);
        if ($pending->count() > 0) {
            $tables = $pending->pluck('table_number')->filter()->map(fn ($t) => "Meja $t")->take(3)->implode(', ');
            $attention[] = [
                'level' => 'warn',
                'title' => $pending->count() . ' bill belum dibayar',
                'detail' => trim(($tables ? "$tables · " : '') . 'Rp ' . number_format($pending->sum('total_price'), 0, ',', '.')),
                'link' => route('admin.orders.index', ['status' => 'pending']),
                'action' => 'Lihat',
            ];
        }

        // Shift terakhir: yang berjalan dan yang sudah ditutup
        $shifts = Settlement::with('user')->latest('id')->limit(5)->get()
            ->map(fn ($s) => ['model' => $s] + ShiftReportService::cashSummary($s));

        foreach ($shifts as $shift) {
            $closedRecently = $shift['model']->closed_at && $shift['model']->closed_at->gt(Carbon::now()->subDays(3));
            if ($closedRecently && $shift['difference'] !== null && $shift['difference'] < 0) {
                $attention[] = [
                    'level' => 'bad',
                    'title' => 'Uang tunai shift ' . ($shift['model']->user->name ?? 'kasir') . ' kurang',
                    'detail' => 'Kurang Rp ' . number_format(abs($shift['difference']), 0, ',', '.') . ' · ' . $shift['model']->closed_at->locale('id')->isoFormat('dddd, HH:mm'),
                    'link' => route('admin.settlements.show', $shift['model']->id),
                    'action' => 'Periksa',
                ];
            }
        }

        $openShift = $shifts->first(fn ($s) => $s['model']->status === 'open');

        return view('admin.dashboard', compact(
            'period', 'periodLabel', 'summary', 'change', 'comparison', 'payments', 'paymentMax',
            'hourly', 'topMenus', 'attention', 'shifts', 'openShift', 'threshold'
        ));
    }
}
