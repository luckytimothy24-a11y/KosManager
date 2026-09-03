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

    public static function label(string $type): string
    {
        return match ($type) {
            self::BOOKING => 'Booking',
            self::KONTRAK => 'Kontrak',
            self::PAYMENT => 'Pembayaran',
            self::BILLING => 'Tagihan',
            self::CHECKIN => 'Check-In',
            self::CHECKOUT => 'Check-Out',
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
        ];
    }
}
