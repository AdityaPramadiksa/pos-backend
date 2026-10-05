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
                'message' => 'Email atau password salah.'
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
            'device_id' => 'nullable|string|max:40',
        ]);

        // Cari user berdasarkan PIN
        $user = User::where('pin', $request->pin)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'PIN salah. Coba lagi atau tanyakan PIN ke admin.'
            ], 401);
        }

        // Satu token per perangkat. Token di HP lain tetap berlaku, supaya
        // transaksi offline di HP itu masih bisa terkirim nanti.
        $tokenName = $request->filled('device_id')
            ? 'pos-device-' . $request->device_id
            : 'pos-resto-token';
        $user->tokens()->where('name', $tokenName)->delete();

        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil masuk.',
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
