<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    /** Nilai bawaan bila belum pernah disimpan */
    public const DEFAULTS = [
        'shop_name'            => "WARUNG BABI GULING\nMEN GEDE",
        'shop_address'         => "Jl. Poppies I, Kuta, Kec. Kuta\nKab. Badung, Bali 80361",
        'shop_phone'           => '0822-3660-6374',
        'receipt_footer'       => "Matur Suksma!\nTerima kasih atas kunjungan Anda",
        'receipt_show_cashier' => '1',
        'starting_cash'        => '500000',
        'tax_rate'             => '10',
        'low_stock_threshold'  => '10',
    ];

    public static function values(): array
    {
        $stored = Setting::whereIn('key', array_keys(self::DEFAULTS))->pluck('value', 'key')->all();
        return array_merge(self::DEFAULTS, $stored);
    }

    public function index()
    {
        return view('admin.settings.index', ['settings' => self::values()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'shop_name'           => 'required|string|max:100',
            'shop_phone'          => 'required|string|max:20',
            'shop_address'        => 'required|string|max:255',
            'receipt_footer'      => 'required|string|max:255',
            'starting_cash'       => 'required|integer|min:0',
            'tax_rate'            => 'required|numeric|min:0|max:100',
            'low_stock_threshold' => 'required|integer|min:0',
        ], [], [
            'shop_name' => 'nama warung',
            'shop_phone' => 'nomor telepon',
            'shop_address' => 'alamat',
            'receipt_footer' => 'catatan kaki struk',
            'starting_cash' => 'modal awal',
            'tax_rate' => 'pajak',
            'low_stock_threshold' => 'batas stok menipis',
        ]);

        // Rapikan baris kosong & spasi di ujung supaya struk tidak berantakan
        foreach (['shop_name', 'shop_address', 'receipt_footer'] as $key) {
            $lines = array_filter(array_map('trim', preg_split('/\R/', $validated[$key])), 'strlen');
            $validated[$key] = implode("\n", $lines);
        }
        $validated['receipt_show_cashier'] = $request->boolean('receipt_show_cashier') ? '1' : '0';

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        return back()->with('success', 'Pengaturan tersimpan. Aplikasi kasir memakai data baru setelah halaman Kasir dibuka ulang.');
    }

    /** PIN admin yang sedang login (dipakai untuk menyetujui pembatalan transaksi) */
    public function updatePin(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'new_pin' => ['required', 'digits:4', Rule::unique('users', 'pin')->ignore($user->id)],
            'admin_password' => 'required'
        ], [
            'new_pin.unique' => 'PIN ini sudah dipakai staff lain. Pilih 4 angka lain.',
            'new_pin.digits' => 'PIN harus 4 angka.',
        ]);

        if (!Hash::check($request->admin_password, $user->password)) {
            return back()->with('error', 'Password salah. PIN tidak diganti.');
        }

        $user->update(['pin' => $request->new_pin]);

        return back()->with('success', 'PIN admin berhasil diganti.');
    }
}
