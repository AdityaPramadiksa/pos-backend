<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Buat atau perbarui akun admin dari terminal hosting, tanpa seeder
 * (seeder memakai password bawaan yang tidak aman untuk server sungguhan).
 */
class CreateAdmin extends Command
{
    protected $signature = 'pos:admin';

    protected $description = 'Buat akun admin baru atau ganti password admin yang sudah ada';

    public function handle(): int
    {
        $email = trim((string) $this->ask('Email admin'));
        $existing = User::where('email', $email)->first();

        if ($existing) {
            $this->info("Akun {$existing->name} sudah ada. Password-nya akan diganti.");
        }

        $name = $existing->name ?? trim((string) $this->ask('Nama', 'Admin'));
        $password = (string) $this->secret('Password (minimal 8 karakter)');
        $confirm = (string) $this->secret('Ulangi password');
        $pin = $existing->pin ?? trim((string) $this->ask('PIN 4 angka (untuk izin void di kasir)'));

        $validator = Validator::make(
            compact('email', 'name', 'password', 'pin'),
            [
                'email' => 'required|email',
                'name' => 'required|string|max:100',
                'password' => 'required|string|min:8',
                'pin' => 'required|digits:4',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }
            return self::FAILURE;
        }

        if ($password !== $confirm) {
            $this->error('Kedua password tidak sama.');
            return self::FAILURE;
        }

        if (!$existing && User::where('pin', $pin)->exists()) {
            $this->error('PIN itu sudah dipakai staf lain.');
            return self::FAILURE;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'pin' => $pin,
            ]
        );

        $this->info($existing ? 'Password admin diganti.' : "Admin {$name} dibuat. Silakan masuk ke /login.");

        return self::SUCCESS;
    }
}
