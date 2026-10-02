<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    // Tambahkan baris ini agar data bisa disimpan sekaligus
    protected $fillable = ['name', 'type', 'value', 'is_active'];
}
