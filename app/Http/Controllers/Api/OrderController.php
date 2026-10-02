<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Menu;
use App\Models\Discount;
use App\Models\Settlement;
use App\Models\Setting;
use App\Models\User;
use App\Services\ShiftReportService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * 1. Simpan Transaksi (Checkout / Simpan ke Meja)
     */
    public function store(Request $request)
    {
        // Normalisasi input agar case-insensitive
        if ($request->has('order_type')) {
            $request->merge(['order_type' => str_replace(' ', '_', strtolower($request->order_type))]);
        }
        if ($request->filled('payment_method')) {
            $request->merge(['payment_method' => strtolower($request->payment_method)]);
        }
        if ($request->filled('delivery_platform')) {
            $request->merge(['delivery_platform' => strtolower($request->delivery_platform)]);
        }

        $request->validate([
            'order_type' => 'required|in:dine_in,to_go,delivery',
            'delivery_platform' => 'nullable|required_if:order_type,delivery|in:gojek,grab,shopee',
            'payment_method' => 'nullable|in:cash,qris,debit,credit,delivery',
            'amount_paid' => 'nullable|numeric|min:0',
            'is_pending' => 'nullable|boolean',
            'customer_name' => 'nullable|string|max:100',
            'table_number' => 'nullable|string|max:10',
            'discount_id' => 'nullable|exists:discounts,id',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.note' => 'nullable|string', // 🔥 FIX: Validasi Note per item
            'notes' => 'nullable|string',
        ], [
            'delivery_platform.required_if' => 'Pilih platform delivery (Gojek / Grab / ShopeeFood).',
        ]);

        $isDelivery = $request->order_type === 'delivery';
        $method = $request->payment_method;

        if ($method === 'delivery' && !$isDelivery) {
            return response()->json(['status' => 'error', 'message' => 'Pembayaran via platform hanya untuk order delivery.'], 422);
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $orderItemsData = [];

            foreach ($request->items as $item) {
                $menu = Menu::lockForUpdate()->findOrFail($item['menu_id']);

                if ($menu->stock < $item['qty']) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => "Stok {$menu->name} tidak cukup! (sisa {$menu->stock})"], 400);
                }

                // LOGIKA HARGA BERDASARKAN TIPE ORDER
                $priceToUse = $isDelivery
                    ? ($menu->price_online > 0 ? $menu->price_online : $menu->price)
                    : ($menu->price_dine_in > 0 ? $menu->price_dine_in : $menu->price);

                $itemSubtotal = $priceToUse * $item['qty'];
                $subtotal += $itemSubtotal;

                $orderItemsData[] = [
                    'menu_id' => $menu->id,
                    'price'   => $priceToUse, // Harga saat kejadian
                    'qty'     => $item['qty'],
                    'subtotal'=> $itemSubtotal,
                    'note'    => isset($item['note']) ? $item['note'] : null, // 🔥 FIX: Simpan Note ke array
                ];

                $menu->decrement('stock', $item['qty']);
            }

            // Hitung Diskon (dibulatkan & tidak boleh melebihi subtotal)
            $discountAmount = 0;
            if ($request->discount_id) {
                $discount = Discount::find($request->discount_id);
                if ($discount && $discount->is_active) {
                    $discountAmount = ($discount->type == 'percentage') ? ($subtotal * ($discount->value / 100)) : $discount->value;
                    $discountAmount = (int) min(round($discountAmount), $subtotal);
                }
            }

            // Pajak / PB1 dari Settings, dibulatkan agar sama persis dengan aplikasi kasir
            $taxAmount = (int) round($subtotal * (self::taxRate() / 100));
            $totalPrice = max(0, ($subtotal + $taxAmount) - $discountAmount);

            // Aplikasi lama tidak mengirim is_pending: uang kurang dianggap simpan ke meja
            $isPending = $request->has('is_pending')
                ? $request->boolean('is_pending')
                : ((int) $request->amount_paid < $totalPrice && $method !== 'delivery');

            $amountPaid = 0;
            $changeAmount = 0;

            if ($isPending) {
                $method = null;
            } else {
                if (!$method) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Metode pembayaran wajib dipilih.'], 422);
                }

                if ($method === 'cash') {
                    $amountPaid = (int) $request->amount_paid;
                    if ($amountPaid < $totalPrice) {
                        DB::rollBack();
                        return response()->json(['status' => 'error', 'message' => 'Uang diterima kurang dari total Rp ' . number_format($totalPrice, 0, ',', '.')], 422);
                    }
                    $changeAmount = $amountPaid - $totalPrice;
                } else {
                    // Non tunai selalu uang pas
                    $amountPaid = $totalPrice;
                }
            }

            $now = Carbon::now('Asia/Makassar');
            $receiptNumber = 'INV-' . $now->format('Ymd') . '-' . str_pad((Order::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'receipt_number'    => $receiptNumber,
                'user_id'           => $request->user()->id,
                'customer_name'     => $request->customer_name ?: 'Pelanggan Umum',
                'table_number'      => $request->table_number,
                'order_type'        => $request->order_type,
                'delivery_platform' => $isDelivery ? $request->delivery_platform : null,
                'subtotal'          => $subtotal,
                'discount_amount'   => $discountAmount,
                'tax_amount'        => $taxAmount,
                'total_price'       => $totalPrice,
                'payment_method'    => $method,
                'amount_paid'       => $amountPaid,
                'change_amount'     => $changeAmount,
                'status'            => $isPending ? 'pending' : 'paid',
                'notes'             => $request->notes,
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // Uang masuk dicatat di shift kasir yang menerima pembayaran
            if (!$isPending) {
                $this->attachToShift($order, $request->user(), $now);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'data' => $order->load(['items.menu', 'user'])], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private static function taxRate(): float
    {
        $taxSetting = Setting::where('key', 'tax_rate')->value('value');
        return $taxSetting !== null ? (float) $taxSetting : 10; // Aman, pasti 10 jika kosong
    }

    /**
     * Helper: catat order lunas ke shift aktif kasir & perbarui total settlement
     */
    private function attachToShift(Order $order, User $cashier, Carbon $paidAt)
    {
        $shift = ShiftReportService::activeShift($cashier);

        $order->update([
            'settlement_id' => $shift->id,
            'paid_at'       => $paidAt,
        ]);

        ShiftReportService::syncTotals($shift);
    }

    /**
     * 2. Riwayat Transaksi Hari Ini (WITA)
     */
    public function history()
    {
        $today = Carbon::now('Asia/Makassar')->toDateString();

        // Termasuk bill lama yang baru dilunasi hari ini
        $orders = Order::with(['items.menu', 'user'])
            ->where(function ($query) use ($today) {
                $query->whereDate('created_at', $today)
                    ->orWhereDate('paid_at', $today);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $orders]);
    }

    /**
     * 3. Rekapitulasi shift yang sedang berjalan (X-Report, tanpa menutup shift)
     */
    public function recapitulation(Request $request)
    {
        $shift = ShiftReportService::activeShift($request->user());

        return response()->json([
            'status' => 'success',
            'data' => ShiftReportService::build($shift),
        ]);
    }

    /**
     * 4. Daftar Pesanan Gantung (Pending Bills)
     */
    public function getPendingBills()
    {
        $bills = Order::with(['items.menu', 'user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $bills]);
    }

    /**
     * 5. Pembatalan Transaksi (VOID)
     */
    public function voidOrder(Request $request, $id)
    {
        $request->validate([
            'admin_pin' => 'required|string',
            'void_reason' => 'required|string|min:5'
        ]);

        $admin = User::where('role', 'admin')->where('pin', $request->admin_pin)->first();
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => 'PIN Admin salah!'], 401);
        }

        DB::beginTransaction();
        try {
            $order = Order::with('items')->lockForUpdate()->findOrFail($id);

            if (strtolower($order->status) === 'void') {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Order sudah di-void.'], 400);
            }

            foreach ($order->items as $item) {
                Menu::where('id', $item->menu_id)->increment('stock', $item->qty);
            }

            $order->update([
                'status' => 'void',
                'void_by' => $admin->id,
                'void_reason' => $request->void_reason
            ]);

            // Total shift dihitung ulang supaya uang order yang di-void tidak ikut terhitung
            if ($order->settlement_id && ($settlement = Settlement::find($order->settlement_id))) {
                ShiftReportService::syncTotals($settlement);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Transaksi berhasil dibatalkan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 6. Pelunasan Pesanan Gantung (Pay Pending Bill)
     */
    public function payPendingOrder(Request $request, $id)
    {
        if ($request->filled('payment_method')) {
            $request->merge(['payment_method' => strtolower($request->payment_method)]);
        }

        $request->validate([
            'payment_method' => 'required|in:cash,qris,debit,credit,delivery',
            'amount_paid'    => 'nullable|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $order = Order::lockForUpdate()->findOrFail($id);

            if ($order->status !== 'pending') {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Pesanan ini sudah lunas atau dibatalkan.'], 400);
            }

            $paymentMethod = $request->payment_method;

            if ($paymentMethod === 'delivery' && $order->order_type !== 'delivery') {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Pembayaran via platform hanya untuk order delivery.'], 422);
            }

            // Hitung Uang Masuk dan Kembalian
            $amountPaid = $order->total_price;
            $changeAmount = 0;

            if ($paymentMethod === 'cash') {
                $amountPaid = (int) ($request->amount_paid ?? $order->total_price);
                if ($amountPaid < $order->total_price) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Uang diterima kurang dari total Rp ' . number_format($order->total_price, 0, ',', '.')], 422);
                }
                $changeAmount = $amountPaid - $order->total_price;
            }

            $order->update([
                'status'         => 'paid',
                'payment_method' => $paymentMethod,
                'amount_paid'    => $amountPaid,
                'change_amount'  => $changeAmount,
                // created_at sengaja tidak diubah agar waktu order awal tetap valid
            ]);

            // Uang masuk ke shift kasir yang melunasi, bukan yang membuat bill
            $this->attachToShift($order, $request->user(), Carbon::now('Asia/Makassar'));

            DB::commit();

            // Load relasi agar struk aman
            $order->load(['items.menu', 'user']);

            return response()->json([
                'status'  => 'success',
                'message' => 'Pembayaran Berhasil!',
                'data'    => $order
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
