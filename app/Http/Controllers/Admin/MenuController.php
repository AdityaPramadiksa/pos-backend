<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    /**
     * Menampilkan daftar semua menu
     */
    public function index()
    {
        $menus = Menu::with('category')->latest()->get();
        // Pastikan nama view sesuai: admin.menu.index
        return view('admin.menu.index', compact('menus'));
    }

    /**
     * Membuka form tambah menu baru
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.menu.create', compact('categories'));
    }

    /**
     * Menyimpan menu baru ke database (Final Fix: Dual Price)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'price_dine_in' => 'required|numeric|min:0', // Harga Resto
            'price_online'  => 'required|numeric|min:0',  // Harga Ojol
            'category_id'   => 'required|exists:categories,id',
            'stock'         => 'required|integer|min:0',
            'image'         => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('menus', 'public');
        }

        Menu::create([
            'name'          => $request->name,
            'price_dine_in' => $request->price_dine_in,
            'price_online'  => $request->price_online,
            'price'         => $request->price_dine_in, // Fallback tetap diisi
            'category_id'   => $request->category_id,
            'stock'         => $request->stock,
            'image'         => $imagePath,
            'is_available'  => true,
        ]);

        return redirect()->route('admin.menu.index')->with('success', 'Menu ' . $request->name . ' ditambahkan.');
    }

    /**
     * Membuka form edit menu
     */
    public function edit($id)
    {
        $menu = Menu::findOrFail($id);
        $categories = Category::all();
        return view('admin.menu.edit', compact('menu', 'categories'));
    }

    /**
     * Memproses update data menu (Final Fix: Dual Price)
     */
    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $request->validate([
            'name'          => 'required|string|max:255',
            'price_dine_in' => 'required|numeric|min:0',
            'price_online'  => 'required|numeric|min:0',
            'category_id'   => 'required|exists:categories,id',
            'stock'         => 'required|integer|min:0',
            'image'         => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        // Ambil data selain image
        $data = $request->only(['name', 'price_dine_in', 'price_online', 'category_id', 'stock']);

        // Pastikan fallback price juga terupdate
        $data['price'] = $request->price_dine_in;

        if ($request->hasFile('image')) {
            // Hapus foto lama dari storage jika ada
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }
            // Simpan foto baru
            $data['image'] = $request->file('image')->store('menus', 'public');
        }

        $menu->update($data);

        return redirect()->route('admin.menu.index')->with('success', 'Perubahan menu ' . $menu->name . ' disimpan.');
    }

    /**
     * Menghapus menu secara permanen
     */
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);

        // Hapus item pesanan terkait agar tidak error Foreign Key
        $menu->items()->delete();

        // Hapus file gambar dari storage
        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }

        $menu->delete();

        return back()->with('success', 'Menu ' . $menu->name . ' dihapus.');
    }

    /**
     * Aktifkan/Nonaktifkan Menu (Available Toggle)
     */
    public function toggleStatus($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->update([
            'is_available' => !$menu->is_available
        ]);

        $status = $menu->is_available ? 'tampil lagi di aplikasi kasir' : 'disembunyikan dari aplikasi kasir';
        return back()->with('success', "{$menu->name} $status.");
    }
}
