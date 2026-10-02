<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Relasi ke Menu (1 Kategori punya banyak Menu)
    public function menus()
    {
        return $this->hasMany(Menu::class);
    }
}
