<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Label Indonesia & kelas badge terpusat untuk seluruh status
 * di luar domain pembayaran (booking, kamar, kontrak, dll) agar
 * nilai enum database tidak pernah tampil mentah ke pengguna.
 */
class StatusLabels
{
    /**
     * Label status booking.
     * `approved` = confirmed (booking terkonfirmasi owner/admin).
     */
    public static function bookingLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu Persetujuan',
            'approved', 'confirmed' => 'Terkonfirmasi',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'completed' => 'Selesai',
            'expired' => 'Kedaluwarsa',
            default => ucfirst($status),
        };
    }

    /**
     * Kelas badge status booking.
     */
    public static function bookingBadge(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300',
            'approved', 'confirmed' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
            'rejected', 'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300',
            'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300',
            'expired' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100',
        };
    }

    /**
     * Label status kamar.
     */
    public static function kamarLabel(string $status): string
    {
        return match ($status) {
            'available' => 'Tersedia',
            'booked' => 'Di-Booking',
            'occupied' => 'Terisi',
            'maintenance' => 'Maintenance',
            default => ucfirst($status),
        };
    }

    /**
     * Kelas badge status kamar.
     */
    public static function kamarBadge(string $status): string
    {
        return match ($status) {
            'available' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
            'booked' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300',
            'occupied' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300',
            'maintenance' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100',
        };
    }

    /**
     * Label status kos.
     */
    public static function kosLabel(string $status): string
    {
        return match ($status) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            default => ucfirst($status),
        };
    }

    /**
     * Kelas badge status kos.
     */
    public static function kosBadge(string $status): string
    {
        return match ($status) {
            'active' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
            'inactive' => 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100',
        };
    }

    /**
     * Label status kontrak.
     */
    public static function kontrakLabel(string $status): string
    {
        return match ($status) {
            'active' => 'Aktif',
            'expired' => 'Berakhir',
            'terminated' => 'Dihentikan',
            'completed' => 'Selesai',
            default => ucfirst($status),
        };
    }

    /**
     * Kelas badge status kontrak.
     */
    public static function kontrakBadge(string $status): string
    {
        return match ($status) {
            'active' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
            'expired' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300',
            'terminated' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300',
            'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100',
        };
    }

    /**
     * Label status penghuni.
     */
    public static function penghuniLabel(string $status): string
    {
        return match ($status) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            default => ucfirst($status),
        };
    }

    /**
     * Kelas badge status penghuni.
     */
    public static function penghuniBadge(string $status): string
    {
        return match ($status) {
            'active' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
            'inactive' => 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100',
        };
    }

    /**
     * Label status check-out.
     */
    public static function checkoutLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'completed' => 'Selesai',
            default => ucfirst($status),
        };
    }

    /**
     * Kelas badge status check-out.
     */
    public static function checkoutBadge(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300',
            'approved', 'completed' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100',
        };
    }

    /**
     * Label role user.
     */
    public static function roleLabel(?string $role): string
    {
        return match ($role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'owner' => 'Owner',
            'tenant' => 'Tenant',
            null, '' => '-',
            default => ucfirst(str_replace('_', ' ', $role)),
        };
    }

    /**
     * Kelas badge role user.
     */
    public static function roleBadge(?string $role): string
    {
        return match ($role) {
            'super_admin' => 'bg-purple-100 text-purple-800 dark:bg-purple-500/10 dark:text-purple-300',
            'admin' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300',
            'owner' => 'bg-primary-100 text-primary-800 dark:bg-primary-500/10 dark:text-primary-300',
            'tenant' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100',
        };
    }

    /**
     * Label mode penagihan kamar.
     */
    public static function billingModeLabel(?string $mode): string
    {
        return match ($mode) {
            'personal', null, '' => 'Per Orang',
            'shared' => 'Patungan',
            default => ucfirst($mode),
        };
    }

    /**
     * Kelas badge mode penagihan kamar.
     */
    public static function billingModeBadge(?string $mode): string
    {
        return match ($mode) {
            'personal', null, '' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300',
            'shared' => 'bg-primary-100 text-primary-800 dark:bg-primary-500/10 dark:text-primary-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100',
        };
    }

    /**
     * Label tipe sewa (booking/kontrak).
     */
    public static function rentalTypeLabel(?string $type): string
    {
        return match ($type) {
            'monthly' => 'Bulanan',
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'yearly' => 'Tahunan',
            null, '' => '-',
            default => ucfirst($type),
        };
    }

    /**
     * Default tanggal mulai form booking (hanya default UI — tenant dapat mengubah).
     * monthly → bulan depan; weekly → +7 hari; daily → hari ini.
     */
    public static function defaultBookingStartDate(string $rentalType = 'monthly'): string
    {
        return match ($rentalType) {
            'weekly' => now()->addDays(7)->toDateString(),
            'daily' => now()->toDateString(),
            default => now()->addMonthNoOverflow()->toDateString(),
        };
    }

    /**
     * Default tanggal selesai form booking berdasarkan start + rental type.
     */
    public static function defaultBookingEndDate(string $startDate, string $rentalType = 'monthly'): string
    {
        $start = Carbon::parse($startDate);

        return match ($rentalType) {
            'weekly' => $start->copy()->addDays(7)->toDateString(),
            'daily' => $start->copy()->addDay()->toDateString(),
            default => $start->copy()->addMonthNoOverflow()->toDateString(),
        };
    }
}
