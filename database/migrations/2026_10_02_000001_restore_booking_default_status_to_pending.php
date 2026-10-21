<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Business rules: booking SELALU dibuat sebagai pending.
        // Owner/admin harus approve/reject secara eksplisit.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending','approved','rejected','cancelled','completed','expired') NOT NULL DEFAULT 'pending'");
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite tidak menghormati DEFAULT enum secara ketat; cukup pastikan tidak ada override berbahaya.
            // Default aplikasi sudah di-set eksplisit di BookingService.
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending','approved','rejected','cancelled','completed','expired') NOT NULL DEFAULT 'approved'");
        }
    }
};
