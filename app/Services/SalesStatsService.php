<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Angka penjualan untuk dashboard, laporan, dan ekspor admin.
 * Penjualan dihitung saat uangnya diterima (paid_at), bukan saat order dibuat,
 * supaya bill gantung yang dilunasi besok masuk ke hari pelunasannya.
 */
class SalesStatsService
{
    public const TZ = 'Asia/Makassar';

    /** Order lunas dalam rentang waktu */
    public static function paidOrders(Carbon $start, Carbon $end): Collection
    {
        return Order::with('user')
            ->where('status', 'paid')
            ->whereRaw('COALESCE(paid_at, created_at) BETWEEN ? AND ?', [$start, $end])
            ->orderByRaw('COALESCE(paid_at, created_at)')
            ->get();
    }

    public static function paidTime(Order $order): Carbon
    {
        return Carbon::parse($order->paid_at ?? $order->created_at)->setTimezone(self::TZ);
    }

    public static function lowStockThreshold(): int
    {
        return max(0, (int) Setting::getValue('low_stock_threshold', 10));
    }

    /** Ringkasan angka utama */
    public static function summary(Collection $paid, Carbon $start, Carbon $end): array
    {
        $bills = $paid->count();
        $net = (int) $paid->sum('total_price');
        $expenses = (int) Expense::whereBetween('created_at', [$start, $end])->sum('amount');

        return [
            'net_sales'      => $net,
            'gross_sales'    => (int) $paid->sum('subtotal'),
            'total_tax'      => (int) $paid->sum('tax_amount'),
            'total_discount' => (int) $paid->sum('discount_amount'),
            'bills'          => $bills,
            'average'        => $bills > 0 ? (int) round($net / $bills) : 0,
            'portions'       => (int) DB::table('order_items')->whereIn('order_id', $paid->pluck('id'))->sum('qty'),
            'expenses'       => $expenses,
            'net_after_expenses' => $net - $expenses,
        ];
    }

    /**
     * Penjualan per jam (gabungan semua hari dalam rentang).
     * Hanya jam operasional yang punya transaksi, minimal 10:00–21:00.
     */
    public static function hourly(Collection $paid): array
    {
        $byHour = array_fill(0, 24, 0);
        foreach ($paid as $order) {
            $byHour[(int) self::paidTime($order)->format('G')] += (int) $order->total_price;
        }

        $active = array_keys(array_filter($byHour));
        $from = min(array_merge([10], $active));
        $to = max(array_merge([21], $active));

        $rows = [];
        for ($hour = $from; $hour <= $to; $hour++) {
            $rows[] = ['hour' => $hour, 'total' => $byHour[$hour]];
        }

        $max = max(1, max(array_column($rows, 'total')));
        $peak = collect($rows)->sortByDesc('total')->first();

        return [
            'rows' => array_map(fn ($r) => $r + ['percent' => round($r['total'] / $max * 100, 1)], $rows),
            'max'  => $max,
            'peak' => $peak && $peak['total'] > 0 ? $peak['hour'] : null,
        ];
    }

    /** Menu terlaris beserta sisa stok saat ini */
    public static function topMenus(Collection $paid, int $limit = 5): Collection
    {
        return DB::table('order_items')
            ->leftJoin('menus', 'menus.id', '=', 'order_items.menu_id')
            ->whereIn('order_items.order_id', $paid->pluck('id'))
            ->select(
                'order_items.menu_id',
                DB::raw("COALESCE(menus.name, 'Menu dihapus') as name"),
                'menus.stock',
                DB::raw('SUM(order_items.qty) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('order_items.menu_id', 'menus.name', 'menus.stock')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();
    }

    /** Menu yang stoknya habis atau sudah di bawah batas peringatan */
    public static function stockAlerts(): Collection
    {
        $threshold = self::lowStockThreshold();

        return Menu::where('is_available', true)
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->get(['id', 'name', 'stock']);
    }

    /** Rentang waktu untuk filter periode di dashboard */
    public static function period(string $period): array
    {
        $now = Carbon::now(self::TZ);

        return match ($period) {
            '7d'  => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(), '7 hari terakhir'],
            '30d' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay(), '30 hari terakhir'],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Hari ini'],
        };
    }

    /** Perbandingan dengan periode sebelumnya (hari ini dibanding hari yang sama minggu lalu) */
    public static function comparison(string $period, Carbon $start, Carbon $end): array
    {
        if ($period === 'today') {
            // Sampai jam yang sama, supaya hari yang belum selesai tidak terlihat turun
            $prevStart = $start->copy()->subWeek();
            $prevEnd = Carbon::now(self::TZ)->subWeek();
            $label = $prevStart->locale('id')->isoFormat('dddd') . ' lalu';
        } else {
            $days = $start->diffInDays($end) + 1;
            $prevStart = $start->copy()->subDays($days);
            $prevEnd = $end->copy()->subDays($days);
            $label = $days . ' hari sebelumnya';
        }

        $previous = (int) Order::where('status', 'paid')
            ->whereRaw('COALESCE(paid_at, created_at) BETWEEN ? AND ?', [$prevStart, $prevEnd])
            ->sum('total_price');

        return ['previous' => $previous, 'label' => $label];
    }
}
