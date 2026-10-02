<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'user_id',
        'customer_name',
        'table_number',
        'order_type',
        'delivery_platform', // Tambahan Baru
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_price',
        'payment_method',
        'amount_paid',
        'change_amount',     // Tambahan Baru
        'status',
        'void_by',
        'void_reason',
        'notes',             // Tambahan Baru
        'settlement_id',     // Shift tempat pembayaran diterima
        'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    // Relasi ke Kasir yang memproses pesanan
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke detail item yang dipesan (Eager Loading otomatis untuk API)
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relasi ke Admin yang melakukan VOID (Pembatalan)
    public function voidBy()
    {
        return $this->belongsTo(User::class, 'void_by');
    }

    /**
     * Scope untuk memfilter pesanan berdasarkan tipe.
     * Berguna untuk laporan nanti.
     */
    public function scopeDelivery($query)
    {
        return $query->where('order_type', 'delivery');
    }
}
