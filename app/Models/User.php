<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Ini kunci utama agar bisa login dari aplikasi Flutter!

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Kolom-kolom yang boleh diisi (Mass Assignable)
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Tambahan kita untuk Admin/Cashier
        'pin',  // Tambahan kita untuk fitur VOID
    ];

    /**
     * Kolom-kolom yang disembunyikan (tidak akan muncul saat data dipanggil lewat API)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Format tipe data
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
