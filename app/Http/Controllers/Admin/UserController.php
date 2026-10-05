<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderByRaw("role = 'admin' desc")->orderBy('name')->get();
        return view('admin.user.index', compact('users'));
    }

    /**
     * Staff baru. Kasir cukup nama + PIN (login aplikasi kasir pakai PIN);
     * email & password hanya perlu untuk admin yang membuka panel ini.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:cashier,admin',
            'pin' => ['required', 'digits:4', Rule::unique('users', 'pin')],
            'email' => ['nullable', 'required_if:role,admin', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => 'nullable|required_if:role,admin|string|min:8',
        ], [
            'pin.unique' => 'PIN ini sudah dipakai staff lain. Pilih 4 angka lain.',
            'pin.digits' => 'PIN harus 4 angka.',
            'email.required_if' => 'Email wajib diisi untuk admin.',
            'password.required_if' => 'Password wajib diisi untuk admin.',
        ]);

        User::create([
            'name' => $request->name,
            'role' => $request->role,
            'pin' => $request->pin,
            // Kasir tidak login ke panel admin: email & password dibuat otomatis
            'email' => $request->email ?: Str::slug($request->name) . '-' . Str::lower(Str::random(5)) . '@kasir.local',
            'password' => Hash::make($request->password ?: Str::random(32)),
        ]);

        return back()->with('success', "Staff {$request->name} berhasil ditambahkan.");
    }

    /** Ganti PIN login kasir */
    public function updatePin(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'pin' => ['required', 'digits:4', Rule::unique('users', 'pin')->ignore($user->id)],
        ], [
            'pin.unique' => 'PIN ini sudah dipakai staff lain. Pilih 4 angka lain.',
            'pin.digits' => 'PIN harus 4 angka.',
        ]);

        $user->update(['pin' => $request->pin]);

        // Paksa login ulang di aplikasi kasir dengan PIN baru
        $user->tokens()->delete();

        return back()->with('success', "PIN {$user->name} berhasil diganti.");
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'password' => Hash::make('password123')
        ]);

        return back()->with('success', "Password {$user->name} direset menjadi: password123");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Proteksi agar admin tidak menghapus dirinya sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $user->tokens()->delete();
        $user->delete();
        return back()->with('success', "Akun {$user->name} berhasil dihapus.");
    }
}
