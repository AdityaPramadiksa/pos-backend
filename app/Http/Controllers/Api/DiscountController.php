<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    // Mengirim data JSON ke Flutter
    public function index()
    {
        $discounts = Discount::where('is_active', true)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar diskon berhasil diambil',
            'data' => $discounts
        ], 200);
    }
}
