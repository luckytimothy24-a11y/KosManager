<?php

use App\Http\Controllers\Admin\AdvertisingController as AdminAdvertisingController;
use App\Http\Controllers\Admin\OwnerController as AdminOwnerController;
use App\Http\Controllers\AdvertisingClickController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Owner\AdminOwnerMessageController;
use App\Http\Controllers\Owner\AdvertisingController;
use App\Http\Controllers\Owner\BookingController;
use App\Http\Controllers\Owner\CheckInController;
use App\Http\Controllers\Owner\CheckOutController;
use App\Http\Controllers\Owner\FavoriteController;
use App\Http\Controllers\Owner\KamarController;
use App\Http\Controllers\Owner\KontrakController;
use App\Http\Controllers\Owner\KosController;
use App\Http\Controllers\Owner\ManagementAgreementController;
use App\Http\Controllers\Owner\MessageController;
use App\Http\Controllers\Owner\OwnerBankAccountController;
use App\Http\Controllers\Owner\OwnerBookingOccupantController;
use App\Http\Controllers\Owner\OwnerPaymentDistributionController;
use App\Http\Controllers\Owner\PayoutAdjustmentController;
use App\Http\Controllers\Owner\PayoutDisbursementController;
use App\Http\Controllers\Owner\PembayaranController;
use App\Http\Controllers\Owner\PenghuniController;
use App\Http\Controllers\Owner\PropertyOwnerPayoutController;
use App\Http\Controllers\Owner\TagihanController;
use App\Http\Controllers\Owner\TenantBookingController;
use App\Http\Controllers\Owner\TenantBookingPaymentController;
use App\Http\Controllers\Owner\TenantKontrakController;
use App\Http\Controllers\Owner\TenantKosController;
use App\Http\Controllers\Owner\TenantLaporanController;
use App\Http\Controllers\Owner\TenantMessageController;
use App\Http\Controllers\Owner\TenantPembayaranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\AdvertisingCampaignController;
use App\Http\Controllers\SuperAdmin\AdvertisingDashboardController;
use App\Http\Controllers\SuperAdmin\AdvertisingPackageController;
use App\Http\Controllers\SuperAdmin\AuditLogController;
use App\Http\Controllers\SuperAdmin\FasilitasController;
use App\Http\Controllers\SuperAdmin\LaporanController;
use App\Http\Controllers\SuperAdmin\ModerationController;
use App\Http\Controllers\SuperAdmin\OwnerController;
use App\Http\Controllers\SuperAdmin\PartnerController;
use App\Http\Controllers\SuperAdmin\SettingsController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\Tenant\BookingOccupantIdentityController;
use App\Http\Controllers\Tenant\TenantBookingOccupantController;
use App\Http\Controllers\Tenant\TenantCheckInController;
use App\Http\Controllers\Tenant\TenantInvitationController;
use App\Http\Controllers\Webhook\PaymentGatewayWebhookController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('welcome');

Route::get('/ad/click/{campaign}', [AdvertisingClickController::class, 'track'])
    ->where('campaign', '[0-9]+')
    ->name('ad.click');

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

    Route::get('/pembayaran/{pembayaran}/kuitansi', [PembayaranController::class, 'receipt'])
        ->name('pembayaran.receipt');
});

