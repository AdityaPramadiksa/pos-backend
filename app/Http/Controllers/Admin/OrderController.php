<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Menu;
use App\Models\Settlement;
use App\Services\ShiftReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Menampilkan daftar semua transaksi untuk Admin
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.menu']);

        // 1. Filter Range Tanggal (Sama dengan Petty Cash & Reports)
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Default: Jika tidak ada filter tanggal sama sekali, bisa opsional nampilin hari ini aja
        // (Tapi karena kita ingin lihat semua history jika filter kosong, kita matikan filter default 'today' ini)
        if (!$request->filled('start_date') && !$request->filled('end_date') && $request->has('today')) {
            $query->whereDate('created_at', Carbon::now('Asia/Makassar')->toDateString());
        }

        // 2. Filter Status & Type
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        // 3. Filter Platform Ojol
        if ($request->filled('platform')) {
            $query->where('delivery_platform', $request->platform);
        }

        // Pakai paginate agar load page tidak berat kalau transaksi sudah ribuan
        $orders = $query->latest()->paginate(15);

        // Membawa parameter filter (tanggal, status, dll) ke halaman pagination selanjutnya
        $orders->appends($request->all());

        return view('admin.order.index', compact('orders'));
    }

    /**
     * Menampilkan detail lengkap satu pesanan
     */
    public function show($id)
    {
        // Menambahkan loading delivery_platform agar admin tahu ojol mana yang pesan
        $order = Order::with(['user', 'items.menu', 'voidBy'])->findOrFail($id);

        return view('admin.order.show', compact('order'));
    }

    /**
     * Fitur VOID dari Dashboard Admin
     */
    public function void(Request $request, $id)
    {
        $request->validate([
            'void_reason' => 'required|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $order = Order::with('items')->findOrFail($id);

            if (strtolower($order->status) === 'void') {
                return back()->with('error', 'Transaksi ini sudah dibatalkan sebelumnya.');
            }

            $oldStatus = strtolower($order->status);

            // 1. Kembalikan stok menu
            foreach ($order->items as $item) {
                // Pastikan hanya menambah stok jika menu tersebut mengelola stok
                Menu::where('id', $item->menu_id)->increment('stock', $item->qty);
            }

            // 2. Update status transaksi menjadi void
            $order->update([
                'status' => 'void',
                'void_by' => Auth::id(),
                'void_reason' => $request->void_reason
            ]);

            // 3. Hitung ulang total settlement (Hanya jika status asalnya 'paid')
            if ($oldStatus === 'paid' && $order->settlement_id && ($settlement = Settlement::find($order->settlement_id))) {
                ShiftReportService::syncTotals($settlement);
            }

            DB::commit();
            return back()->with('success', 'Transaksi VOID berhasil. Stok kembali dan saldo settlement telah dikurangi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses VOID: ' . $e->getMessage());
        }
    }
}
