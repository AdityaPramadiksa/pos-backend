<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SettlementController;
use App\Http\Controllers\Api\DiscountController as ApiDiscount;
use App\Http\Controllers\Api\ExpenseController;

/*
|--------------------------------------------------------------------------
| Public Routes (Tanpa Token)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login-pin', [AuthController::class, 'loginPin']);

// 🔥 JALUR VIP GAMBAR MENU: Bypass CORS untuk Flutter Web
Route::get('/menu-image/{filename}', function ($filename) {
    // Cari gambar langsung di folder storage/app/public/menus
    $path = storage_path('app/public/menus/' . $filename);

    // Jika file tidak ada, kembalikan error 404
    if (!file_exists($path)) {
        abort(404);
    }

    // Kembalikan gambar melalui sistem Laravel agar lolos CORS
    return response()->file($path);
});

// 🔥 JALUR VIP GAMBAR NOTA EXPENSE: Bypass CORS untuk Flutter Web
// PENTING: Ditaruh di luar middleware agar Image.network Flutter bisa akses langsung
Route::get('/expense-image/{filename}', function ($filename) {
    $path = storage_path('app/public/expenses/' . $filename);
    if (!file_exists($path)) abort(404);
    return response()->file($path);
});


/*
|--------------------------------------------------------------------------
| Protected Routes (Wajib Token Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Profile & Logout
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Master Data
    Route::get('/categories', [MenuController::class, 'getCategories']);
    Route::get('/menus', [MenuController::class, 'getMenus']);
    Route::get('/discounts', [ApiDiscount::class, 'index']);
    Route::get('/settings', [SettlementController::class, 'appSettings']);

    // Modul Transaksi (Orders)
    Route::prefix('orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/history', [OrderController::class, 'history']);
        Route::get('/pending', [OrderController::class, 'getPendingBills']);
        Route::get('/recapitulation', [OrderController::class, 'recapitulation']);
        Route::post('/{id}/void', [OrderController::class, 'voidOrder']);
        Route::post('/{id}/pay', [OrderController::class, 'payPendingOrder']);
    });

    // Modul Shift (Settlement)
    Route::prefix('settlement')->group(function () {
        Route::get('/status', [SettlementController::class, 'currentStatus']);
        Route::get('/last', [SettlementController::class, 'lastClosed']);
        Route::post('/starting-cash', [SettlementController::class, 'storeStartingCash']);
        Route::post('/close', [SettlementController::class, 'closeSettlement']);
    });

    // 🔥 Modul Pengeluaran (Petty Cash)
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);

});
