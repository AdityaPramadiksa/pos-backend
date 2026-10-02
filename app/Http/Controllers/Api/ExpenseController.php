<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Services\ShiftReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    // 1. Ambil daftar pengeluaran hari ini (untuk history di Kasir/Admin)
    public function index(Request $request)
    {
        // Pengeluaran shift yang sedang berjalan, sama dengan yang dihitung di settlement
        $shift = ShiftReportService::activeShift($request->user());

        $expenses = Expense::with('user')
            ->where('settlement_id', $shift->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $expenses
        ]);
    }

    // 2. Simpan pengeluaran baru beserta foto nota
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'description' => 'required|string|max:255',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' // Maksimal 2MB
        ]);

        try {
            $imagePath = null;

            // Logika upload foto nota
            if ($request->hasFile('receipt_image')) {
                // Gambar akan disimpan di folder storage/app/public/expenses
                $imagePath = $request->file('receipt_image')->store('expenses', 'public');
            }

            $shift = ShiftReportService::activeShift($request->user());

            $expense = Expense::create([
                'user_id' => $request->user()->id,
                'settlement_id' => $shift->id,
                'amount' => $request->amount,
                'description' => $request->description,
                'receipt_image' => $imagePath,
            ]);

            ShiftReportService::syncTotals($shift);

            return response()->json([
                'status' => 'success',
                'message' => 'Pengeluaran berhasil dicatat!',
                'data' => $expense
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mencatat pengeluaran: ' . $e->getMessage()
            ], 500);
        }
    }
}
