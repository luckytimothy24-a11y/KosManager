<?php

namespace App\Support;

class NotificationType
{
    public const BOOKING = 'booking';

    public const KONTRAK = 'kontrak';

    public const PAYMENT = 'payment';

    public const BILLING = 'billing';

    public const CHECKIN = 'checkin';

    public const CHECKOUT = 'checkout';

    public const ADVERTISING = 'advertising';

    public const MESSAGE = 'message';

    public const IDENTITY = 'identity';

    public const DAMAGE_REPORT = 'damage_report';

    public const KOS_MODERATION = 'kos_moderation';

    public static function label(string $type): string
    {
        return match ($type) {
            self::BOOKING => 'Booking',
            self::KONTRAK => 'Kontrak',
            self::PAYMENT => 'Pembayaran',
            self::BILLING => 'Tagihan',
            self::CHECKIN => 'Check-In',
            self::CHECKOUT => 'Check-Out',
            self::ADVERTISING => 'Advertising',
            self::MESSAGE => 'Pesan',
            self::IDENTITY => 'Identitas',
            self::DAMAGE_REPORT => 'Lapor Kerusakan',
            self::KOS_MODERATION => 'Moderasi Kos',
            default => 'Lainnya',
        };
    }

    public static function all(): array
    {
        return [
            self::BOOKING => self::label(self::BOOKING),
            self::KONTRAK => self::label(self::KONTRAK),
            self::PAYMENT => self::label(self::PAYMENT),
            self::BILLING => self::label(self::BILLING),
            self::CHECKIN => self::label(self::CHECKIN),
            self::CHECKOUT => self::label(self::CHECKOUT),
            self::ADVERTISING => self::label(self::ADVERTISING),
            self::MESSAGE => self::label(self::MESSAGE),
            self::IDENTITY => self::label(self::IDENTITY),
            self::DAMAGE_REPORT => self::label(self::DAMAGE_REPORT),
            self::KOS_MODERATION => self::label(self::KOS_MODERATION),
        ];
    }
}