// Super Admin Routes
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('users/export', [UserController::class, 'exportCsv'])->name('users.export');
    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('fasilitas', FasilitasController::class)->parameter('fasilitas', 'fasilitas');

    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/export-pdf', [LaporanController::class, 'exportCombinedPdf'])->name('laporan.export-pdf');
    Route::get('laporan/export-excel', [LaporanController::class, 'exportCombinedExcel'])->name('laporan.export-excel');

    Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

    // Owner Management
    Route::get('owners', [OwnerController::class, 'index'])->name('owners.index');
    Route::get('owners/{owner}', [OwnerController::class, 'show'])->name('owners.show');
    Route::post('owners/{owner}/activate', [OwnerController::class, 'activate'])->name('owners.activate');
    Route::post('owners/{owner}/deactivate', [OwnerController::class, 'deactivate'])->name('owners.deactivate');

    // Admin Management
    Route::get('admins', [AdminController::class, 'index'])->name('admins.index');
    Route::get('admins/create', [AdminController::class, 'create'])->name('admins.create');
    Route::post('admins', [AdminController::class, 'store'])->name('admins.store');
    Route::get('admins/{admin}', [AdminController::class, 'show'])->name('admins.show');
    Route::post('admins/{admin}/activate', [AdminController::class, 'activate'])->name('admins.activate');
    Route::post('admins/{admin}/deactivate', [AdminController::class, 'deactivate'])->name('admins.deactivate');
    Route::post('admins/{admin}/assign', [AdminController::class, 'assign'])->name('admins.assign');
    Route::post('admins/{admin}/unassign/{kos}', [AdminController::class, 'unassign'])->name('admins.unassign');

    // Moderation
    Route::get('moderation', [ModerationController::class, 'index'])->name('moderation.index');
    Route::get('moderation/kos', [ModerationController::class, 'kos'])->name('moderation.kos');
    Route::post('moderation/kos/{kos}/approve', [ModerationController::class, 'approveKos'])->name('moderation.kos.approve');
    Route::post('moderation/kos/{kos}/reject', [ModerationController::class, 'rejectKos'])->name('moderation.kos.reject');
    Route::get('moderation/photos', [ModerationController::class, 'photos'])->name('moderation.photos');
    Route::post('moderation/photos/{photo}/approve', [ModerationController::class, 'approvePhoto'])->name('moderation.photos.approve');
    Route::post('moderation/photos/{photo}/reject', [ModerationController::class, 'rejectPhoto'])->name('moderation.photos.reject');
    Route::get('moderation/advertising', [ModerationController::class, 'advertising'])->name('moderation.advertising');
    Route::get('moderation/damage-reports', [ModerationController::class, 'damageReports'])->name('moderation.damage-reports');

    // System Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('system-health', [App\Http\Controllers\SuperAdmin\HealthController::class, 'index'])->name('system-health.index');

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
    Route::post('advertising/orders/{order}/mark-paid', [AdvertisingCampaignController::class, 'markThirdPartyOrderPaid'])->name('advertising.orders.mark-paid');

    Route::get('advertising/revenue', [AdvertisingCampaignController::class, 'revenue'])->name('advertising.revenue');
    Route::get('advertising/revenue/export', [AdvertisingCampaignController::class, 'exportCsv'])->name('advertising.revenue.export');

    // Partner (mitra monetisasi — DANA, Shopee, GoPay, dll.)
    Route::get('partners', [PartnerController::class, 'index'])->name('partners.index');
    Route::get('partners/create', [PartnerController::class, 'create'])->name('partners.create');
    Route::post('partners', [PartnerController::class, 'store'])->name('partners.store');
    Route::get('partners/{partner}/edit', [PartnerController::class, 'edit'])->name('partners.edit');
    Route::put('partners/{partner}', [PartnerController::class, 'update'])->name('partners.update');
    Route::delete('partners/{partner}', [PartnerController::class, 'destroy'])->name('partners.destroy');
});

