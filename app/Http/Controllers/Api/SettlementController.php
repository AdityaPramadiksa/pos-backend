<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settlement;
use App\Models\Setting;
use App\Services\ShiftReportService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettlementController extends Controller
{
    /**
     * 1. Cek Status Shift Aktif & Otomatis Buka Shift
     */
    public function currentStatus(Request $request)
    {
        $activeShift = ShiftReportService::activeShift($request->user());

        return response()->json([
            'status' => 'success',
            'is_open' => true,
            'data' => $activeShift
        ]);
    }

    /**
     * 2. Tutup Shift (Close Settlement). Response berisi laporan lengkap untuk 2 struk:
     *    rekap uang per cara bayar + penjualan per menu.
     */
    public function closeSettlement(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'notes' => 'nullable|string|max:255',
            'actual_cash' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $settlement = Settlement::where('user_id', $user->id)
                ->where('status', 'open')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$settlement) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Tidak ada shift aktif.'], 404);
            }

            ShiftReportService::syncTotals($settlement);

            $expectedCash = ($settlement->starting_cash + $settlement->total_cash_sales) - $settlement->total_expenses;

            $settlement->update([
                // Jika kasir tidak menghitung fisik, uang di laci dianggap sesuai sistem
                'actual_cash_on_hand' => $request->filled('actual_cash') ? (int) $request->actual_cash : $expectedCash,
                'status'              => 'closed',
                'notes'               => $request->notes,
                'closed_at'           => Carbon::now('Asia/Makassar'),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Shift berhasil ditutup.',
                'data' => ShiftReportService::build($settlement),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menutup shift: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 3. Laporan shift terakhir yang sudah ditutup (untuk cetak ulang struk settlement)
     */
    public function lastClosed(Request $request)
    {
        $settlement = Settlement::where('user_id', $request->user()->id)
            ->where('status', 'closed')
            ->latest('closed_at')
            ->first();

        if (!$settlement) {
            return response()->json(['status' => 'error', 'message' => 'Belum ada shift yang ditutup.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => ShiftReportService::build($settlement),
        ]);
    }

    /**
     * 4. Override Manual (Optional)
     */
    public function storeStartingCash(Request $request)
    {
        $request->validate(['starting_cash' => 'required|numeric|min:0']);
        $activeShift = Settlement::where('user_id', $request->user()->id)->where('status', 'open')->first();

        if ($activeShift) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah memiliki shift berjalan.'], 400);
        }

        $settlement = Settlement::create(['user_id' => $request->user()->id, 'starting_cash' => $request->starting_cash, 'status' => 'open']);
        return response()->json(['status' => 'success', 'data' => $settlement], 201);
    }

    /**
     * 5. Pengaturan toko yang dibutuhkan aplikasi kasir
     */
    public function appSettings()
    {
        $taxRate = Setting::getValue('tax_rate');

        return response()->json([
            'status' => 'success',
            'data' => [
                'tax_rate' => $taxRate !== null ? (float) $taxRate : 10,
            ],
        ]);
    }
}
