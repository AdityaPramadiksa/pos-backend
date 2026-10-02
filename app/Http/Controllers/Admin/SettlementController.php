<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settlement;
use App\Models\Expense; // 🔥 Tambahkan ini
use Illuminate\Http\Request;
use Carbon\Carbon;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $query = Settlement::with('user');

        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('created_at', $request->date);
        } else {
            $query->whereDate('created_at', Carbon::now('Asia/Makassar')->toDateString());
        }

        $settlements = $query->orderBy('created_at', 'desc')->paginate(10);
        $settlements->appends($request->all());

        return view('admin.settlement.index', compact('settlements'));
    }

    public function show($id)
    {
        $settlement = Settlement::with('user')->findOrFail($id);

        /**
         * 🔥 LOGIKA PENTING:
         * Jika status masih 'open', kita hitung pengeluaran secara real-time dari tabel Expense.
         * Jika sudah 'closed', kita pakai nilai total_expenses yang sudah terkunci di kolom settlement.
         */
        if ($settlement->status == 'open') {
            $totalExpenses = Expense::where('settlement_id', $settlement->id)->sum('amount');
        } else {
            $totalExpenses = $settlement->total_expenses ?? 0;
        }

        // Kalkulasi Expected Cash (Uang Fisik di Laci)
        $expectedCash = ($settlement->starting_cash + $settlement->total_cash_sales) - $totalExpenses;

        // Total Penjualan Digital
        $totalDigitalSales = $settlement->total_qris_sales +
                             $settlement->total_debit_sales +
                             $settlement->total_credit_sales +
                             $settlement->total_delivery_sales;

        // Total Omzet Keseluruhan
        $grandTotalSales = $settlement->total_cash_sales + $totalDigitalSales;

        return view('admin.settlement.show', compact(
            'settlement',
            'expectedCash',
            'totalExpenses', // 🔥 Kirim variabel ini ke Blade agar bisa ditampilkan
            'totalDigitalSales',
            'grandTotalSales'
        ));
    }
}
