<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     */
    protected $table = 'settings';

    /**
     * Kolom yang boleh diisi secara massal
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Helper Static untuk mengambil nilai berdasarkan key dengan cepat
     * Contoh penggunaan: Setting::getValue('starting_cash', 500000);
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Helper Static untuk update atau create setting
     * Contoh penggunaan: Setting::setValue('starting_cash', 1000000);
     */
    public static function setValue($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
