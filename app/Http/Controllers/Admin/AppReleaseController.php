<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\KasirApk;
use Illuminate\Http\Request;

/**
 * Admin > Aplikasi kasir: unggah APK terbaru yang dibagikan di /unduh.
 */
class AppReleaseController extends Controller
{
    public function index()
    {
        return view('admin.app-release.index', [
            'info' => KasirApk::info(),
            'downloadUrl' => route('download.page'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'apk' => 'required|file|max:153600', // 150 MB
            'version' => ['required', 'string', 'max:20', 'regex:/^[0-9][0-9A-Za-z.\-+]*$/'],
            'notes' => 'nullable|string|max:1000',
        ], [
            'apk.required' => 'Pilih file APK dulu.',
            'apk.max' => 'File APK maksimal 150 MB.',
            'apk.uploaded' => 'File gagal terunggah. Biasanya karena ukurannya melebihi batas server.',
            'version.required' => 'Isi nomor versi, misalnya 1.0.1.',
            'version.regex' => 'Nomor versi diawali angka, misalnya 1.0.1.',
        ]);

        $file = $request->file('apk');
        if (strtolower($file->getClientOriginalExtension()) !== 'apk' || !KasirApk::looksLikeApk($file)) {
            return back()->withErrors(['apk' => 'File ini bukan APK. Pilih file berakhiran .apk dari folder build Flutter.'])->withInput();
        }

        $info = KasirApk::store($file, $request->version, $request->notes);

        return redirect()->route('admin.app_release.index')
            ->with('success', "Kasir Men Gede versi {$info['version']} sekarang tersedia di halaman unduhan.");
    }
}
