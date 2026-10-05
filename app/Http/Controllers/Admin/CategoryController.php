<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    // Menampilkan daftar kategori
    public function index()
    {
        // withCount membantu kita tahu ada berapa menu di tiap kategori
        $categories = Category::withCount('menus')->latest()->get();
        return view('admin.category.index', compact('categories'));
    }

    // Simpan kategori baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name'
        ]);

        Category::create($request->all());

        return back()->with('success', 'Kategori ditambahkan.');
    }

    // Hapus kategori
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Cek dulu, jangan sampai hapus kategori yang masih ada isi menunya
        if ($category->menus()->count() > 0) {
            return back()->with('error', 'Kategori ini masih berisi menu. Pindahkan atau hapus menunya dulu.');
        }

        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }
}
