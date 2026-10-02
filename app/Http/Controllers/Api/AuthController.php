<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Login menggunakan Email & Password (Biasanya untuk Admin di awal)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau Password salah!'
            ], 401);
        }

        $token = $user->createToken('pos-resto-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 200);
    }

    // LOGIN KHUSUS PIN (Untuk Kasir/Staff di Flutter)
    public function loginPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|min:4',
        ]);

        // Cari user berdasarkan PIN
        $user = User::where('pin', $request->pin)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'PIN salah atau petugas tidak ditemukan.'
            ], 401);
        }

        // Hapus token lama jika ada (biar nggak numpuk)
        $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken('pos-resto-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login Berhasil (Via PIN)',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ], 200);
    }
}
