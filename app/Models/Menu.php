<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    /**
     * Update fillable agar mendukung harga bertingkat
     */
    protected $fillable = [
        'category_id',
        'name',
        'price_dine_in', // Harga Resto
        'price_online',  // Harga Ojol
        'price',         // Harga dasar/fallback
        'image',
        'stock',
        'is_available'
    ];

    /**
     * Menambahkan attribute kustom agar Flutter bisa langsung pakai 'image_url'
     */
    protected $appends = ['image_url'];

    /**
     * Relasi: Satu Menu dimiliki oleh satu Kategori
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi ke OrderItems
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'menu_id');
    }

    /**
     * Accessor: Membuat URL lengkap untuk gambar menu
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return "https://via.placeholder.com/150";
        }

        // Cek path agar tidak double 'menus/'
        $path = str_contains($this->image, 'menus/') ? $this->image : 'menus/' . $this->image;

        // Gunakan asset() agar otomatis mengambil APP_URL dari .env (Pastikan .env berisi IP Laptop kamu)
        return asset('storage/' . $path);
    }

    /**
     * Casts: Penting agar Flutter tidak error saat parsing JSON
     */
    protected $casts = [
        'price_dine_in' => 'integer',
        'price_online'  => 'integer',
        'price'         => 'integer',
        'stock'         => 'integer',
        'is_available'  => 'boolean',
        'category_id'   => 'integer',
    ];
}