// Owner Routes
Route::middleware(['auth', 'role:super_admin,owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::resource('kos', KosController::class)->parameter('kos', 'kos');
    Route::resource('kamar', KamarController::class);
    Route::post('kamar/{kamar}/penghuni', [KamarController::class, 'addPenghuni'])->name('kamar.add-penghuni');

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
    // Transisi status booking — hanya owner/admin. Tenant tidak boleh mengubah status.
    Route::post('booking/{booking}/approve', [BookingController::class, 'approve'])->name('booking.approve');
    Route::post('booking/{booking}/reject', [BookingController::class, 'reject'])->name('booking.reject');
    // Cancellation request review — owner approve → booking cancelled; reject → booking unchanged.
    Route::post('booking-cancellation/{cancellationRequest}/approve', [BookingController::class, 'approveCancellation'])->name('booking.cancellation.approve');
    Route::post('booking-cancellation/{cancellationRequest}/reject', [BookingController::class, 'rejectCancellation'])->name('booking.cancellation.reject');

    // Rekening bank Owner (penerima pembayaran booking)
    Route::get('bank-account', [OwnerBankAccountController::class, 'edit'])->name('bank-account.edit');
    Route::put('bank-account', [OwnerBankAccountController::class, 'update'])->name('bank-account.update');

    // Distribusi pembayaran booking → hak Owner
    Route::get('distribution', [OwnerPaymentDistributionController::class, 'index'])->name('distribution.index');
    Route::post('distribution/{distribution}/mark-processed', [OwnerPaymentDistributionController::class, 'markProcessed'])->name('distribution.mark-processed');

    // Payout/Settlement ke Pemilik Kos Asli (property owner).
    // Transfer manual di luar aplikasi; KosManager hanya mencatat.
    // TERPISAH dari distribution (booking → hak role Owner).
    Route::get('payouts', [PropertyOwnerPayoutController::class, 'index'])->name('payouts.index');
    Route::post('payouts', [PropertyOwnerPayoutController::class, 'store'])->name('payouts.store');
    Route::get('payouts/{payout}', [PropertyOwnerPayoutController::class, 'show'])->name('payouts.show');
    Route::get('payouts/{payout}/recipient-account', [PropertyOwnerPayoutController::class, 'revealRecipientAccount'])->name('payouts.recipient-account');
    Route::post('payouts/{payout}/transfer', [PropertyOwnerPayoutController::class, 'markTransferred'])->name('payouts.transfer');
    Route::post('payouts/{payout}/verify', [PropertyOwnerPayoutController::class, 'verify'])->name('payouts.verify');
    Route::post('payouts/{payout}/reject', [PropertyOwnerPayoutController::class, 'reject'])->name('payouts.reject');
    Route::get('payouts/{payout}/proof', [PropertyOwnerPayoutController::class, 'downloadProof'])->name('payouts.proof');
    Route::post('kos/{kos}/property-owner', [PropertyOwnerPayoutController::class, 'setPropertyOwner'])->name('payouts.set-property-owner');
    Route::post('payouts/{payout}/disbursements', [PayoutDisbursementController::class, 'store'])->name('payouts.disbursements.store');
    Route::get('disbursements/{disbursement}/proof', [PayoutDisbursementController::class, 'downloadProof'])->name('disbursements.proof');
    Route::post('disbursements/{disbursement}/verify', [PayoutDisbursementController::class, 'verify'])->name('disbursements.verify');
    Route::post('disbursements/{disbursement}/reject', [PayoutDisbursementController::class, 'reject'])->name('disbursements.reject');
    Route::post('adjustments', [PayoutAdjustmentController::class, 'store'])->name('adjustments.store');

    Route::get('agreements', [ManagementAgreementController::class, 'index'])->name('agreements.index');
    Route::post('agreements', [ManagementAgreementController::class, 'store'])->name('agreements.store');

    Route::get('penghuni', [PenghuniController::class, 'index'])->name('penghuni.index');
    Route::get('penghuni/{penghuni}', [PenghuniController::class, 'show'])->name('penghuni.show');

    Route::get('kontrak', [KontrakController::class, 'index'])->name('kontrak.index');
    Route::get('kontrak/{kontrak}', [KontrakController::class, 'show'])->name('kontrak.show');

    Route::get('tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
    Route::get('tagihan/create', [TagihanController::class, 'create'])->name('tagihan.create');
    Route::post('tagihan', [TagihanController::class, 'store'])->name('tagihan.store');
    Route::get('tagihan/{tagihan}', [TagihanController::class, 'show'])->name('tagihan.show');

    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('pembayaran/bulk-verify', [PembayaranController::class, 'bulkVerify'])->name('pembayaran.bulk-verify');
    Route::get('pembayaran/{pembayaran}', [PembayaranController::class, 'show'])->name('pembayaran.show');
    Route::post('pembayaran/{pembayaran}/verify', [PembayaranController::class, 'verify'])->name('pembayaran.verify')->middleware('throttle:5,30');
    Route::post('pembayaran/{pembayaran}/reject', [PembayaranController::class, 'reject'])->name('pembayaran.reject')->middleware('throttle:5,30');

    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('messages/inquiries', [MessageController::class, 'inquiries'])->name('messages.inquiries');
    Route::get('messages/{tenant}/latest', [MessageController::class, 'latest'])->name('messages.latest');
    Route::get('messages/{tenant}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('messages/{tenant}', [MessageController::class, 'reply'])->name('messages.reply');

    Route::get('check-in', [CheckInController::class, 'index'])->name('checkin.index');
    Route::post('check-in/{booking}', [CheckInController::class, 'process'])->name('checkin.process');
    // Owner review of tenant identity (pending -> confirm/reject) — tenant cannot hit these (owner/admin middleware)
    Route::post('check-in/{booking}/confirm', [CheckInController::class, 'confirmIdentity'])->name('checkin.confirm')->middleware('throttle:10,60');
    Route::post('check-in/{booking}/reject', [CheckInController::class, 'rejectIdentity'])->name('checkin.reject')->middleware('throttle:10,60');

    // Booking occupants — owner manages per-orang identity & check-in
    Route::get('booking/{booking}/occupants', [OwnerBookingOccupantController::class, 'index'])->name('booking-occupants.index');
    Route::post('booking-occupants/{occupant}/confirm', [OwnerBookingOccupantController::class, 'confirmIdentity'])->name('booking-occupants.confirm')->middleware('throttle:10,60');
    Route::post('booking-occupants/{occupant}/reject', [OwnerBookingOccupantController::class, 'rejectIdentity'])->name('booking-occupants.reject')->middleware('throttle:10,60');
    Route::post('booking-occupants/{occupant}/checkin', [OwnerBookingOccupantController::class, 'checkIn'])->name('booking-occupants.checkin')->middleware('throttle:10,60');

    Route::get('check-out', [CheckOutController::class, 'index'])->name('checkout.index');
    Route::post('check-out/bulk-approve', [CheckOutController::class, 'bulkApprove'])->name('checkout.bulk-approve')->middleware('throttle:5,60');
    Route::post('check-out/{penghuni}/request', [CheckOutController::class, 'requestCheckout'])->name('checkout.request');
    Route::post('check-out/{checkOut}/approve', [CheckOutController::class, 'approve'])->name('checkout.approve')->middleware('throttle:10,60');
    Route::post('check-out/{checkOut}/reject', [CheckOutController::class, 'reject'])->name('checkout.reject')->middleware('throttle:10,60');
    Route::post('check-out/{checkOut}/cancel', [CheckOutController::class, 'cancel'])->name('checkout.cancel')->middleware('throttle:10,60');

    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/export-pdf', [LaporanController::class, 'exportCombinedPdf'])->name('laporan.export-pdf');
    Route::get('laporan/export-excel', [LaporanController::class, 'exportCombinedExcel'])->name('laporan.export-excel');

    // Refund management
    Route::get('refund', [App\Http\Controllers\Owner\RefundController::class, 'index'])->name('refund.index');
    Route::post('refund/{pembayaran}/approve', [App\Http\Controllers\Owner\RefundController::class, 'approve'])->name('refund.approve');
    Route::post('refund/{pembayaran}/reject', [App\Http\Controllers\Owner\RefundController::class, 'reject'])->name('refund.reject');
});

// Admin Routes
Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
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
    Route::post('advertising/orders/{order}/mark-paid', [AdminAdvertisingController::class, 'markThirdPartyOrderPaid'])->name('advertising.orders.mark-paid');

    Route::get('booking', [BookingController::class, 'index'])->name('booking.index');
    Route::get('booking/{booking}', [BookingController::class, 'show'])->name('booking.show');
    Route::post('booking/{booking}/approve', [BookingController::class, 'approve'])->name('booking.approve');
    Route::post('booking/{booking}/reject', [BookingController::class, 'reject'])->name('booking.reject');
    Route::post('booking-cancellation/{cancellationRequest}/approve', [BookingController::class, 'approveCancellation'])->name('booking.cancellation.approve');
    Route::post('booking-cancellation/{cancellationRequest}/reject', [BookingController::class, 'rejectCancellation'])->name('booking.cancellation.reject');
    Route::get('distribution', [OwnerPaymentDistributionController::class, 'index'])->name('distribution.index');
    Route::post('distribution/{distribution}/mark-processed', [OwnerPaymentDistributionController::class, 'markProcessed'])->name('distribution.mark-processed');

    Route::resource('kos', KosController::class)->parameter('kos', 'kos');
    Route::resource('kamar', KamarController::class);
    Route::post('kamar/{kamar}/penghuni', [KamarController::class, 'addPenghuni'])->name('kamar.add-penghuni');

    Route::resource('fasilitas', App\Http\Controllers\Admin\FasilitasController::class);

    Route::get('penghuni', [PenghuniController::class, 'index'])->name('penghuni.index');
    Route::get('penghuni/{penghuni}', [PenghuniController::class, 'show'])->name('penghuni.show');

    Route::get('kontrak', [KontrakController::class, 'index'])->name('kontrak.index');
    Route::get('kontrak/{kontrak}', [KontrakController::class, 'show'])->name('kontrak.show');

    Route::get('tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
    Route::get('tagihan/create', [TagihanController::class, 'create'])->name('tagihan.create');
    Route::post('tagihan', [TagihanController::class, 'store'])->name('tagihan.store');
    Route::get('tagihan/{tagihan}', [TagihanController::class, 'show'])->name('tagihan.show');

    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('pembayaran/bulk-verify', [PembayaranController::class, 'bulkVerify'])->name('pembayaran.bulk-verify');
    Route::get('pembayaran/{pembayaran}', [PembayaranController::class, 'show'])->name('pembayaran.show');
    Route::post('pembayaran/{pembayaran}/verify', [PembayaranController::class, 'verify'])->name('pembayaran.verify')->middleware('throttle:5,30');
    Route::post('pembayaran/{pembayaran}/reject', [PembayaranController::class, 'reject'])->name('pembayaran.reject')->middleware('throttle:5,30');

    // Pesan read-only untuk admin (audit sengketa) — tanpa rute reply.
    Route::get('messages', [MessageController::class, 'adminIndex'])->name('messages.index');
    Route::get('messages/{tenant}', [MessageController::class, 'adminShow'])->name('messages.show');

    // Komunikasi operasional Admin ↔ Owner (kos assigned saja). Bukan Owner Management.
    Route::get('owner-contacts', [AdminOwnerMessageController::class, 'index'])->name('owner-messages.index');
    Route::get('owner-messages/{kos}/{owner}', [AdminOwnerMessageController::class, 'show'])->name('owner-messages.show');
    Route::post('owner-messages/{kos}/{owner}', [AdminOwnerMessageController::class, 'store'])->middleware('throttle:5,60')->name('owner-messages.store');

    Route::get('check-in', [CheckInController::class, 'index'])->name('checkin.index');
    Route::post('check-in/{booking}', [CheckInController::class, 'process'])->name('checkin.process');
    Route::post('check-in/{booking}/confirm', [CheckInController::class, 'confirmIdentity'])->name('checkin.confirm')->middleware('throttle:10,60');
    Route::post('check-in/{booking}/reject', [CheckInController::class, 'rejectIdentity'])->name('checkin.reject')->middleware('throttle:10,60');

    Route::get('check-out', [CheckOutController::class, 'index'])->name('checkout.index');
    Route::post('check-out/bulk-approve', [CheckOutController::class, 'bulkApprove'])->name('checkout.bulk-approve')->middleware('throttle:5,60');
    Route::post('check-out/{penghuni}/request', [CheckOutController::class, 'requestCheckout'])->name('checkout.request');
    Route::post('check-out/{checkOut}/approve', [CheckOutController::class, 'approve'])->name('checkout.approve')->middleware('throttle:10,60');
    Route::post('check-out/{checkOut}/reject', [CheckOutController::class, 'reject'])->name('checkout.reject')->middleware('throttle:10,60');
    Route::post('check-out/{checkOut}/cancel', [CheckOutController::class, 'cancel'])->name('checkout.cancel')->middleware('throttle:10,60');

    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/export-pdf', [LaporanController::class, 'exportCombinedPdf'])->name('laporan.export-pdf');
    Route::get('laporan/export-excel', [LaporanController::class, 'exportCombinedExcel'])->name('laporan.export-excel');

    Route::get('moderation', [ModerationController::class, 'index'])->name('moderation.index');
    Route::get('moderation/kos', [ModerationController::class, 'kos'])->name('moderation.kos');
    Route::post('moderation/kos/{kos}/approve', [ModerationController::class, 'approveKos'])->name('moderation.kos.approve');
    Route::post('moderation/kos/{kos}/reject', [ModerationController::class, 'rejectKos'])->name('moderation.kos.reject');
    Route::get('moderation/photos', [ModerationController::class, 'photos'])->name('moderation.photos');
    Route::post('moderation/photos/{photo}/approve', [ModerationController::class, 'approvePhoto'])->name('moderation.photos.approve');
    Route::post('moderation/photos/{photo}/reject', [ModerationController::class, 'rejectPhoto'])->name('moderation.photos.reject');
    Route::get('moderation/advertising', [ModerationController::class, 'advertising'])->name('moderation.advertising');
    Route::get('moderation/damage-reports', [ModerationController::class, 'damageReports'])->name('moderation.damage-reports');

    Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

    Route::get('owners', [AdminOwnerController::class, 'index'])->name('owners.index');
    Route::get('owners/{owner}', [AdminOwnerController::class, 'show'])->name('owners.show');
});

// Tenant Routes
Route::middleware(['auth', 'role:tenant', 'billing'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::get('kos', [TenantKosController::class, 'index'])->name('kos.index');
    Route::get('kos/{kos}', [TenantKosController::class, 'show'])->name('kos.show');

    Route::get('booking', [TenantBookingController::class, 'index'])->name('booking.index');
    Route::get('booking/create', [TenantBookingController::class, 'create'])->name('booking.create');
    Route::post('booking', [TenantBookingController::class, 'store'])->name('booking.store');
    Route::get('booking/success/{booking}', [TenantBookingController::class, 'success'])->name('booking.success');
    Route::get('booking/{booking}', [TenantBookingController::class, 'show'])->name('booking.show');
    Route::post('booking/{booking}/cancel', [TenantBookingController::class, 'cancel'])->name('booking.cancel');
    // Pembayaran booking — prerequisite konfirmasi. Nominal dari server (booking.price).
    Route::post('booking/{booking}/payment', [TenantBookingPaymentController::class, 'store'])->name('booking.payment.store');

    Route::post('favorites/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    Route::get('checkin/{booking}/identity', [TenantCheckInController::class, 'show'])->name('checkin.identity');
    Route::post('checkin/{booking}/identity', [TenantCheckInController::class, 'submit'])->middleware('throttle:10,60')->name('checkin.identity.confirm');
    Route::get('checkin', [TenantCheckInController::class, 'index'])->name('checkin.index');

    Route::post('check-out/{penghuni}/request', [CheckOutController::class, 'requestCheckout'])->name('checkout.request');
    Route::post('check-out/{checkOut}/cancel', [CheckOutController::class, 'cancel'])->name('checkout.cancel');

    Route::get('kontrak', [TenantKontrakController::class, 'index'])->name('kontrak.index');
    Route::get('kontrak/{kontrak}', [TenantKontrakController::class, 'show'])->name('kontrak.show');

    Route::get('tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
    Route::get('tagihan/pay', [TagihanController::class, 'pay'])->name('tagihan.pay');
    Route::get('tagihan/{tagihan}', [TagihanController::class, 'show'])->name('tagihan.show');

    Route::get('messages', [TenantMessageController::class, 'index'])->name('messages.index');
    Route::post('messages', [TenantMessageController::class, 'store'])->middleware('throttle:5,60')->name('messages.store');
    Route::get('messages/latest', [TenantMessageController::class, 'latest'])->name('messages.latest');

    // Undangan tinggal bersama — orange modern
    Route::get('invitations', [TenantInvitationController::class, 'index'])->name('invitations.index');
    Route::get('invitations/{token}', [TenantInvitationController::class, 'show'])->where('token', '[a-f0-9]{64}')->name('invitations.show');
    Route::post('invitations/{token}/accept', [TenantInvitationController::class, 'accept'])->where('token', '[a-f0-9]{64}')->name('invitations.accept');
    Route::post('invitations/{token}/decline', [TenantInvitationController::class, 'decline'])->where('token', '[a-f0-9]{64}')->name('invitations.decline');
    Route::post('invitations/{occupant}/accept', [TenantInvitationController::class, 'acceptById'])->where('occupant', '[0-9]+')->name('invitations.acceptId');
    Route::post('invitations/{occupant}/decline', [TenantInvitationController::class, 'declineById'])->where('occupant', '[0-9]+')->name('invitations.declineId');
    Route::get('invitations/{token}/identity', [BookingOccupantIdentityController::class, 'show'])->where('token', '[a-f0-9]{64}')->name('invitations.identity');
    Route::post('invitations/{token}/identity', [BookingOccupantIdentityController::class, 'submit'])->where('token', '[a-f0-9]{64}')->name('invitations.identity.submit');

    // Booking occupants — kelola penghuni tambahan dalam 1 booking
    Route::get('booking/{booking}/occupants', [TenantBookingOccupantController::class, 'index'])->name('booking.occupants.index');
    Route::post('booking/{booking}/invite', [TenantBookingOccupantController::class, 'invite'])->name('booking.invite');
    Route::delete('booking/{booking}/occupants/{occupant}', [TenantBookingOccupantController::class, 'cancel'])->name('booking.occupants.cancel');

    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::get('pembayaran/{pembayaran}', [PembayaranController::class, 'show'])->name('pembayaran.show');
    Route::post('pembayaran', [TenantPembayaranController::class, 'store'])->name('pembayaran.store');

    Route::get('laporan', [TenantLaporanController::class, 'index'])->name('laporan.index');

    // Dispute (komplain)
    Route::get('dispute', [App\Http\Controllers\Tenant\DisputeController::class, 'index'])->name('dispute.index');
    Route::get('dispute/create', [App\Http\Controllers\Tenant\DisputeController::class, 'create'])->name('dispute.create');
    Route::post('dispute', [App\Http\Controllers\Tenant\DisputeController::class, 'store'])->name('dispute.store');
    Route::get('dispute/{dispute}', [App\Http\Controllers\Tenant\DisputeController::class, 'show'])->name('dispute.show');

    // Marketplace (jual barang bekas)
    Route::get('marketplace', [App\Http\Controllers\Tenant\MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::get('marketplace/create', [App\Http\Controllers\Tenant\MarketplaceController::class, 'create'])->name('marketplace.create');
    Route::post('marketplace', [App\Http\Controllers\Tenant\MarketplaceController::class, 'store'])->name('marketplace.store');
    Route::get('marketplace/my-items', [App\Http\Controllers\Tenant\MarketplaceController::class, 'myItems'])->name('marketplace.my-items');
    Route::get('marketplace/{item}', [App\Http\Controllers\Tenant\MarketplaceController::class, 'show'])->name('marketplace.show');
    Route::post('marketplace/{item}/buy', [App\Http\Controllers\Tenant\MarketplaceController::class, 'buy'])->name('marketplace.buy');
    Route::get('marketplace/{item}/chat', [App\Http\Controllers\Tenant\MarketplaceController::class, 'chat'])->name('marketplace.chat');
    Route::post('marketplace/{item}/chat', [App\Http\Controllers\Tenant\MarketplaceController::class, 'chatSend'])->name('marketplace.chat.send');
    Route::post('marketplace/transaction/{transaction}/complete', [App\Http\Controllers\Tenant\MarketplaceController::class, 'completeTransaction'])->name('marketplace.complete');
    Route::post('marketplace/transaction/{transaction}/review', [App\Http\Controllers\Tenant\MarketplaceController::class, 'storeReview'])->name('marketplace.review');
    Route::post('marketplace/{item}/report', [App\Http\Controllers\Tenant\MarketplaceController::class, 'report'])->name('marketplace.report');
    Route::delete('marketplace/{item}', [App\Http\Controllers\Tenant\MarketplaceController::class, 'destroy'])->name('marketplace.destroy');
});

// Tanya Owner — pencari kos (tenant login, tanpa perlu menghuni).
// Sengaja di luar grup 'billing' agar tenant overdue tetap dapat bertanya (pre-sales).
Route::middleware(['auth', 'role:tenant'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::get('kos/{kos}/tanya', [TenantMessageController::class, 'ask'])->name('kos.ask');
    Route::post('kos/{kos}/tanya', [TenantMessageController::class, 'askStore'])->middleware('throttle:5,60')->name('kos.ask.store');
    Route::get('inquiries', [TenantMessageController::class, 'inquiries'])->name('inquiries.index');
    Route::get('inquiries/{kos}', [TenantMessageController::class, 'inquiryShow'])->name('inquiries.show');
    Route::put('inquiries/{message}', [TenantMessageController::class, 'inquiryUpdate'])->name('inquiries.update');
});

require __DIR__.'/auth.php';
if (file_exists(__DIR__.'/damage-report.php')) {
    require __DIR__.'/damage-report.php';
}
