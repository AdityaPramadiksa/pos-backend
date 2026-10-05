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
     *
     * Aplikasi kasir mengirim `client_uuid` di setiap pesanan. Kalau pesanan
     * yang sama terkirim dua kali (sinyal putus saat menunggu jawaban server),
     * pesanan yang sudah ada dikembalikan, bukan dibuat lagi.
     *
     * `offline = true` berarti pesanan sudah terjadi di warung saat aplikasi
     * offline: uang sudah diterima dan struk sudah dicetak. Server mencatatnya
     * apa adanya (harga & total dari aplikasi, stok boleh habis) supaya
     * hitungan kas tetap sama dengan uang di laci.
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

        if ($request->filled('client_uuid')) {
            $existing = Order::with(['items.menu', 'user'])->where('client_uuid', $request->client_uuid)->first();
            if ($existing) {
                return response()->json(['status' => 'success', 'duplicate' => true, 'data' => $existing], 200);
            }
        }

        $request->validate([
            'order_type' => 'required|in:dine_in,to_go,delivery',
            'delivery_platform' => 'nullable|required_if:order_type,delivery|in:gojek,grab,shopee',
            'payment_method' => 'nullable|in:cash,qris,debit,credit,delivery',
            'amount_paid' => 'nullable|numeric|min:0',
            'is_pending' => 'nullable|boolean',
            'customer_name' => 'nullable|string|max:100',
            'table_number' => 'nullable|string|max:10',
            'discount_id' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.note' => 'nullable|string',
            'items.*.price' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'client_uuid' => 'nullable|string|max:36',
            'receipt_number' => 'nullable|string|max:40',
            'created_at' => 'nullable|date',
            'offline' => 'nullable|boolean',
            'tax_amount' => 'nullable|integer|min:0',
            'discount_amount' => 'nullable|integer|min:0',
        ], [
            'delivery_platform.required_if' => 'Pilih aplikasi ojol: GoFood, GrabFood, atau ShopeeFood.',
        ]);

        $isDelivery = $request->order_type === 'delivery';
        $method = $request->payment_method;
        $offline = $request->boolean('offline');

        if ($method === 'delivery' && !$isDelivery) {
            return response()->json(['status' => 'error', 'message' => 'Pembayaran lewat aplikasi ojol hanya untuk pesanan ojol.'], 422);
        }

        DB::beginTransaction();
        try {
            try {
                [$subtotal, $orderItemsData] = $this->takeItems($request->items, $isDelivery, $offline);
            } catch (\RuntimeException $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
            }

            if ($offline) {
                // Angka yang sudah tercetak di struk pelanggan
                $discountAmount = (int) min((int) $request->discount_amount, $subtotal);
                $taxAmount = $request->filled('tax_amount')
                    ? (int) $request->tax_amount
                    : (int) round($subtotal * (self::taxRate() / 100));
            } else {
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
            }
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
                    return response()->json(['status' => 'error', 'message' => 'Pilih cara bayar dulu.'], 422);
                }

                if ($method === 'cash') {
                    $amountPaid = (int) $request->amount_paid;
                    if ($amountPaid < $totalPrice && !$offline) {
                        DB::rollBack();
                        return response()->json(['status' => 'error', 'message' => 'Uang diterima kurang dari total Rp ' . number_format($totalPrice, 0, ',', '.')], 422);
                    }
                    $amountPaid = max($amountPaid, $totalPrice);
                    $changeAmount = $amountPaid - $totalPrice;
                } else {
                    // Non tunai selalu uang pas
                    $amountPaid = $totalPrice;
                }
            }

            $at = self::clientTime($request->created_at);

            $order = new Order([
                'receipt_number'    => $this->receiptNumber($request->receipt_number, $at),
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
                'client_uuid'       => $request->client_uuid,
            ]);
            // Waktu pesanan sebenarnya (bisa lebih awal dari waktu terkirim)
            $order->created_at = $at;
            $order->save();

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // Uang masuk dicatat di shift kasir yang menerima pembayaran
            if (!$isPending) {
                $this->attachToShift($order, $request->user(), $at);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'data' => $order->load(['items.menu', 'user'])], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Nomor struk dari aplikasi dipakai bila belum terpakai, supaya nomor di
     * struk pelanggan sama dengan yang tercatat. Selain itu dibuat server.
     */
    private function receiptNumber(?string $fromClient, Carbon $at): string
    {
        if ($fromClient && preg_match('/^[A-Z0-9-]{6,40}$/', $fromClient)
            && !Order::where('receipt_number', $fromClient)->exists()) {
            return $fromClient;
        }

        $base = 'INV-' . $at->format('Ymd') . '-';
        $next = (Order::max('id') ?? 0) + 1;
        while (Order::where('receipt_number', $base . str_pad($next, 4, '0', STR_PAD_LEFT))->exists()) {
            $next++;
        }

        return $base . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Waktu kejadian dari aplikasi kasir. Jam HP yang ngawur (di masa depan
     * atau lebih dari 7 hari lalu) diganti waktu server.
     */
    private static function clientTime($value): Carbon
    {
        $now = Carbon::now('Asia/Makassar');
        if (!$value) {
            return $now;
        }

        try {
            $time = Carbon::parse($value)->setTimezone('Asia/Makassar');
        } catch (\Exception $e) {
            return $now;
        }

        if ($time->greaterThan($now->copy()->addMinutes(5)) || $time->lessThan($now->copy()->subDays(7))) {
            return $now;
        }

        return $time;
    }

    /**
     * Cari order dari id angka atau client_uuid (bill yang dibuat saat offline
     * belum punya id server ketika aplikasi menyimpan perintah bayar/tambah).
     */
    private static function findOrderForUpdate($key): Order
    {
        $query = Order::lockForUpdate();

        return ctype_digit((string) $key)
            ? $query->findOrFail($key)
            : $query->where('client_uuid', $key)->firstOrFail();
    }

    /**
     * Ambil item dari stok: cek ketersediaan, pakai harga sesuai jenis order,
     * lalu kurangi stok. Melempar RuntimeException bila stok tidak cukup.
     *
     * Pesanan offline sudah terlanjur dijual: tidak ditolak karena stok/menu
     * nonaktif, harga mengikuti yang tercetak, dan stok berhenti di 0.
     */
    private function takeItems(array $items, bool $isDelivery, bool $offline = false, ?string $batch = null): array
    {
        $subtotal = 0;
        $rows = [];

        foreach ($items as $item) {
            $menu = Menu::lockForUpdate()->findOrFail($item['menu_id']);
            $qty = (int) $item['qty'];

            if (!$offline) {
                if (!$menu->is_available) {
                    throw new \RuntimeException("{$menu->name} sedang tidak dijual.");
                }
                if ($menu->stock < $qty) {
                    throw new \RuntimeException($menu->stock > 0
                        ? "Stok {$menu->name} tidak cukup (sisa {$menu->stock})."
                        : "{$menu->name} sudah habis.");
                }
            }

            // LOGIKA HARGA BERDASARKAN TIPE ORDER
            $priceToUse = $isDelivery
                ? ($menu->price_online > 0 ? $menu->price_online : $menu->price)
                : ($menu->price_dine_in > 0 ? $menu->price_dine_in : $menu->price);
            if ($offline && isset($item['price'])) {
                $priceToUse = (int) $item['price'];
            }

            $itemSubtotal = $priceToUse * $qty;
            $subtotal += $itemSubtotal;

            $rows[] = [
                'menu_id'      => $menu->id,
                'price'        => $priceToUse, // Harga saat kejadian
                'qty'          => $qty,
                'subtotal'     => $itemSubtotal,
                'note'         => $item['note'] ?? null,
                'client_batch' => $batch,
            ];

            $menu->stock = max(0, $menu->stock - $qty);
            $menu->save();
        }

        return [$subtotal, $rows];
    }

    /**
     * 7. Tambah pesanan ke bill yang belum dibayar (mis. meja pesan lagi).
     * Response memuat order lengkap + `new_items` untuk dicetak ke dapur.
     * `batch_uuid` mencegah item yang sama tertambah dua kali.
     */
    public function addItems(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.note' => 'nullable|string',
            'items.*.price' => 'nullable|integer|min:0',
            'batch_uuid' => 'nullable|string|max:36',
            'offline' => 'nullable|boolean',
        ]);

        $offline = $request->boolean('offline');
        $batch = $request->batch_uuid;

        DB::beginTransaction();
        try {
            try {
                $order = self::findOrderForUpdate($id);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Bill tidak ditemukan.'], 404);
            }

            if ($batch && $order->items()->where('client_batch', $batch)->exists()) {
                DB::rollBack();
                return response()->json([
                    'status' => 'success',
                    'duplicate' => true,
                    'data' => $this->withNewItems($order, $order->items()->where('client_batch', $batch)->pluck('id')->all()),
                ]);
            }

            if ($order->status !== 'pending') {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Bill ini sudah dibayar atau dibatalkan.'], 400);
            }

            try {
                [, $rows] = $this->takeItems($request->items, $order->order_type === 'delivery', $offline, $batch);
            } catch (\RuntimeException $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
            }

            $newIds = [];
            foreach ($rows as $row) {
                $newIds[] = $order->items()->create($row)->id;
            }

            // Hitung ulang total. Diskon nominal yang sudah ada dipertahankan.
            $subtotal = (int) $order->items()->sum('subtotal');
            $tax = (int) round($subtotal * (self::taxRate() / 100));
            $discount = (int) min($order->discount_amount, $subtotal);

            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'discount_amount' => $discount,
                'total_price' => max(0, $subtotal + $tax - $discount),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan ditambahkan ke bill.',
                'data' => $this->withNewItems($order, $newIds),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function withNewItems(Order $order, array $newIds): array
    {
        $order->load(['items.menu', 'user']);
        $data = $order->toArray();
        $data['new_items'] = $order->items->whereIn('id', $newIds)->values()->toArray();

        return $data;
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
            return response()->json(['status' => 'error', 'message' => 'PIN admin salah.'], 401);
        }

        DB::beginTransaction();
        try {
            $order = Order::with('items')->lockForUpdate()->findOrFail($id);

            if (strtolower($order->status) === 'void') {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Transaksi ini sudah dibatalkan.'], 400);
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
            'amount_paid'    => 'nullable|numeric|min:0',
            'op_uuid'        => 'nullable|string|max:36',
            'paid_at'        => 'nullable|date',
            'offline'        => 'nullable|boolean',
        ]);

        $offline = $request->boolean('offline');

        DB::beginTransaction();
        try {
            try {
                $order = self::findOrderForUpdate($id);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Bill tidak ditemukan.'], 404);
            }

            // Pembayaran yang sama terkirim ulang: anggap berhasil
            if ($order->status === 'paid' && $request->filled('op_uuid') && $order->pay_uuid === $request->op_uuid) {
                DB::rollBack();
                return response()->json([
                    'status'    => 'success',
                    'duplicate' => true,
                    'message'   => 'Pembayaran berhasil.',
                    'data'      => $order->load(['items.menu', 'user']),
                ]);
            }

            if ($order->status !== 'pending') {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Pesanan ini sudah lunas atau dibatalkan.'], 400);
            }

            $paymentMethod = $request->payment_method;

            if ($paymentMethod === 'delivery' && $order->order_type !== 'delivery') {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Pembayaran lewat aplikasi ojol hanya untuk pesanan ojol.'], 422);
            }

            // Hitung Uang Masuk dan Kembalian
            $amountPaid = $order->total_price;
            $changeAmount = 0;

            if ($paymentMethod === 'cash') {
                $amountPaid = (int) ($request->amount_paid ?? $order->total_price);
                if ($amountPaid < $order->total_price && !$offline) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Uang diterima kurang dari total Rp ' . number_format($order->total_price, 0, ',', '.')], 422);
                }
                $amountPaid = max($amountPaid, (int) $order->total_price);
                $changeAmount = $amountPaid - $order->total_price;
            }

            $order->update([
                'status'         => 'paid',
                'payment_method' => $paymentMethod,
                'amount_paid'    => $amountPaid,
                'change_amount'  => $changeAmount,
                'pay_uuid'       => $request->op_uuid,
                // created_at sengaja tidak diubah agar waktu order awal tetap valid
            ]);

            // Uang masuk ke shift kasir yang melunasi, bukan yang membuat bill
            $this->attachToShift($order, $request->user(), self::clientTime($request->paid_at));

            DB::commit();

            // Load relasi agar struk aman
            $order->load(['items.menu', 'user']);

            return response()->json([
                'status'  => 'success',
                'message' => 'Pembayaran berhasil.',
                'data'    => $order
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
