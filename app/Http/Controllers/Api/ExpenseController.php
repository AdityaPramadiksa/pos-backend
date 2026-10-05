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
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
            'client_uuid' => 'nullable|string|max:36',
            'created_at' => 'nullable|date',
        ]);

        // Pengeluaran yang sama terkirim ulang dari aplikasi (sinyal putus)
        if ($request->filled('client_uuid')
            && ($existing = Expense::where('client_uuid', $request->client_uuid)->first())) {
            return response()->json(['status' => 'success', 'duplicate' => true, 'data' => $existing], 200);
        }

        try {
            $imagePath = null;

            // Logika upload foto nota
            if ($request->hasFile('receipt_image')) {
                // Gambar akan disimpan di folder storage/app/public/expenses
                $imagePath = $request->file('receipt_image')->store('expenses', 'public');
            }

            $shift = ShiftReportService::activeShift($request->user());

            $expense = new Expense([
                'user_id' => $request->user()->id,
                'settlement_id' => $shift->id,
                'amount' => $request->amount,
                'description' => $request->description,
                'receipt_image' => $imagePath,
                'client_uuid' => $request->client_uuid,
            ]);
            $expense->created_at = self::clientTime($request->created_at);
            $expense->save();

            ShiftReportService::syncTotals($shift);

            return response()->json([
                'status' => 'success',
                'message' => 'Pengeluaran dicatat.',
                'data' => $expense
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mencatat pengeluaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Waktu pencatatan dari aplikasi (bisa lebih awal bila tercatat saat offline).
     */
    private static function clientTime($value): Carbon
    {
        $now = Carbon::now();
        if (!$value) {
            return $now;
        }

        try {
            $time = Carbon::parse($value)->setTimezone(config('app.timezone'));
        } catch (\Exception $e) {
            return $now;
        }

        return $time->between($now->copy()->subDays(7), $now->copy()->addMinutes(5)) ? $time : $now;
    }
}
