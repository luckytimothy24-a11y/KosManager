<?php

namespace App\Support;

/**
 * Label Indonesia & kelas badge untuk domain advertising (paket, campaign,
 * order) agar nilai enum database tidak pernah tampil mentah ke pengguna.
 */
class AdvertisingLabels
{
    public static function campaignLabel(string $status): string
    {
        return match ($status) {
            'draft' => 'Draf',
            'pending_payment' => 'Menunggu Pembayaran',
            'paid' => 'Dibayar',
            'pending_review' => 'Menunggu Review',
            'approved' => 'Disetujui',
            'active' => 'Aktif',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
            'suspended' => 'Ditangguhkan',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($status),
        };
    }

    public static function campaignBadge(string $status): string
    {
        return match ($status) {
            'draft' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            'pending_payment', 'pending_review' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300',
            'paid', 'approved', 'active' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
            'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300',
            'rejected', 'suspended', 'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100',
        };
    }

    public static function orderLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu',
            'paid' => 'Dibayar',
            'refunded' => 'Dikembalikan',
            default => ucfirst($status),
        };
    }

    public static function orderBadge(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300',
            'paid' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
            'refunded' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100',
        };
    }

    public static function placementLabel(?string $placement): string
    {
        return match ($placement) {
            'homepage' => 'Homepage',
            'marketplace' => 'Marketplace',
            'detail' => 'Detail Kos',
            'native' => 'Native',
            'tenant_dashboard' => 'Dashboard Tenant',
            'featured' => 'Featured',
            'sponsored' => 'Sponsored',
            null, '' => '-',
            default => ucfirst($placement),
        };
    }

    public static function placementBadge(?string $placement): string
    {
        return match ($placement) {
            'homepage' => 'bg-primary-100 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300',
            'marketplace' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300',
            'detail' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
            'native' => 'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/10 dark:text-fuchsia-300',
            'tenant_dashboard' => 'bg-teal-100 text-teal-700 dark:bg-teal-500/10 dark:text-teal-300',
            'featured' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
            'sponsored' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300',
            default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
        };
    }

    public static function partnerStatusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            default => ucfirst($status),
        };
    }

    public static function partnerStatusBadge(string $status): string
    {
        return match ($status) {
            'active' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
            'inactive' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100',
        };
    }

    public static function monetizationLabel(?string $type): string
    {
        return match ($type) {
            'cpm' => 'CPM (per impressi)',
            'cpc' => 'CPC (per klik)',
            'deal' => 'Kesepakatan paket',
            'contract' => 'Kontrak',
            null, '' => '-',
            default => ucfirst($type),
        };
    }
}
