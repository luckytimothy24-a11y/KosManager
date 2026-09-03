<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Owner\BookingController;
use App\Http\Controllers\Owner\CheckInController;
use App\Http\Controllers\Owner\CheckOutController;
use App\Http\Controllers\Owner\FavoriteController;
use App\Http\Controllers\Owner\KamarController;
use App\Http\Controllers\Owner\KontrakController;
use App\Http\Controllers\Owner\KosController;
use App\Http\Controllers\Owner\PembayaranController;
use App\Http\Controllers\Owner\PenghuniController;
use App\Http\Controllers\Owner\TagihanController;
use App\Http\Controllers\Owner\TenantBookingController;
use App\Http\Controllers\Owner\TenantKontrakController;
use App\Http\Controllers\Owner\TenantKosController;
use App\Http\Controllers\Owner\TenantPembayaranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\AuditLogController;
use App\Http\Controllers\SuperAdmin\FasilitasController;
use App\Http\Controllers\SuperAdmin\LaporanController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\Webhook\PaymentGatewayWebhookController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class);

Route::get('/health', HealthController::class)->name('health');
Route::get('/up', HealthController::class)->name('up');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'active', 'verified'])
    ->name('dashboard');

Route::post('/webhooks/payment-gateway', [PaymentGatewayWebhookController::class, 'handle'])
    ->middleware('throttle:20,1')
    ->name('webhook.payment-gateway');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
        ->name('notifications.markAllRead');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::get('/pembayaran/{pembayaran}/proof', [PembayaranController::class, 'downloadProof'])
        ->name('pembayaran.proof');
});

// Super Admin Routes
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('fasilitas', FasilitasController::class)->except(['show'])->parameter('fasilitas', 'fasilitas');

    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/export-pdf/{type}', [LaporanController::class, 'exportPdf'])->name('laporan.export-pdf');
    Route::get('laporan/export-excel/{type}', [LaporanController::class, 'exportExcel'])->name('laporan.export-excel');

    Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
});

