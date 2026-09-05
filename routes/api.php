<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BillingController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\ContractController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\KosController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\Owner\OwnerBookingController;
use App\Http\Controllers\Api\V1\Owner\OwnerDashboardController;
use App\Http\Controllers\Api\V1\Owner\OwnerKontrakController;
use App\Http\Controllers\Api\V1\Owner\OwnerKosController;
use App\Http\Controllers\Api\V1\Owner\OwnerTagihanController;
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
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::get('/me', [MeController::class, 'show'])->name('api.v1.me');
    });

    // Authenticated — Favorites (P7-D Batch 2)
    // Middleware auth:sanctum + role:tenant dikelola di FavoriteController::__construct
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('api.v1.favorites.index');
    Route::post('/favorites/{kos}/toggle', [FavoriteController::class, 'toggle'])->name('api.v1.favorites.toggle');

    // Authenticated — Tenant Bookings (P7-D Batch 3)
    // Middleware auth:sanctum + role:tenant dikelola di BookingController::__construct
    Route::get('/bookings', [BookingController::class, 'index'])->name('api.v1.bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('api.v1.bookings.show');
    Route::post('/bookings', [BookingController::class, 'store'])->name('api.v1.bookings.store');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('api.v1.bookings.cancel');

    // Authenticated — Tenant Billing & Payment (P7-D Batch 4)
    // Middleware auth:sanctum + role:tenant dikelola di BillingController::__construct
    Route::get('/tagihan', [BillingController::class, 'index'])->name('api.v1.tagihan.index');
    Route::get('/tagihan/{tagihan}', [BillingController::class, 'show'])->name('api.v1.tagihan.show');
    Route::post('/tagihan/{tagihan}/pembayaran', [BillingController::class, 'paymentStore'])->name('api.v1.tagihan.pembayaran.store');
    Route::get('/tagihan/{tagihan}/pembayaran', [BillingController::class, 'paymentShow'])->name('api.v1.tagihan.pembayaran.show');

    // Authenticated — Tenant Contracts (P7-D Batch 5)
    // Middleware auth:sanctum + role:tenant dikelola di ContractController::__construct
    Route::get('/kontrak', [ContractController::class, 'index'])->name('api.v1.kontrak.index');
    Route::get('/kontrak/{kontrak}', [ContractController::class, 'show'])->name('api.v1.kontrak.show');
    Route::get('/kontrak/{kontrak}/tagihan', [ContractController::class, 'tagihan'])->name('api.v1.kontrak.tagihan');

    // Authenticated — Tenant Notifications (P7-D Batch 6)
    // Middleware auth:sanctum + role:tenant dikelola di NotificationController::__construct
    Route::get('/notifications', [NotificationController::class, 'index'])->name('api.v1.notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('api.v1.notifications.show');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('api.v1.notifications.read');

    // Authenticated — Owner Property & Dashboard (P7-D Batch 7)
    // Middleware auth:sanctum + role:owner dikelola di Owner controllers
    Route::get('/owner/kos', [OwnerKosController::class, 'index'])->name('api.v1.owner.kos.index');
    Route::get('/owner/kos/{kos}', [OwnerKosController::class, 'show'])->name('api.v1.owner.kos.show');
    Route::get('/owner/kos/{kos}/kamar', [OwnerKosController::class, 'kamar'])->name('api.v1.owner.kos.kamar');
    Route::get('/owner/dashboard', [OwnerDashboardController::class, 'show'])->name('api.v1.owner.dashboard.show');

    // Authenticated — Owner Bookings (P7-D Batch 8)
    // Middleware auth:sanctum + role:owner dikelola di OwnerBookingController::__construct
    Route::get('/owner/bookings', [OwnerBookingController::class, 'index'])->name('api.v1.owner.bookings.index');
    Route::get('/owner/bookings/{booking}', [OwnerBookingController::class, 'show'])->name('api.v1.owner.bookings.show');

    // Authenticated — Owner Contracts & Billing (P7-D Batch 9)
    // Middleware auth:sanctum + role:owner dikelola di OwnerKontrakController::__construct
    Route::get('/owner/kontrak', [OwnerKontrakController::class, 'index'])->name('api.v1.owner.kontrak.index');
    Route::get('/owner/kontrak/{kontrak}', [OwnerKontrakController::class, 'show'])->name('api.v1.owner.kontrak.show');
    Route::get('/owner/kontrak/{kontrak}/tagihan', [OwnerKontrakController::class, 'tagihan'])->name('api.v1.owner.kontrak.tagihan');

    // Authenticated — Owner Billing & Payment (P7-D Batch 10)
    // Middleware auth:sanctum + role:owner dikelola di OwnerTagihanController::__construct
    Route::get('/owner/tagihan/{tagihan}/pembayaran', [OwnerTagihanController::class, 'pembayaran'])->name('api.v1.owner.tagihan.pembayaran.index');
});
