<?php

namespace App\Services;

use App\Mail\KosManagerMail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public static function create(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?array $data = null
    ): Notification {
        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        $user = User::find($userId);

        if ($user) {
            Mail::to($user->email)->queue(
                new KosManagerMail($title, $message, url('/dashboard'))
            );
        }

        return $notification;
    }

    public static function bookingNew(int $ownerId, string $tenantName, string $roomNumber): void
    {
        self::create($ownerId, 'booking', 'Booking Baru', "{$tenantName} melakukan booking untuk kamar {$roomNumber}");
    }

    public static function bookingApproved(int $tenantId, string $bookingCode): void
    {
        self::create($tenantId, 'booking', 'Booking Disetujui', "Booking {$bookingCode} telah disetujui.");
    }

    public static function bookingRejected(int $tenantId, string $bookingCode): void
    {
        self::create($tenantId, 'booking', 'Booking Ditolak', "Booking {$bookingCode} telah ditolak.");
    }

    public static function bookingExpired(int $tenantId, string $bookingCode): void
    {
        self::create($tenantId, 'booking', 'Booking Expired', "Booking {$bookingCode} telah kedaluwarsa karena tidak dikonfirmasi.");
    }

    public static function kontrakActive(int $tenantId, string $contractNumber): void
    {
        self::create($tenantId, 'kontrak', 'Kontrak Aktif', "Kontrak sewa {$contractNumber} kini berstatus aktif.");
    }

    public static function paymentSubmitted(int $ownerId, string $tenantName, string $billNumber): void
    {
        self::create($ownerId, 'payment', 'Pembayaran Baru', "{$tenantName} telah mengirim bukti pembayaran untuk tagihan {$billNumber}");
    }

    public static function paymentVerified(int $tenantId, string $billNumber): void
    {
        self::create($tenantId, 'payment', 'Pembayaran Diverifikasi', "Pembayaran untuk tagihan {$billNumber} telah diverifikasi.");
    }

    public static function paymentRejected(int $tenantId, string $billNumber): void
    {
        self::create($tenantId, 'payment', 'Pembayaran Ditolak', "Pembayaran untuk tagihan {$billNumber} telah ditolak. Silakan ajukan ulang.");
    }

    public static function billCreated(int $tenantId, string $billNumber, string $dueDate): void
    {
        self::create($tenantId, 'billing', 'Tagihan Baru', "Tagihan {$billNumber} telah dibuat. Jatuh tempo: {$dueDate}");
    }

    public static function billDueSoon(int $tenantId, string $billNumber, string $dueDate): void
    {
        self::create($tenantId, 'billing', 'Pengingat Jatuh Tempo', "Tagihan {$billNumber} akan jatuh tempo pada {$dueDate}. Segera lakukan pembayaran.");
    }

    public static function billOverdue(int $tenantId, string $billNumber): void
    {
        self::create($tenantId, 'billing', 'Tagihan Overdue', "Tagihan {$billNumber} telah melewati jatuh tempo!");
    }

    public static function checkin(int $tenantId, string $roomNumber): void
    {
        self::create($tenantId, 'checkin', 'Check-In Berhasil', "Anda telah berhasil check-in di kamar {$roomNumber}");
    }

    public static function checkoutRequested(int $ownerId, string $tenantName, string $roomNumber): void
    {
        self::create($ownerId, 'checkout', 'Pengajuan Check-Out', "{$tenantName} mengajukan check-out dari kamar {$roomNumber}");
    }

    public static function checkoutApproved(int $tenantId, string $roomNumber): void
    {
        self::create($tenantId, 'checkout', 'Check-Out Disetujui', "Pengajuan check-out dari kamar {$roomNumber} telah disetujui.");
    }
}
