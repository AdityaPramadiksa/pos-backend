<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Settlement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Satu sumber kebenaran untuk angka shift: dipakai rekap berjalan (X-report),
 * tutup shift (Z-report), cetak ulang, dan halaman admin.
 */
class ShiftReportService
{
    public const METHOD_LABELS = [
        'cash'   => 'CASH',
        'qris'   => 'QRIS',
        'debit'  => 'DEBIT',
        'credit' => 'CREDIT',
    ];

    public const PLATFORM_LABELS = [
        'gojek'  => 'GOJEK / GOFOOD',
        'grab'   => 'GRAB / GRABFOOD',
        'shopee' => 'SHOPEEFOOD',
    ];

    public const ORDER_TYPE_LABELS = [
        'dine_in'  => 'DINE IN',
        'to_go'    => 'TO GO',
        'delivery' => 'DELIVERY',
    ];

    /**
     * Ambil shift kasir yang sedang berjalan, buka otomatis jika belum ada.
     */
    public static function activeShift(User $user): Settlement
    {
        $shift = Settlement::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('id')
            ->first();

        if (!$shift) {
            $shift = Settlement::create([
                'user_id'       => $user->id,
                'starting_cash' => (int) Setting::getValue('starting_cash', 0),
                'status'        => 'open',
            ]);
        }

        return $shift;
    }

    /**
     * Hitung ulang kolom total_*_sales & total_expenses dari data order/pengeluaran shift tsb.
     */
    public static function syncTotals(Settlement $settlement): void
    {
        $totals = Order::where('settlement_id', $settlement->id)
            ->where('status', 'paid')
            ->select('payment_method', DB::raw('SUM(total_price) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $settlement->update([
            'total_cash_sales'     => (int) ($totals['cash'] ?? 0),
            'total_qris_sales'     => (int) ($totals['qris'] ?? 0),
            'total_debit_sales'    => (int) ($totals['debit'] ?? 0),
            'total_credit_sales'   => (int) ($totals['credit'] ?? 0),
            'total_delivery_sales' => (int) ($totals['delivery'] ?? 0),
            'total_expenses'       => (int) Expense::where('settlement_id', $settlement->id)->sum('amount'),
        ]);
    }

    /**
     * Laporan lengkap satu shift.
     */
    public static function build(Settlement $settlement): array
    {
        $settlement->loadMissing('user');

        $orders = Order::where('settlement_id', $settlement->id)->get();
        $paid   = $orders->where('status', 'paid');
        $void   = $orders->where('status', 'void');

        $expenses      = Expense::where('settlement_id', $settlement->id)->orderBy('created_at')->get();
        $totalExpenses = (int) $expenses->sum('amount');

        // Bill gantung berlaku se-toko (bisa dilunasi kasir/shift berikutnya)
        $pending = Order::where('status', 'pending')->get(['id', 'total_price']);

        $startingCash = (int) $settlement->starting_cash;
        $cashSales    = (int) $paid->where('payment_method', 'cash')->sum('total_price');
        $expectedCash = $startingCash + $cashSales - $totalExpenses;
        $actualCash   = $settlement->actual_cash_on_hand !== null ? (int) $settlement->actual_cash_on_hand : null;

        return [
            'settlement' => [
                'id'        => $settlement->id,
                'status'    => $settlement->status,
                'cashier'   => $settlement->user->name ?? 'Kasir',
                'opened_at' => optional($settlement->created_at)->format('Y-m-d H:i:s'),
                'closed_at' => optional($settlement->closed_at)->format('Y-m-d H:i:s'),
                'notes'     => $settlement->notes,
            ],
            'summary' => [
                'starting_cash'        => $startingCash,
                'payments_in_cash'     => $cashSales,
                'total_expenses'       => $totalExpenses,
                'expected_ending_cash' => $expectedCash,
                'actual_cash'          => $actualCash,
                'cash_difference'      => $actualCash !== null ? $actualCash - $expectedCash : null,
                'non_cash_sales'       => (int) $paid->where('payment_method', '!=', 'cash')->sum('total_price'),
                'gross_sales'          => (int) $paid->sum('subtotal'),
                'total_tax'            => (int) $paid->sum('tax_amount'),
                'total_discount'       => (int) $paid->sum('discount_amount'),
                'net_sales'            => (int) $paid->sum('total_price'),
                'total_bills'          => $paid->count(),
                'void_count'           => $void->count(),
                'void_total'           => (int) $void->sum('total_price'),
                'pending_count'        => $pending->count(),
                'pending_total'        => (int) $pending->sum('total_price'),
            ],
            'payments'          => self::paymentBreakdown($paid),
            'order_types'       => self::orderTypeBreakdown($paid),
            'platforms'         => self::platformBreakdown($paid),
            'expenses'          => $expenses->map(fn ($e) => [
                'description' => $e->description,
                'amount'      => (int) $e->amount,
                'time'        => optional($e->created_at)->format('H:i'),
            ])->values(),
            'menus_by_category' => self::menuBreakdown($settlement->id),
        ];
    }

