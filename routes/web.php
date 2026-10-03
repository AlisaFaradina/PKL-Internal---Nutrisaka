<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminTokenController;
use App\Http\Controllers\ActivationController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SppgController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// PANEL ADMINISTRATOR (Pemilik Aplikasi — Pengaturan Token Lisensi)
// =========================================================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [AdminTokenController::class, 'index'])->name('dashboard');
        Route::get('/tokens', [AdminTokenController::class, 'index'])->name('tokens.index');
        Route::post('/tokens', [AdminTokenController::class, 'store'])->name('tokens.store');
        Route::post('/tokens/{token}/revoke', [AdminTokenController::class, 'revoke'])->name('tokens.revoke');
        Route::post('/tokens/{token}/restore', [AdminTokenController::class, 'restore'])->name('tokens.restore');
        Route::post('/tokens/{token}/unbind', [AdminTokenController::class, 'unbind'])->name('tokens.unbind');
        Route::delete('/tokens/{token}', [AdminTokenController::class, 'destroy'])->name('tokens.destroy');
        Route::get('/tokens/export/csv', [AdminTokenController::class, 'exportCsv'])->name('tokens.export-csv');

        // Permintaan Lisensi Perangkat (License Requests)
        Route::get('/requests', [\App\Http\Controllers\Admin\AdminLicenseRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{licenseRequest}', [\App\Http\Controllers\Admin\AdminLicenseRequestController::class, 'show'])->name('requests.show');
        Route::post('/requests/{licenseRequest}/generate', [\App\Http\Controllers\Admin\AdminLicenseRequestController::class, 'generate'])->name('requests.generate');
        Route::post('/requests/{licenseRequest}/reject', [\App\Http\Controllers\Admin\AdminLicenseRequestController::class, 'reject'])->name('requests.reject');
    });
});

// Onboarding Flow: Pemilihan Mode Aplikasi (Mode Demo vs Mode Asli)
Route::get('/onboarding', [ActivationController::class, 'showOnboarding'])->name('onboarding');
Route::post('/onboarding/demo', [ActivationController::class, 'startDemoMode'])->name('onboarding.demo');
Route::post('/onboarding/real', [ActivationController::class, 'startRealMode'])->name('onboarding.real');

// API Pengambilan Device ID Aktual dari Perangkat
Route::get('/api/device-id', [ActivationController::class, 'getDeviceIdApi'])->name('api.device-id');

// Registrasi & Permohonan Lisensi Perangkat (Mengirim email ke admin: sakanutri@gmail.com)
Route::post('/register-request', [ActivationController::class, 'submitLicenseRequest'])->name('activation.register-request');

// Halaman Status & Kedaluwarsa Masa Uji Coba (Trial 7 Hari)
Route::get('/trial-status', [ActivationController::class, 'showTrialStatus'])->name('trial.status');
Route::get('/trial-expired', [ActivationController::class, 'showTrialExpired'])->name('trial.expired');

// Login / Aktivasi Lisensi Perangkat dengan Token dari Admin
Route::get('/login', [ActivationController::class, 'showRegister'])->name('login');
Route::post('/login', [ActivationController::class, 'register'])->name('login.store');
Route::get('/register-token', [ActivationController::class, 'showRegister'])->name('activation.register');
Route::post('/register-token', [ActivationController::class, 'register'])->name('activation.register.store');
Route::post('/register-token/demo', [ActivationController::class, 'registerDemo'])->name('activation.register.demo');
Route::post('/register-token/trial', [ActivationController::class, 'startTrial'])->name('activation.register.trial');
Route::redirect('/aktivasi', '/login');
Route::get('/terkunci', [ActivationController::class, 'showLocked'])->name('activation.locked');
Route::post('/terkunci/revalidate', [ActivationController::class, 'revalidate'])->name('activation.revalidate');
Route::post('/request-license-token', [ActivationController::class, 'requestLicenseToken'])->name('activation.request-token');
Route::post('/copy-license-draft', [ActivationController::class, 'copyLicenseRequestDraft'])->name('activation.copy-draft');

// Pengalihan Mode Aplikasi dari Halaman About
Route::post('/about/mode', [AboutController::class, 'switchMode'])->name('about.switch-mode');

// Redirect root to dashboard
Route::redirect('/', '/dashboard');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// SPPG / Pelanggan
Route::resource('sppgs', SppgController::class);

// Kategori Produk
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

// Produk & Katalog
Route::resource('products', ProductController::class);

// Pesanan SPPG
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
Route::post('/orders/{order}/convert-to-sale', [OrderController::class, 'convertToSale'])->name('orders.convert-to-sale');

// Penjualan & Faktur
Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
Route::get('/sales/{sale}/print-invoice', [SaleController::class, 'printInvoice'])->name('sales.print-invoice');
Route::get('/sales/{sale}/print-thermal', [SaleController::class, 'printThermal'])->name('sales.print-thermal');

// Pembayaran & Piutang
Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
Route::post('/payments/{payment}/upload-proof', [PaymentController::class, 'uploadProof'])->name('payments.upload-proof');
Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirmPayment'])->name('payments.confirm');

// Stok & Mutasi
Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
Route::get('/stock/in', [StockController::class, 'createIn'])->name('stock.create-in');
Route::post('/stock/in', [StockController::class, 'storeIn'])->name('stock.store-in');
Route::get('/stock/adjustment', [StockController::class, 'createAdjustment'])->name('stock.create-adjustment');
Route::post('/stock/adjustment', [StockController::class, 'storeAdjustment'])->name('stock.store-adjustment');
Route::get('/stock/history', [StockController::class, 'history'])->name('stock.history');
Route::get('/stock/{product}', [StockController::class, 'show'])->whereNumber('product')->name('stock.show');

// Laporan
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

// Pengaturan & Profil
Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.update-profile');
Route::post('/settings/pin', [SettingController::class, 'updatePin'])->name('settings.update-pin');
Route::get('/settings/export-backup', [SettingController::class, 'exportBackup'])->name('settings.export-backup');
Route::post('/settings/import-restore', [SettingController::class, 'importRestore'])->name('settings.import-restore');
Route::post('/settings/load-demo-data', [SettingController::class, 'loadDemoData'])->name('settings.load-demo-data');
Route::post('/settings/license/check', [LicenseController::class, 'checkOnline'])->name('settings.license.check');
Route::post('/settings/license/deactivate', [LicenseController::class, 'deactivate'])->name('settings.license.deactivate');

// Tentang Aplikasi
Route::get('/about', [AboutController::class, 'index'])->name('about.index');
