<?php

namespace App\Support;

/**
 * Label Indonesia & kelas badge terpusat untuk seluruh status
 * di luar domain pembayaran (booking, kamar, kontrak, dll) agar
 * nilai enum database tidak pernah tampil mentah ke pengguna.
 */
class StatusLabels
{
    /**
     * Label status booking.
     */
    public static function bookingLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'completed' => 'Selesai',
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
            'approved' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
            'rejected', 'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300',
            'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300',
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
}