    /**
     * Uang masuk per cara bayar. Pembayaran via ojol (payment_method = delivery)
     * dipecah per platform supaya terlihat Gojek/Grab/ShopeeFood masing-masing berapa.
     */
    /**
     * Ringkasan kas satu shift untuk tabel riwayat: penjualan, uang tunai
     * yang seharusnya ada, uang yang dihitung kasir, dan selisihnya.
     */
    public static function cashSummary(Settlement $settlement): array
    {
        if ($settlement->status === 'open') {
            self::syncTotals($settlement);
        }

        $sales = (int) ($settlement->total_cash_sales + $settlement->total_qris_sales
            + $settlement->total_debit_sales + $settlement->total_credit_sales
            + $settlement->total_delivery_sales);
        $expected = (int) ($settlement->starting_cash + $settlement->total_cash_sales - $settlement->total_expenses);
        $actual = $settlement->status === 'closed' && $settlement->actual_cash_on_hand !== null
            ? (int) $settlement->actual_cash_on_hand
            : null;

        return [
            'sales'      => $sales,
            'expected'   => $expected,
            'actual'     => $actual,
            'difference' => $actual !== null ? $actual - $expected : null,
        ];
    }

    public static function paymentBreakdown($paid): array
    {
        $rows = [];

        foreach (self::METHOD_LABELS as $key => $label) {
            $subset = $paid->where('payment_method', $key);
            $rows[] = self::paymentRow($key, $label, $subset);
        }

        $viaPlatform = $paid->where('payment_method', 'delivery');

        foreach (self::PLATFORM_LABELS as $key => $label) {
            $subset = $viaPlatform->where('delivery_platform', $key);
            $rows[] = self::paymentRow($key, $label, $subset);
        }

        $others = $viaPlatform->whereNotIn('delivery_platform', array_keys(self::PLATFORM_LABELS));
        if ($others->count() > 0) {
            $rows[] = self::paymentRow('delivery_other', 'DELIVERY LAINNYA', $others);
        }

        return $rows;
    }

    private static function paymentRow(string $key, string $label, $subset): array
    {
        return [
            'payment_method' => $key,
            'label'          => $label,
            'qty'            => $subset->count(),
            'total'          => (int) $subset->sum('total_price'),
        ];
    }

    private static function orderTypeBreakdown($paid): array
    {
        $rows = [];

        foreach (self::ORDER_TYPE_LABELS as $key => $label) {
            $subset = $paid->where('order_type', $key);
            $rows[] = [
                'order_type' => $key,
                'label'      => $label,
                'qty'        => $subset->count(),
                'total'      => (int) $subset->sum('total_price'),
            ];
        }

        return $rows;
    }

    /**
     * Semua order delivery per platform, apa pun cara bayarnya.
     */
    private static function platformBreakdown($paid): array
    {
        return $paid->where('order_type', 'delivery')
            ->groupBy(fn ($order) => $order->delivery_platform ?: 'lainnya')
            ->map(fn ($rows, $key) => [
                'platform' => $key,
                'label'    => self::PLATFORM_LABELS[$key] ?? strtoupper($key),
                'qty'      => $rows->count(),
                'total'    => (int) $rows->sum('total_price'),
            ])
            ->values()
            ->all();
    }

    /**
     * Penjualan per menu, dikelompokkan per kategori. Harga dine-in dan online
     * bisa berbeda, jadi menu yang sama dengan harga berbeda ditampilkan terpisah.
     */
    private static function menuBreakdown(int $settlementId): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('menus', 'menus.id', '=', 'order_items.menu_id')
            ->leftJoin('categories', 'categories.id', '=', 'menus.category_id')
            ->where('orders.settlement_id', $settlementId)
            ->where('orders.status', 'paid')
            ->select(
                DB::raw("COALESCE(categories.name, 'Lainnya') as category_name"),
                DB::raw("COALESCE(menus.name, 'Menu Dihapus') as menu_name"),
                'order_items.price',
                DB::raw('SUM(order_items.qty) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_sales')
            )
            ->groupBy('category_name', 'menu_name', 'order_items.price')
            ->orderBy('category_name')
            ->orderByDesc('total_qty')
            ->get();

        return $rows->groupBy('category_name')
            ->map(fn ($items, $category) => [
                'category' => $category,
                'qty'      => (int) $items->sum('total_qty'),
                'total'    => (int) $items->sum('total_sales'),
                'items'    => $items->map(fn ($item) => [
                    'name'  => $item->menu_name,
                    'price' => (int) $item->price,
                    'qty'   => (int) $item->total_qty,
                    'total' => (int) $item->total_sales,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }
}
