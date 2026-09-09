<?php

namespace App\Services;

use App\Mail\KosManagerMail;
use App\Models\Notification;
use App\Models\User;
use App\Support\NotificationType;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public static function create(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $uniqueKey = null
    ): Notification {
        if ($uniqueKey !== null) {
            $data = array_merge($data ?? [], ['unique_key' => $uniqueKey]);

            $duplicate = Notification::where('user_id', $userId)
                ->where('type', $type)
                ->where('created_at', '>=', now()->subDay())
                ->latest()
                ->get()
                ->first(fn (Notification $n) => ($n->data['unique_key'] ?? null) === $uniqueKey);

            if ($duplicate) {
                return $duplicate;
            }
        }

        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        $user = User::find($userId);

        if ($user) {
            try {
                Mail::to($user->email)->queue(
                    new KosManagerMail($title, $message, url('/dashboard'))
                );
            } catch (\Exception $e) {
                \Log::warning('Failed to send notification email: '.$e->getMessage());
            }
        }

        return $notification;
    }

    public static function bookingNew(int $ownerId, string $tenantName, string $roomNumber): void
    {
        self::create($ownerId, NotificationType::BOOKING, 'Booking Baru', "{$tenantName} melakukan booking untuk kamar {$roomNumber}");
    }

    public static function bookingInstantConfirmation(int $tenantId, string $bookingCode, string $roomNumber): void
    {
        self::create($tenantId, NotificationType::BOOKING, 'Booking Berhasil', "Booking {$bookingCode} untuk kamar {$roomNumber} telah terkonfirmasi. Silakan lakukan check-in sesuai jadwal.");
    }

    public static function bookingExpired(int $tenantId, string $bookingCode, ?string $uniqueKey = null): void
    {
        self::create($tenantId, NotificationType::BOOKING, 'Booking Kedaluwarsa', "Booking {$bookingCode} telah kedaluwarsa karena tidak melakukan check-in tepat waktu.", null, $uniqueKey);
    }

    public static function kontrakActive(int $tenantId, string $contractNumber): void
    {
        self::create($tenantId, NotificationType::KONTRAK, 'Kontrak Aktif', "Kontrak sewa {$contractNumber} kini berstatus aktif.");
    }

    public static function paymentSubmitted(int $ownerId, string $tenantName, string $billNumber): void
    {
        self::create($ownerId, NotificationType::PAYMENT, 'Pembayaran Baru', "{$tenantName} telah mengirim bukti pembayaran untuk tagihan {$billNumber}");
    }

    public static function paymentVerified(int $tenantId, string $billNumber): void
    {
        self::create($tenantId, NotificationType::PAYMENT, 'Pembayaran Diverifikasi', "Pembayaran untuk tagihan {$billNumber} telah diverifikasi.");
    }

    public static function paymentRejected(int $tenantId, string $billNumber): void
    {
        self::create($tenantId, NotificationType::PAYMENT, 'Pembayaran Ditolak', "Pembayaran untuk tagihan {$billNumber} telah ditolak. Silakan ajukan ulang.");
    }

    public static function billCreated(int $tenantId, string $billNumber, string $dueDate): void
    {
        self::create($tenantId, NotificationType::BILLING, 'Tagihan Baru', "Tagihan {$billNumber} telah dibuat. Jatuh tempo: {$dueDate}");
    }

    public static function billDueSoon(int $tenantId, string $billNumber, string $dueDate, ?string $uniqueKey = null): void
    {
        self::create($tenantId, NotificationType::BILLING, 'Pengingat Jatuh Tempo', "Tagihan {$billNumber} akan jatuh tempo pada {$dueDate}. Segera lakukan pembayaran.", null, $uniqueKey);
    }

    public static function billOverdue(int $tenantId, string $billNumber, ?string $uniqueKey = null): void
    {
        self::create($tenantId, NotificationType::BILLING, 'Tagihan Overdue', "Tagihan {$billNumber} telah melewati jatuh tempo!", null, $uniqueKey);
    }

    public static function checkin(int $tenantId, string $roomNumber): void
    {
        self::create($tenantId, NotificationType::CHECKIN, 'Check-In Berhasil', "Anda telah berhasil check-in di kamar {$roomNumber}");
    }

    public static function checkoutRequested(int $ownerId, string $tenantName, string $roomNumber): void
    {
        self::create($ownerId, NotificationType::CHECKOUT, 'Pengajuan Check-Out', "{$tenantName} mengajukan check-out dari kamar {$roomNumber}");
    }

    public static function checkoutApproved(int $tenantId, string $roomNumber, ?string $uniqueKey = null): void
    {
        self::create($tenantId, NotificationType::CHECKOUT, 'Check-Out Disetujui', "Pengajuan check-out dari kamar {$roomNumber} telah disetujui.", null, $uniqueKey);
    }

    public static function checkoutRejected(int $tenantId, string $roomNumber): void
    {
        self::create($tenantId, NotificationType::CHECKOUT, 'Check-Out Ditolak', "Pengajuan check-out dari kamar {$roomNumber} telah ditolak.");
    }

    public static function bookingCancelledByTenant(int $ownerId, string $tenantName, string $bookingCode): void
    {
        self::create($ownerId, NotificationType::BOOKING, 'Booking Dibatalkan', "{$tenantName} membatalkan booking {$bookingCode}.");
    }

    public static function advertisingCreated(int $moderatorId, string $ownerName, string $kosName): void
    {
        self::create($moderatorId, NotificationType::ADVERTISING, 'Kampanye Menunggu Review', "{$ownerName} membuat kampanye iklan untuk kos {$kosName} yang menunggu review.");
    }

    public static function advertisingApproved(int $ownerId, string $kosName): void
    {
        self::create($ownerId, NotificationType::ADVERTISING, 'Kampanye Disetujui', "Kampanye iklan untuk kos {$kosName} telah disetujui dan aktif.");
    }

    public static function advertisingRejected(int $ownerId, string $kosName, string $reason): void
    {
        self::create($ownerId, NotificationType::ADVERTISING, 'Kampanye Ditolak', "Kampanye iklan untuk kos {$kosName} ditolak. Alasan: {$reason}");
    }

    public static function advertisingSuspended(int $ownerId, string $kosName, string $reason): void
    {
        self::create($ownerId, NotificationType::ADVERTISING, 'Kampanye Ditangguhkan', "Kampanye iklan untuk kos {$kosName} ditangguhkan. Alasan: {$reason}");
    }

    public static function advertisingPaymentReceived(int $moderatorId, string $ownerName, string $kosName): void
    {
        self::create($moderatorId, NotificationType::ADVERTISING, 'Pembayaran Kampanye Diterima', "{$ownerName} telah membayar kampanye iklan untuk kos {$kosName}.");
    }

    public static function cleanup(int $olderThanDays = 90): int
    {
        return Notification::where('created_at', '<', now()->subDays($olderThanDays))->delete();
    }
}