// Owner Routes
Route::middleware(['auth', 'role:super_admin,owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::resource('kos', KosController::class)->parameter('kos', 'kos');
    Route::resource('kamar', KamarController::class);

    Route::get('booking', [BookingController::class, 'index'])->name('booking.index');
    Route::get('booking/{booking}', [BookingController::class, 'show'])->name('booking.show');
    Route::post('booking/{booking}/approve', [BookingController::class, 'approve'])->name('booking.approve');
    Route::post('booking/{booking}/reject', [BookingController::class, 'reject'])->name('booking.reject');

    Route::get('penghuni', [PenghuniController::class, 'index'])->name('penghuni.index');
    Route::get('penghuni/{penghuni}', [PenghuniController::class, 'show'])->name('penghuni.show');

    Route::get('kontrak', [KontrakController::class, 'index'])->name('kontrak.index');
    Route::get('kontrak/{kontrak}', [KontrakController::class, 'show'])->name('kontrak.show');

    Route::get('tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
    Route::get('tagihan/create', [TagihanController::class, 'create'])->name('tagihan.create');
    Route::post('tagihan', [TagihanController::class, 'store'])->name('tagihan.store');
    Route::get('tagihan/{tagihan}', [TagihanController::class, 'show'])->name('tagihan.show');

    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::get('pembayaran/{pembayaran}', [PembayaranController::class, 'show'])->name('pembayaran.show');
    Route::post('pembayaran/{pembayaran}/verify', [PembayaranController::class, 'verify'])->name('pembayaran.verify');
    Route::post('pembayaran/{pembayaran}/reject', [PembayaranController::class, 'reject'])->name('pembayaran.reject');

    Route::get('check-in', [CheckInController::class, 'index'])->name('checkin.index');
    Route::post('check-in/{booking}', [CheckInController::class, 'process'])->name('checkin.process');

    Route::get('check-out', [CheckOutController::class, 'index'])->name('checkout.index');
    Route::post('check-out/{penghuni}/request', [CheckOutController::class, 'requestCheckout'])->name('checkout.request');
    Route::post('check-out/{checkOut}/approve', [CheckOutController::class, 'approve'])->name('checkout.approve');
    Route::post('check-out/{checkOut}/reject', [CheckOutController::class, 'reject'])->name('checkout.reject');

    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/export-pdf/{type}', [LaporanController::class, 'exportPdf'])->name('laporan.export-pdf');
    Route::get('laporan/export-excel/{type}', [LaporanController::class, 'exportExcel'])->name('laporan.export-excel');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('booking', [BookingController::class, 'index'])->name('booking.index');
    Route::get('booking/{booking}', [BookingController::class, 'show'])->name('booking.show');
    Route::post('booking/{booking}/approve', [BookingController::class, 'approve'])->name('booking.approve');
    Route::post('booking/{booking}/reject', [BookingController::class, 'reject'])->name('booking.reject');

    Route::get('kamar', [KamarController::class, 'index'])->name('kamar.index');
    Route::get('kamar/{kamar}', [KamarController::class, 'show'])->name('kamar.show');

    Route::get('penghuni', [PenghuniController::class, 'index'])->name('penghuni.index');
    Route::get('penghuni/{penghuni}', [PenghuniController::class, 'show'])->name('penghuni.show');

    Route::get('kontrak', [KontrakController::class, 'index'])->name('kontrak.index');
    Route::get('kontrak/{kontrak}', [KontrakController::class, 'show'])->name('kontrak.show');

    Route::get('tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
    Route::get('tagihan/create', [TagihanController::class, 'create'])->name('tagihan.create');
    Route::post('tagihan', [TagihanController::class, 'store'])->name('tagihan.store');
    Route::get('tagihan/{tagihan}', [TagihanController::class, 'show'])->name('tagihan.show');

    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::get('pembayaran/{pembayaran}', [PembayaranController::class, 'show'])->name('pembayaran.show');
    Route::post('pembayaran/{pembayaran}/verify', [PembayaranController::class, 'verify'])->name('pembayaran.verify');
    Route::post('pembayaran/{pembayaran}/reject', [PembayaranController::class, 'reject'])->name('pembayaran.reject');

    Route::get('check-in', [CheckInController::class, 'index'])->name('checkin.index');
    Route::post('check-in/{booking}', [CheckInController::class, 'process'])->name('checkin.process');

    Route::get('check-out', [CheckOutController::class, 'index'])->name('checkout.index');
    Route::post('check-out/{penghuni}/request', [CheckOutController::class, 'requestCheckout'])->name('checkout.request');
    Route::post('check-out/{checkOut}/approve', [CheckOutController::class, 'approve'])->name('checkout.approve');
    Route::post('check-out/{checkOut}/reject', [CheckOutController::class, 'reject'])->name('checkout.reject');

    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/export-pdf/{type}', [LaporanController::class, 'exportPdf'])->name('laporan.export-pdf');
    Route::get('laporan/export-excel/{type}', [LaporanController::class, 'exportExcel'])->name('laporan.export-excel');
});

// Tenant Routes
Route::middleware(['auth', 'role:tenant'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::get('kos', [TenantKosController::class, 'index'])->name('kos.index');
    Route::get('kos/{kos}', [TenantKosController::class, 'show'])->name('kos.show');

    Route::get('booking', [TenantBookingController::class, 'index'])->name('booking.index');
    Route::get('booking/create', [TenantBookingController::class, 'create'])->name('booking.create');
    Route::post('booking', [TenantBookingController::class, 'store'])->name('booking.store');
    Route::get('booking/success/{booking}', [TenantBookingController::class, 'success'])->name('booking.success');
    Route::get('booking/{booking}', [TenantBookingController::class, 'show'])->name('booking.show');
    Route::post('booking/{booking}/cancel', [TenantBookingController::class, 'cancel'])->name('booking.cancel');

    Route::post('favorites/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    Route::post('check-out/{penghuni}/request', [CheckOutController::class, 'requestCheckout'])->name('checkout.request');

    Route::get('kontrak', [TenantKontrakController::class, 'index'])->name('kontrak.index');
    Route::get('kontrak/{kontrak}', [TenantKontrakController::class, 'show'])->name('kontrak.show');

    Route::get('tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
    Route::get('tagihan/{tagihan}', [TagihanController::class, 'show'])->name('tagihan.show');

    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::get('pembayaran/{pembayaran}', [PembayaranController::class, 'show'])->name('pembayaran.show');
    Route::post('pembayaran', [TenantPembayaranController::class, 'store'])->name('pembayaran.store');
    Route::post('pembayaran/gateway', [TenantPembayaranController::class, 'gatewayStore'])
        ->name('pembayaran.gateway');
});

require __DIR__.'/auth.php';
