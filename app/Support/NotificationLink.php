<?php

namespace App\Support;

use App\Models\Notification;
use App\Models\User;

/**
 * Menentukan halaman tujuan sebuah notifikasi.
 *
 * Prioritas: data.url yang tersimpan (sudah spesifik saat notifikasi dibuat),
 * lalu fallback berdasarkan tipe DAN role penerima. Fallback tidak pernah
 * mengarah ke route role lain — owner/admin tidak boleh jatuh ke halaman tenant.
 */
class NotificationLink
{
    public static function resolve(Notification $notification, ?User $user = null): ?string
    {
        $stored = $notification->data['url'] ?? null;
        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        $user ??= $notification->user;
        if (! $user) {
            return null;
        }

        $route = self::fallbackRoute($notification->type, $user);

        return $route && \Route::has($route) ? route($route) : null;
    }

    private static function fallbackRoute(string $type, User $user): ?string
    {
        $prefix = match (true) {
            $user->isTenant() => 'tenant',
            $user->isOwner() || $user->isSuperAdmin() => 'owner',
            $user->isAdmin() => 'admin',
            default => null,
        };

        if ($prefix === null) {
            return null;
        }

        return match ($type) {
            NotificationType::BOOKING => "{$prefix}.booking.index",
            NotificationType::PAYMENT => "{$prefix}.pembayaran.index",
            NotificationType::BILLING => "{$prefix}.tagihan.index",
            NotificationType::KONTRAK => "{$prefix}.kontrak.index",
            NotificationType::MESSAGE => "{$prefix}.messages.index",
            NotificationType::CHECKIN => $prefix === 'tenant' ? 'tenant.checkin.index' : "{$prefix}.checkin.index",
            NotificationType::CHECKOUT => $prefix === 'tenant' ? 'dashboard' : "{$prefix}.checkout.index",
            NotificationType::DAMAGE_REPORT => "{$prefix}.damage-reports.index",
            NotificationType::ADVERTISING => match ($prefix) {
                'tenant' => null,
                'admin' => 'admin.advertising.index',
                default => 'owner.advertising.dashboard',
            },
            NotificationType::IDENTITY => $prefix === 'tenant' ? 'tenant.checkin.index' : "{$prefix}.checkin.index",
            default => null,
        };
    }
}
