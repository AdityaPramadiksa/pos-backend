<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // HANYA AKUN ADMIN UTAMA
        // Biar kamu bisa login dan mulai input data Master dari nol
        User::create([
            'name' => 'Admin Babi Guling',
            'email' => 'admin@resto.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'pin' => '1234' // PIN buat approve VOID nanti
        ]);

        // Opsional: Kalau kamu mau ngetes login sebagai kasir juga, buka comment di bawah
        /*
        User::create([
            'name' => 'Kasir Utama',
            'email' => 'kasir@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'cashier'
        ]);
        */
    }
}
