<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        return view('admin.user.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Staff baru berhasil didaftarkan!');
    }

    public function resetPassword($id)
{
    $user = User::findOrFail($id);

    // Reset password ke default: password123
    $user->update([
        'password' => Hash::make('password123')
    ]);

    return back()->with('success', 'Password staff ' . $user->name . ' telah direset menjadi: password123');
}

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Proteksi agar admin tidak menghapus dirinya sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kamu tidak bisa menghapus akun kamu sendiri!');
        }

        $user->delete();
        return back()->with('success', 'Akun staff berhasil dihapus!');
    }
}
