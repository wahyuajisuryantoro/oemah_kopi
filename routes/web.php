<?php

use App\Http\Controllers\Dapur_DashboardController;
use App\Http\Controllers\Kasir_DashboardController;
use App\Http\Controllers\Kasir_MejaController;
use App\Http\Controllers\Kasir_MesinKasirController;
use App\Http\Controllers\Kasir_PesananController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PembayaranController;
use Illuminate\Support\Facades\Route;




Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.authenticate');
Route::get('/pemesanan/meja/{meja}', [Kasir_PesananController::class, 'createFromQrCode'])->name('pemesanan.create');
Route::prefix('payment')->name('payment.')->group(function () {
   Route::post('/create', [PembayaranController::class, 'createPayment'])->name('create');
   Route::post('/update-status', [PembayaranController::class, 'updatePaymentStatus'])->name('update.status');
   Route::post('/notification', [PembayaranController::class, 'handleNotification'])->name('notification');
   Route::get('/success', [PembayaranController::class, 'paymentSuccess'])->name('success');
   Route::get('/pending', [PembayaranController::class, 'paymentPending'])->name('pending');
   Route::get('/error', [PembayaranController::class, 'paymentError'])->name('error');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Routes for Cashier
    Route::middleware('role:kasir')->group(function () {
        Route::get('/dashboard-kasir', [Kasir_DashboardController::class, 'index'])->name('kasir.dashboard');
        Route::get('/pesanan', [Kasir_DashboardController::class, 'index'])->name('pesanan.index');
        Route::post('/pesanan', [Kasir_DashboardController::class, 'store'])->name('pesanan.store');
        Route::post('/pesanan/{pesanan}/status', [Kasir_DashboardController::class, 'updateStatus'])->name('pesanan.updateStatus');

        // Mesin Kasir
        Route::prefix('mesin-kasir')->group(function () {
            Route::get('/', [Kasir_MesinKasirController::class, 'index'])->name('mesin-kasir.index');
        });

        // Manajemen Meja
        Route::prefix('meja')->group(function () {
            Route::get('/', [Kasir_MejaController::class, 'index'])->name('meja.index');
            Route::get('/create', [Kasir_MejaController::class, 'create'])->name('meja.create');
            Route::post('/', [Kasir_MejaController::class, 'store'])->name('meja.store');
            Route::get('/{meja}', [Kasir_MejaController::class, 'show'])->name('meja.show');
            Route::get('/{meja}/edit', [Kasir_MejaController::class, 'edit'])->name('meja.edit');
            Route::put('/{meja}', [Kasir_MejaController::class, 'update'])->name('meja.update');
            Route::delete('/{meja}', [Kasir_MejaController::class, 'destroy'])->name('meja.destroy');
            Route::patch('/meja/{meja}/toggle-status', [Kasir_MejaController::class, 'toggleStatus'])->name('meja.toggle-status');
            Route::get('/{meja}/generate-qrcode', [Kasir_MejaController::class, 'generateQrCode'])->name('meja.generate-qrcode');
            Route::get('/{meja}/show-qrcode', [Kasir_MejaController::class, 'showQrCode'])->name('meja.show-qrcode');
            Route::get('/{meja}/regenerate-qrcode', [Kasir_MejaController::class, 'regenerateQrCode'])->name('meja.regenerate-qrcode');

        });

        Route::prefix('list-pesanan')->group(function () {
            Route::get('/', [Kasir_PesananController::class, 'index'])->name('list-pesanan.index');
            Route::get('/datatables', [Kasir_PesananController::class, 'getDatatables'])->name('kasir.pesanan.datatables');
            Route::get('/{id}', [Kasir_PesananController::class, 'show'])->name('kasir.pesanan.show');
            Route::delete('/{id}', [Kasir_PesananController::class, 'destroy'])->name('kasir.pesanan.destroy');
        });


    });

    // Routes for Kitchen
    Route::middleware(['role:dapur'])->group(function () {
        Route::get('/dashboard-dapur', [Dapur_DashboardController::class, 'index'])->name('dapur.dashboard');
        Route::post('/dapur/pesanan/{pesanan}/status', [Dapur_DashboardController::class, 'updateStatus'])->name('dapur.updateStatus');
        Route::get('/dapur/antrian-data', [Dapur_DashboardController::class, 'getAntrianData'])->name('dapur.getAntrianData');
    });
});
