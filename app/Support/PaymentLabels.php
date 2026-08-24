<?php

namespace App\Support;

/**
 * Label & warna badge terpusat agar backend status teknis tidak pernah
 * ditampilkan mentah ke pengguna.
 */
class PaymentLabels
{
    /**
     * Label Indonesia untuk status tagihan.
     */
    public static function tagihanLabel(string $status): string
    {
        return match ($status) {
            'unpaid' => 'Belum Dibayar',
            'pending_verification' => 'Menunggu Verifikasi',
            'paid' => 'Lunas',
            'overdue' => 'Terlambat',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($status),
        };
    }

    /**
     * Kelas Tailwind badge untuk status tagihan.
     */
    public static function tagihanBadge(string $status): string
    {
        return match ($status) {
            'unpaid' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300',
            'pending_verification' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300',
            'paid' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
            'overdue' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-200 ring-1 ring-red-200 dark:ring-red-500/30',
            'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-slate-800 dark:text-slate-100',
            default => 'bg-gray-100 text-gray-800 dark:bg-slate-800 dark:text-slate-100',
        };
    }

    /**
     * Label Indonesia untuk status verifikasi pembayaran.
     */
    public static function verificationLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu Verifikasi',
            'approved' => 'Terverifikasi',
            'rejected' => 'Pembayaran Ditolak',
            default => ucfirst($status),
        };
    }

    /**
     * Kelas Tailwind badge untuk status verifikasi pembayaran.
     */
    public static function verificationBadge(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300',
            'approved' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-slate-800 dark:text-slate-100',
        };
    }

    /**
     * Label metode pembayaran dalam bahasa Indonesia.
     */
    public static function paymentMethod(string $method): string
    {
        return match ($method) {
            'transfer_bank' => 'Transfer Bank',
            'cash' => 'Tunai',
            'e_wallet' => 'QRIS / E-Wallet',
            default => ucfirst(str_replace('_', ' ', $method)),
        };
    }

    /**
     * Label tipe tagihan dalam bahasa Indonesia.
     */
    public static function billType(string $type): string
    {
        return match ($type) {
            'rent', 'sewa_kamar' => 'Sewa Kamar',
            'listrik' => 'Listrik',
            'air' => 'Air',
            'internet' => 'Internet',
            'kebersihan' => 'Kebersihan',
            'lainnya', 'other' => 'Lainnya',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }
}
