<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller {

    /**
     * 1. Ambil Semua Menu (Sinkron dengan Flutter POS)
     */
    public function getMenus() {
        // Kita ambil menu yang tersedia saja
        // Eager load category biar datanya lengkap
        $menus = Menu::with('category')
            ->where('is_available', true)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $menus
        ]);
    }

    /**
     * 1b. Ubah sisa stok dari aplikasi kasir (0 = tandai habis)
     */
    public function updateStock(Request $request, $id) {
        $request->validate([
            'stock' => 'required|integer|min:0|max:99999',
        ]);

        $menu = Menu::findOrFail($id);
        $menu->update(['stock' => (int) $request->stock]);

        return response()->json([
            'status' => 'success',
            'message' => $menu->stock > 0
                ? "Stok {$menu->name} sekarang {$menu->stock} porsi"
                : "{$menu->name} ditandai habis",
            'data' => $menu->load('category'),
        ]);
    }

    /**
     * 2. Ambil semua kategori untuk Tab Filter di Flutter
     */
    public function getCategories() {
        return response()->json([
            'status' => 'success',
            'data' => Category::all()
        ]);
    }

    /**
     * 3. Simpan Menu Baru via API (Final Fix: Dual Price)
     */
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'price_dine_in' => 'required|numeric|min:0',
            'price_online' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'stock' => 'required|integer|min:0',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            // Kita simpan path lengkap agar accessor di Model Menu bekerja maksimal
            $path = $request->file('image')->store('menus', 'public');
            $imageName = $path;
        }

        $menu = Menu::create([
            'name' => $request->name,
            'price_dine_in' => $request->price_dine_in,
            'price_online' => $request->price_online,
            'price' => $request->price_dine_in, // Fallback price
            'category_id' => $request->category_id,
            'image' => $imageName,
            'stock' => $request->stock,
            'is_available' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Menu ditambahkan.',
            'data' => $menu->load('category')
        ], 201);
    }
}
