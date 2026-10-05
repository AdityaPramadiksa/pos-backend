<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SalesStatsService;
use App\Services\ShiftReportService;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private function range(Request $request): array
    {
        $tz = SalesStatsService::TZ;
        $start = $request->filled('start_date')
            ? Carbon::parse($request->start_date, $tz)->startOfDay()
            : Carbon::now($tz)->subDays(6)->startOfDay();
        $end = $request->filled('end_date')
            ? Carbon::parse($request->end_date, $tz)->endOfDay()
            : Carbon::now($tz)->endOfDay();

        if ($end->lt($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }

    public function index(Request $request)
    {
        [$startDate, $endDate] = $this->range($request);

        $paid = SalesStatsService::paidOrders($startDate, $endDate);
        $summary = SalesStatsService::summary($paid, $startDate, $endDate);
        $payments = ShiftReportService::paymentBreakdown($paid);
        $paymentMax = max(1, max(array_column($payments, 'total')));
        $topMenus = SalesStatsService::topMenus($paid, 10);

        // Penjualan per hari
        $daily = $paid->groupBy(fn ($o) => SalesStatsService::paidTime($o)->toDateString())
            ->map(fn ($rows, $date) => [
                'date' => Carbon::parse($date, SalesStatsService::TZ),
                'bills' => $rows->count(),
                'total' => (int) $rows->sum('total_price'),
            ])->values();

        return view('admin.report.index', compact(
            'startDate', 'endDate', 'summary', 'payments', 'paymentMax', 'topMenus', 'daily'
        ));
    }

    /**
     * Unduh laporan sebagai CSV (dibuka langsung di Excel).
     * type=transaksi: satu baris per transaksi; type=menu: penjualan per menu.
     */
    public function export(Request $request): StreamedResponse
    {
        [$start, $end] = $this->range($request);
        $type = $request->type === 'menu' ? 'menu' : 'transaksi';
        $paid = SalesStatsService::paidOrders($start, $end);

        $filename = "laporan-{$type}-{$start->format('Ymd')}-{$end->format('Ymd')}.csv";

        return response()->streamDownload(function () use ($type, $paid) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
            // Excel berbahasa Indonesia memakai titik koma sebagai pemisah kolom
            $put = fn (array $row) => fputcsv($out, $row, ';');

            if ($type === 'menu') {
                $put(['Kategori', 'Menu', 'Harga satuan', 'Porsi', 'Total']);
                $rows = DB::table('order_items')
                    ->leftJoin('menus', 'menus.id', '=', 'order_items.menu_id')
                    ->leftJoin('categories', 'categories.id', '=', 'menus.category_id')
                    ->whereIn('order_items.order_id', $paid->pluck('id'))
                    // Alias sengaja beda dari nama kolom asli supaya GROUP BY tidak ambigu
                    ->select(
                        DB::raw("COALESCE(categories.name, 'Lainnya') as category_label"),
                        DB::raw("COALESCE(menus.name, 'Menu dihapus') as menu_label"),
                        'order_items.price',
                        DB::raw('SUM(order_items.qty) as total_qty'),
                        DB::raw('SUM(order_items.subtotal) as total_sales')
                    )
                    ->groupBy('category_label', 'menu_label', 'order_items.price')
                    ->orderBy('category_label')->orderByDesc('total_qty')
                    ->get();
                foreach ($rows as $r) {
                    $put([$r->category_label, $r->menu_label, (int) $r->price, (int) $r->total_qty, (int) $r->total_sales]);
                }
            } else {
                $put(['Tanggal', 'Jam', 'No. struk', 'Kasir', 'Jenis', 'Platform', 'Cara bayar', 'Subtotal', 'Diskon', 'Pajak', 'Total']);
                foreach ($paid as $o) {
                    $time = SalesStatsService::paidTime($o);
                    $put([
                        $time->format('Y-m-d'),
                        $time->format('H:i'),
                        $o->receipt_number,
                        $o->user->name ?? '-',
                        ['dine_in' => 'Makan di sini', 'to_go' => 'Bungkus', 'delivery' => 'Ojol'][$o->order_type] ?? $o->order_type,
                        $o->delivery_platform ? ucfirst($o->delivery_platform) : '',
                        $o->payment_method === 'delivery' ? 'Aplikasi ojol' : strtoupper((string) $o->payment_method),
                        (int) $o->subtotal,
                        (int) $o->discount_amount,
                        (int) $o->tax_amount,
                        (int) $o->total_price,
                    ]);
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
