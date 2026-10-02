<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'description',
        'receipt_image',
        'settlement_id',
    ];

    // Relasi untuk mengetahui siapa kasir yang menginput
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
