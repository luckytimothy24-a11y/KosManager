<?php

use App\Http\Controllers\Admin\AdvertisingController as AdminAdvertisingController;
use App\Http\Controllers\AdvertisingClickController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Owner\AdvertisingController;
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
use App\Http\Controllers\SuperAdmin\AdvertisingCampaignController;
use App\Http\Controllers\SuperAdmin\AdvertisingDashboardController;
use App\Http\Controllers\SuperAdmin\AdvertisingPackageController;
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

    // Advertising (Super Admin)
    Route::get('advertising', [AdvertisingDashboardController::class, 'index'])->name('advertising.dashboard');
    Route::get('advertising/packages', [AdvertisingPackageController::class, 'index'])->name('advertising.packages.index');
    Route::get('advertising/packages/create', [AdvertisingPackageController::class, 'create'])->name('advertising.packages.create');
    Route::post('advertising/packages', [AdvertisingPackageController::class, 'store'])->name('advertising.packages.store');
    Route::get('advertising/packages/{package}/edit', [AdvertisingPackageController::class, 'edit'])->name('advertising.packages.edit');
    Route::put('advertising/packages/{package}', [AdvertisingPackageController::class, 'update'])->name('advertising.packages.update');
    Route::delete('advertising/packages/{package}', [AdvertisingPackageController::class, 'destroy'])->name('advertising.packages.destroy');

    Route::get('advertising/campaigns', [AdvertisingCampaignController::class, 'index'])->name('advertising.campaigns.index');
    Route::get('advertising/campaigns/create', [AdvertisingCampaignController::class, 'create'])->name('advertising.campaigns.create');
    Route::post('advertising/campaigns', [AdvertisingCampaignController::class, 'store'])->name('advertising.campaigns.store');
    Route::get('advertising/campaigns/{campaign}', [AdvertisingCampaignController::class, 'show'])->name('advertising.campaigns.show');
    Route::get('advertising/campaigns/{campaign}/edit', [AdvertisingCampaignController::class, 'edit'])->name('advertising.campaigns.edit');
    Route::put('advertising/campaigns/{campaign}', [AdvertisingCampaignController::class, 'update'])->name('advertising.campaigns.update');
    Route::post('advertising/campaigns/{campaign}/approve', [AdvertisingCampaignController::class, 'approve'])->name('advertising.campaigns.approve');
    Route::post('advertising/campaigns/{campaign}/reject', [AdvertisingCampaignController::class, 'reject'])->name('advertising.campaigns.reject');
    Route::post('advertising/campaigns/{campaign}/suspend', [AdvertisingCampaignController::class, 'suspend'])->name('advertising.campaigns.suspend');

    Route::get('advertising/revenue', [AdvertisingCampaignController::class, 'revenue'])->name('advertising.revenue');
    Route::get('advertising/revenue/export', [AdvertisingCampaignController::class, 'exportCsv'])->name('advertising.revenue.export');
});

// Owner Routes
Route::middleware(['auth', 'role:super_admin,owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::resource('kos', KosController::class)->parameter('kos', 'kos');
    Route::resource('kamar', KamarController::class);

    // Advertising (Owner + Super Admin)
    Route::get('advertising', [AdvertisingController::class, 'dashboard'])->name('advertising.dashboard');
    Route::get('advertising/campaigns', [AdvertisingController::class, 'index'])->name('advertising.index');
    Route::get('advertising/campaigns/create', [AdvertisingController::class, 'create'])->name('advertising.create');
    Route::post('advertising/campaigns', [AdvertisingController::class, 'store'])->name('advertising.store');
    Route::get('advertising/campaigns/{campaign}', [AdvertisingController::class, 'show'])->name('advertising.show');
    Route::post('advertising/campaigns/{campaign}/pay', [AdvertisingController::class, 'pay'])->name('advertising.pay');
    Route::post('advertising/campaigns/{campaign}/cancel', [AdvertisingController::class, 'cancel'])->name('advertising.cancel');

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
    // Advertising (Admin moderation)
    Route::get('advertising', [AdminAdvertisingController::class, 'index'])->name('advertising.index');
    Route::get('advertising/create', [AdminAdvertisingController::class, 'create'])->name('advertising.create');
    Route::post('advertising', [AdminAdvertisingController::class, 'store'])->name('advertising.store');
    Route::get('advertising/{campaign}', [AdminAdvertisingController::class, 'show'])->name('advertising.show');
    Route::get('advertising/{campaign}/edit', [AdminAdvertisingController::class, 'edit'])->name('advertising.edit');
    Route::put('advertising/{campaign}', [AdminAdvertisingController::class, 'update'])->name('advertising.update');
    Route::post('advertising/{campaign}/approve', [AdminAdvertisingController::class, 'approve'])->name('advertising.approve');
    Route::post('advertising/{campaign}/reject', [AdminAdvertisingController::class, 'reject'])->name('advertising.reject');
    Route::post('advertising/{campaign}/suspend', [AdminAdvertisingController::class, 'suspend'])->name('advertising.suspend');

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

    // Click tracking iklan (sponsored/featured) -> redirect ke detail kos.
    Route::get('ad/click/{campaign}', [AdvertisingClickController::class, 'track'])
        ->where('campaign', '[0-9]+')
        ->name('ad.click');

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
