<?php

use App\Http\Controllers\Owner\DamageReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:tenant', 'billing'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::get('damage-reports', [DamageReportController::class, 'index'])->name('damage-reports.index');
    Route::get('damage-reports/create', [DamageReportController::class, 'create'])->name('damage-reports.create');
    Route::post('damage-reports', [DamageReportController::class, 'store'])->name('damage-reports.store');
    Route::get('damage-reports/{report}', [DamageReportController::class, 'show'])->name('damage-reports.show');
    Route::delete('damage-reports/{report}', [DamageReportController::class, 'destroy'])->name('damage-reports.destroy');
});

Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('damage-reports', [DamageReportController::class, 'index'])->name('damage-reports.index');
    Route::get('damage-reports/create', [DamageReportController::class, 'createOwner'])->name('damage-reports.create');
    Route::post('damage-reports', [DamageReportController::class, 'storeOwner'])->name('damage-reports.store');
    Route::get('damage-reports/{report}', [DamageReportController::class, 'show'])->name('damage-reports.show');
    Route::put('damage-reports/{report}', [DamageReportController::class, 'update'])->name('damage-reports.update');
});

Route::middleware(['auth', 'role:super_admin,owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('damage-reports', [DamageReportController::class, 'index'])->name('damage-reports.index');
    Route::get('damage-reports/create', [DamageReportController::class, 'createOwner'])->name('damage-reports.create');
    Route::post('damage-reports', [DamageReportController::class, 'storeOwner'])->name('damage-reports.store');
    Route::get('damage-reports/{report}', [DamageReportController::class, 'show'])->name('damage-reports.show');
    Route::put('damage-reports/{report}', [DamageReportController::class, 'update'])->name('damage-reports.update');
});
