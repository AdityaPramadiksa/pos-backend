<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settlement;
use App\Services\ShiftReportService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $query = Settlement::with('user');

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->get('filter') === 'selisih') {
            $query->where('status', 'closed')
                ->whereRaw('actual_cash_on_hand <> (starting_cash + total_cash_sales - total_expenses)');
        }

        $settlements = $query->latest('id')->paginate(15)->appends($request->query());

        // Penjualan, uang tunai seharusnya, dan selisih tiap shift
        $rows = $settlements->getCollection()
            ->map(fn ($s) => ['model' => $s] + ShiftReportService::cashSummary($s));

        $openCount = Settlement::where('status', 'open')->count();

        return view('admin.settlement.index', compact('settlements', 'rows', 'openCount'));
    }

    public function show($id)
    {
        $settlement = Settlement::with('user')->findOrFail($id);

        if ($settlement->status === 'open') {
            ShiftReportService::syncTotals($settlement);
        }

        $report = ShiftReportService::build($settlement);

        return view('admin.settlement.show', compact('settlement', 'report'));
    }
}
