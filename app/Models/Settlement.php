<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'starting_cash',
        'total_cash_sales',
        'total_qris_sales',
        'total_debit_sales',
        'total_credit_sales',
        'total_delivery_sales',
        'total_expenses',
        'actual_cash_on_hand',
        'status',
        'notes',
        'closed_at'
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    // Relasi ke Kasir yang bertanggung jawab
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
