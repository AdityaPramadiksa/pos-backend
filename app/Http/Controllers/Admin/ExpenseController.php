<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('user')->orderBy('created_at', 'desc');

        // 🔥 Logika Filter Tanggal Mulai
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        // 🔥 Logika Filter Tanggal Akhir
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Pagination dengan membawa parameter URL (agar filter tidak hilang saat next page)
        $expenses = $query->paginate(15)->appends($request->query());

        return view('admin.expenses.index', compact('expenses'));
    }
}
