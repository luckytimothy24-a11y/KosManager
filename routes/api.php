<?php

use App\Http\Controllers\Api\V1\Admin\AdminDamageController;
use App\Http\Controllers\Api\V1\Admin\AdminMessageController;
use App\Http\Controllers\Api\V1\Admin\AdminOwnerMessageController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BillingController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\ContractController;
use App\Http\Controllers\Api\V1\DamageReportController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\InquiryController;
use App\Http\Controllers\Api\V1\KosController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\Owner\OwnerActionController;
use App\Http\Controllers\Api\V1\Owner\OwnerBankAccountController;
use App\Http\Controllers\Api\V1\Owner\OwnerBookingController;
use App\Http\Controllers\Api\V1\Owner\OwnerDashboardController;
use App\Http\Controllers\Api\V1\Owner\OwnerKontrakController;
use App\Http\Controllers\Api\V1\Owner\OwnerKosController;
use App\Http\Controllers\Api\V1\Owner\OwnerPaymentDistributionController;
use App\Http\Controllers\Api\V1\Owner\OwnerTagihanController;
use App\Http\Controllers\Api\V1\TenantBookingPaymentController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('api.v1.auth.login');
    Route::get('/health', HealthController::class)->name('api.v1.health');

    // Public — Marketplace (P7-D Batch 2)
    Route::get('/kos', [KosController::class, 'index'])->name('api.v1.kos.index');
    Route::get('/kos/{kos}', [KosController::class, 'show'])->name('api.v1.kos.show');
    Route::get('/kos/{kos}/kamar', [KosController::class, 'kamar'])->name('api.v1.kos.kamar');

    // Authenticated
    Route::middleware(['auth:sanctum', 'active'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::get('/me', [MeController::class, 'show'])->name('api.v1.me');
        Route::put('/me', [MeController::class, 'update'])->name('api.v1.me.update');
    });

    Route::middleware(['auth:sanctum', 'active', 'role:tenant'])->group(function () {
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('api.v1.favorites.index');
        Route::post('/favorites/{kos}/toggle', [FavoriteController::class, 'toggle'])->name('api.v1.favorites.toggle');

        Route::get('/bookings', [BookingController::class, 'index'])->name('api.v1.bookings.index');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('api.v1.bookings.show');
        Route::post('/bookings', [BookingController::class, 'store'])->name('api.v1.bookings.store');
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('api.v1.bookings.cancel');
        Route::post('/bookings/{booking}/payment', [TenantBookingPaymentController::class, 'store'])->name('api.v1.bookings.payment.store');

        Route::get('/tagihan', [BillingController::class, 'index'])->name('api.v1.tagihan.index');
        Route::get('/tagihan/{tagihan}', [BillingController::class, 'show'])->name('api.v1.tagihan.show');
        Route::post('/tagihan/{tagihan}/pembayaran', [BillingController::class, 'paymentStore'])->name('api.v1.tagihan.pembayaran.store');
        Route::get('/tagihan/{tagihan}/pembayaran', [BillingController::class, 'paymentShow'])->name('api.v1.tagihan.pembayaran.show');

        Route::get('/kontrak', [ContractController::class, 'index'])->name('api.v1.kontrak.index');
        Route::get('/kontrak/{kontrak}', [ContractController::class, 'show'])->name('api.v1.kontrak.show');
        Route::get('/kontrak/{kontrak}/tagihan', [ContractController::class, 'tagihan'])->name('api.v1.kontrak.tagihan');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('api.v1.notifications.index');
        Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('api.v1.notifications.show');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('api.v1.notifications.read');

        Route::post('/checkout/{penghuni}/request', [CheckoutController::class, 'request'])->name('api.v1.checkout.request');
        Route::post('/checkout/{checkout}/cancel', [CheckoutController::class, 'cancel'])->name('api.v1.checkout.cancel');

        Route::get('/inquiries', [InquiryController::class, 'index'])->name('api.v1.inquiries.index');
        Route::get('/inquiries/{kos}', [InquiryController::class, 'show'])->name('api.v1.inquiries.show');
        Route::put('/inquiries/{message}', [InquiryController::class, 'update'])->name('api.v1.inquiries.update');
        Route::post('/kos/{kos}/inquiry', [InquiryController::class, 'store'])
            ->middleware('throttle:5,60')
            ->name('api.v1.kos.inquiry.store');

        Route::get('/damage-reports', [DamageReportController::class, 'index'])->name('api.v1.damage-reports.index');
        Route::post('/damage-reports', [DamageReportController::class, 'store'])->name('api.v1.damage-reports.store');
        Route::get('/damage-reports/{damageReport}', [DamageReportController::class, 'show'])->name('api.v1.damage-reports.show');
        Route::delete('/damage-reports/{damageReport}', [DamageReportController::class, 'destroy'])->name('api.v1.damage-reports.destroy');
    });

    Route::middleware(['auth:sanctum', 'active', 'role:owner'])->group(function () {
        Route::get('/owner/kos', [OwnerKosController::class, 'index'])->name('api.v1.owner.kos.index');
        Route::get('/owner/kos/{kos}', [OwnerKosController::class, 'show'])->name('api.v1.owner.kos.show');
        Route::get('/owner/kos/{kos}/kamar', [OwnerKosController::class, 'kamar'])->name('api.v1.owner.kos.kamar');
        Route::get('/owner/dashboard', [OwnerDashboardController::class, 'show'])->name('api.v1.owner.dashboard.show');
        Route::get('/owner/bookings', [OwnerBookingController::class, 'index'])->name('api.v1.owner.bookings.index');
        Route::get('/owner/bookings/{booking}', [OwnerBookingController::class, 'show'])->name('api.v1.owner.bookings.show');
        Route::post('/owner/bookings/{booking}/approve', [OwnerBookingController::class, 'approve'])->name('api.v1.owner.bookings.approve');
        Route::post('/owner/bookings/{booking}/reject', [OwnerBookingController::class, 'reject'])->name('api.v1.owner.bookings.reject');
        Route::post('/owner/booking-cancellation/{cancellationRequest}/approve', [OwnerBookingController::class, 'approveCancellation'])->name('api.v1.owner.booking-cancellation.approve');
        Route::post('/owner/booking-cancellation/{cancellationRequest}/reject', [OwnerBookingController::class, 'rejectCancellation'])->name('api.v1.owner.booking-cancellation.reject');
        Route::get('/owner/bank-account', [OwnerBankAccountController::class, 'show'])->name('api.v1.owner.bank-account.show');
        Route::put('/owner/bank-account', [OwnerBankAccountController::class, 'update'])->name('api.v1.owner.bank-account.update');
        Route::get('/owner/distributions', [OwnerPaymentDistributionController::class, 'index'])->name('api.v1.owner.distributions.index');
        Route::post('/owner/distributions/{distribution}/mark-processed', [OwnerPaymentDistributionController::class, 'markProcessed'])->name('api.v1.owner.distributions.mark-processed');
        Route::get('/owner/kontrak', [OwnerKontrakController::class, 'index'])->name('api.v1.owner.kontrak.index');
        Route::get('/owner/kontrak/{kontrak}', [OwnerKontrakController::class, 'show'])->name('api.v1.owner.kontrak.show');
        Route::get('/owner/kontrak/{kontrak}/tagihan', [OwnerKontrakController::class, 'tagihan'])->name('api.v1.owner.kontrak.tagihan');
        Route::get('/owner/tagihan/{tagihan}/pembayaran', [OwnerTagihanController::class, 'pembayaran'])->name('api.v1.owner.tagihan.pembayaran.index');
        Route::post('/owner/pembayaran/{pembayaran}/verify', [OwnerActionController::class, 'verifyPayment'])->name('api.v1.owner.pembayaran.verify');
        Route::post('/owner/pembayaran/{pembayaran}/reject', [OwnerActionController::class, 'rejectPayment'])->name('api.v1.owner.pembayaran.reject');
        Route::post('/owner/checkout/{checkout}/approve', [OwnerActionController::class, 'approveCheckout'])->name('api.v1.owner.checkout.approve');
        Route::post('/owner/checkout/{checkout}/cancel', [OwnerActionController::class, 'cancelCheckout'])->name('api.v1.owner.checkout.cancel');
        Route::post('/owner/checkout/bulk-approve', [OwnerActionController::class, 'bulkApproveCheckout'])->name('api.v1.owner.checkout.bulk-approve');
        Route::post('/owner/messages/{tenant}/reply', [OwnerActionController::class, 'replyMessage'])->name('api.v1.owner.messages.reply');
    });

    Route::middleware(['auth:sanctum', 'active', 'role:admin'])->group(function () {
        Route::get('/admin/messages', [AdminMessageController::class, 'index'])->name('api.v1.admin.messages.index');
        Route::get('/admin/messages/{tenant}', [AdminMessageController::class, 'show'])->name('api.v1.admin.messages.show');
        Route::get('/admin/owner-contacts', [AdminOwnerMessageController::class, 'index'])->name('api.v1.admin.owner-contacts.index');
        Route::get('/admin/owner-messages/{kos}/{owner}', [AdminOwnerMessageController::class, 'show'])->name('api.v1.admin.owner-messages.show');
        Route::post('/admin/owner-messages/{kos}/{owner}', [AdminOwnerMessageController::class, 'store'])->middleware('throttle:5,60')->name('api.v1.admin.owner-messages.store');
        Route::get('/admin/damage-reports', [AdminDamageController::class, 'index'])->name('api.v1.admin.damage-reports.index');
        Route::get('/admin/damage-reports/{damageReport}', [AdminDamageController::class, 'show'])->name('api.v1.admin.damage-reports.show');
    });
});
