<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Import Semua Controller Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SettlementController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\AppReleaseController;
use App\Services\KasirApk;

/*
|--------------------------------------------------------------------------
| Web Routes - Babi Guling POS Admin
|--------------------------------------------------------------------------
*/

// 1. ROOT ROUTE
Route::get('/', function () {
    return redirect()->route('login');
});

// Cadangan foto (menu, nota) untuk hosting yang tidak mengizinkan
// `php artisan storage:link`. Bila symlink ada, file dilayani langsung
// oleh web server dan rute ini tidak terpanggil.
Route::get('/storage/{path}', function (string $path) {
    $root = realpath(storage_path('app/public'));
    $file = realpath($root . DIRECTORY_SEPARATOR . $path);

    if (!$root || !$file || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file)) {
        abort(404);
    }

    return response()->file($file, ['Cache-Control' => 'public, max-age=604800']);
})->where('path', '.*');

// Halaman unduh aplikasi kasir untuk HP outlet (tanpa login)
Route::get('/unduh', function () {
    return view('download', ['info' => KasirApk::info()]);
})->name('download.page');

Route::get('/unduh/kasir-men-gede.apk', function () {
    $info = KasirApk::info();
    abort_unless($info, 404);

    return response()->download(KasirApk::path(), KasirApk::downloadName($info), [
        'Content-Type' => 'application/vnd.android.package-archive',
        'Cache-Control' => 'no-cache',
    ]);
})->name('download.apk');

// 2. AUTH ROUTES
Route::get('login', function () {
    return view('admin.login');
})->name('login');

Route::post('login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('admin/dashboard');
    }

    return back()->withErrors([
        'email' => 'Email atau password salah',
    ])->onlyInput('email');
});

Route::post('logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');


// 3. GROUP ADMIN PROTECTED (Wajib Login & Role Admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    // --- DASHBOARD ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // --- MENU & CATEGORY ---
    Route::patch('menus/{id}/toggle', [MenuController::class, 'toggleStatus'])->name('admin.menu.toggle');
    Route::resource('menus', MenuController::class)->names('admin.menu');
    Route::resource('categories', CategoryController::class)->names('admin.category');

    // --- ORDERS (RIWAYAT TRANSAKSI) ---
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('admin.orders.index');
        Route::get('/{id}', [OrderController::class, 'show'])->name('admin.orders.show');
        Route::patch('/{id}/void', [OrderController::class, 'void'])->name('admin.orders.void');
    });

    // --- SETTLEMENT (LAPORAN SHIFT) ---
    Route::prefix('settlements')->group(function () {
        Route::get('/', [SettlementController::class, 'index'])->name('admin.settlements.index');
        Route::get('/{id}', [SettlementController::class, 'show'])->name('admin.settlements.show');
    });

    // --- MARKETING (DISKON & LAPORAN) ---
    Route::resource('discounts', DiscountController::class)->names('admin.discounts');

    // --- LAPORAN PENJUALAN ---
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('admin.reports.export');

    // --- USER MANAGEMENT ---
    Route::patch('users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset_password');
    Route::patch('users/{id}/pin', [UserController::class, 'updatePin'])->name('admin.users.update_pin');
    Route::resource('users', UserController::class)->names('admin.users');

    // --- EXPENSES (KAS KELUAR) ---
    Route::get('expenses', [ExpenseController::class, 'index'])->name('admin.expenses.index');

    // --- SETTINGS ---
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('admin.settings.index');

        // Update Informasi Toko (General)
        Route::post('/update', [SettingController::class, 'update'])->name('admin.settings.update');

        // Update PIN Keamanan (Security)
        Route::post('/update-pin', [SettingController::class, 'updatePin'])->name('admin.settings.update_pin');
    });

    // Aplikasi kasir (APK untuk halaman /unduh)
    Route::prefix('app-release')->group(function () {
        Route::get('/', [AppReleaseController::class, 'index'])->name('admin.app_release.index');
        Route::post('/', [AppReleaseController::class, 'store'])->name('admin.app_release.store');
    });
});
