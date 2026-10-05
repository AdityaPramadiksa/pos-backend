<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    // Menampilkan halaman daftar diskon di browser
    public function index()
    {
        $discounts = Discount::latest()->get();
        return view('admin.discounts.index', compact('discounts'));
    }

    // Menghandle input dari form Blade
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
        ]);

        Discount::create($request->all());

        return redirect()->back()->with('success', 'Diskon ditambahkan.');
    }

    public function destroy($id)
    {
        Discount::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Diskon dihapus.');
    }
}
