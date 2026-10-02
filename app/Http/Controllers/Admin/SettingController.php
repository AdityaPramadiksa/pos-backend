<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    /**
     * Menampilkan halaman pengaturan
     */
    public function index()
    {
        $settings = [
            'shop_name'           => Setting::getValue('shop_name', 'Babi Guling Dharma Praja'),
            'shop_phone'          => Setting::getValue('shop_phone', '08123456789'),
            'shop_address'        => Setting::getValue('shop_address', 'Denpasar, Bali'),
            'receipt_footer'      => Setting::getValue('receipt_footer', 'Terima Kasih, Selamat Menikmati!'),
            'starting_cash'       => Setting::getValue('starting_cash', 500000),
            'tax_rate'            => Setting::getValue('tax_rate', 10),
            'service_charge'      => Setting::getValue('service_charge', 0),
            'low_stock_threshold' => Setting::getValue('low_stock_threshold', 10),
            'printer_target'      => Setting::getValue('printer_target', ''),
            'printer_type'        => Setting::getValue('printer_type', 'network'),
        ];

        return view('admin.settings.index', $settings);
    }

    /**
     * Menyimpan pengaturan toko
     */
    /**
     * Menyimpan pengaturan toko
     */
    /**
     * Menyimpan pengaturan toko
     */
  /**
     * Menyimpan pengaturan toko
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'shop_name'           => 'required|string|max:100',
            'shop_phone'          => 'required|string|max:20',
            'shop_address'        => 'required|string',
            'receipt_footer'      => 'required|string',
            'starting_cash'       => 'required|numeric|min:0',
            'tax_rate'            => 'required|numeric|min:0|max:100',
            'service_charge'      => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'printer_target'      => 'nullable|string',
            'printer_type'        => 'required|in:network,bluetooth,usb',
        ]);

        // Simpan langsung ke Database
        foreach ($validated as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                // FIX: Gunakan null coalescing (?? '') agar null diubah jadi string kosong
                ['value' => $value ?? '']
            );
        }

        return back()->with('success', 'Semua pengaturan berhasil diperbarui!');
    }
    /**
     * Update PIN Khusus untuk Security
     */
    public function updatePin(Request $request)
    {
        $request->validate([
            'new_pin' => 'required|numeric|digits:4',
            'admin_password' => 'required'
        ]);

        $user = Auth::user();

        // Cek apakah password admin benar sebelum ganti PIN
        if (!Hash::check($request->admin_password, $user->password)) {
            return back()->with('error', 'Konfirmasi Password Salah! PIN gagal diubah.');
        }

        $user->update([
            'pin' => $request->new_pin
        ]);

        return back()->with('success', 'Security PIN Anda berhasil diperbarui!');
    }
}
