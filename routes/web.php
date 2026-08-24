<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Owner\BookingController;
use App\Http\Controllers\Owner\CheckInController;
use App\Http\Controllers\Owner\CheckOutController;
use App\Http\Controllers\Owner\KamarController;
use App\Http\Controllers\Owner\KontrakController;
use App\Http\Controllers\Owner\KosController;
use App\Http\Controllers\Owner\PembayaranController;
use App\Http\Controllers\Owner\PenghuniController;
use App\Http\Controllers\Owner\TagihanController;
use App\Http\Controllers\Owner\TenantBookingController;
use App\Http\Controllers\Owner\TenantKosController;
use App\Http\Controllers\Owner\TenantPembayaranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\FasilitasController;
use App\Http\Controllers\SuperAdmin\LaporanController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Models\AuditLog;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $q = trim((string) $request->query('q', ''));

    $kosQuery = Kos::where('status', 'active')
        ->withCount(['kamar as available_rooms' => fn ($qr) => $qr->where('status', 'available')])
        ->withMin('kamar as min_price', 'monthly_price')
        ->orderByDesc('available_rooms');

    if ($q !== '') {
        $kosQuery->where(function ($w) use ($q) {
            $w->where('name', 'like', "%{$q}%")
                ->orWhere('address', 'like', "%{$q}%");
        });
    }

    $featuredKos = $kosQuery->limit(6)->get();

    $stats = [
        'kos' => Kos::where('status', 'active')->count(),
        'kamar' => Kamar::where('status', 'available')->count(),
        'owners' => User::where('role', 'owner')->count(),
    ];

    return view('welcome', compact('featuredKos', 'stats', 'q'));
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
        ->name('notifications.markAllRead');

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

    Route::get('audit-log', function () {
        $logs = AuditLog::with('user')->latest()->paginate(20);

        return view('super-admin.audit-log.index', compact('logs'));
    })->name('audit-log.index');
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
    Route::post('booking/{booking}/cancel', [TenantBookingController::class, 'cancel'])->name('booking.cancel');

    Route::post('check-out/{penghuni}/request', [CheckOutController::class, 'requestCheckout'])->name('checkout.request');

    Route::get('tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
    Route::get('tagihan/{tagihan}', [TagihanController::class, 'show'])->name('tagihan.show');

    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::get('pembayaran/{pembayaran}', [PembayaranController::class, 'show'])->name('pembayaran.show');
    Route::post('pembayaran', [TenantPembayaranController::class, 'store'])->name('pembayaran.store');
});

require __DIR__.'/auth.php';
