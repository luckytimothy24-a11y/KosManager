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

    public static function bookingNew(int $ownerId, string $tenantName, string $roomNumber, ?string $url = null): void
    {
        self::create($ownerId, NotificationType::BOOKING, 'Booking Baru', "{$tenantName} melakukan booking untuk kamar {$roomNumber} dan menunggu konfirmasi Anda.", ['url' => $url]);
    }

    public static function bookingCreated(int $tenantId, string $bookingCode, string $roomNumber, ?string $url = null): void
    {
        self::create($tenantId, NotificationType::BOOKING, 'Booking Dibuat', "Booking {$bookingCode} untuk kamar {$roomNumber} telah dibuat. Silakan lakukan pembayaran untuk melanjutkan. Booking dikonfirmasi setelah pembayaran diverifikasi pemilik kos.", ['url' => $url]);
    }

    public static function bookingApproved(int $tenantId, string $bookingCode, string $roomNumber, ?string $url = null): void
    {
        self::create($tenantId, NotificationType::BOOKING, 'Booking Dikonfirmasi', "Booking {$bookingCode} untuk kamar {$roomNumber} telah dikonfirmasi. Silakan lakukan check-in sesuai jadwal.", ['url' => $url]);
    }

    public static function bookingRejected(int $tenantId, string $bookingCode, string $roomNumber, ?string $reason = null, ?string $url = null): void
    {
        $message = "Booking {$bookingCode} untuk kamar {$roomNumber} ditolak oleh pemilik kos.";
        if ($reason) {
            $message .= " Alasan: {$reason}";
        }
        self::create($tenantId, NotificationType::BOOKING, 'Booking Ditolak', $message, ['url' => $url]);
    }

    /**
     * @deprecated Gunakan bookingCreated — booking tidak lagi auto-confirm.
     */
    public static function bookingInstantConfirmation(int $tenantId, string $bookingCode, string $roomNumber, ?string $url = null): void
    {
        self::bookingCreated($tenantId, $bookingCode, $roomNumber, $url);
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

    public static function bookingPaymentSubmitted(int $ownerId, string $tenantName, string $bookingCode): void
    {
        self::create($ownerId, NotificationType::PAYMENT, 'Pembayaran Booking Baru', "{$tenantName} mengirim pembayaran untuk booking {$bookingCode}. Silakan verifikasi. Booking belum dikonfirmasi.");
    }

    public static function paymentVerified(int $tenantId, string $billNumber): void
    {
        self::create($tenantId, NotificationType::PAYMENT, 'Pembayaran Diverifikasi', "Pembayaran untuk tagihan {$billNumber} telah diverifikasi.");
    }

    public static function bookingPaymentVerified(int $tenantId, string $bookingCode): void
    {
        self::create($tenantId, NotificationType::PAYMENT, 'Pembayaran Booking Diverifikasi', "Pembayaran untuk booking {$bookingCode} telah diverifikasi. Booking masih menunggu konfirmasi pemilik kos.");
    }

    public static function distributionProcessed(int $ownerId, string $bookingCode, float $amount): void
    {
        self::create(
            $ownerId,
            NotificationType::PAYMENT,
            'Pembayaran Dicairkan',
            "Pembayaran booking {$bookingCode} (Rp ".number_format($amount, 0, ',', '.').') ditandai sudah dicairkan ke rekening Anda.'
        );
    }

    public static function paymentRejected(int $tenantId, string $billNumber): void
    {
        self::create($tenantId, NotificationType::PAYMENT, 'Pembayaran Ditolak', "Pembayaran untuk tagihan {$billNumber} telah ditolak. Silakan ajukan ulang.");
    }

    public static function billCreated(int $tenantId, string $billNumber, string $dueDate): void
    {
        self::create($tenantId, NotificationType::BILLING, 'Tagihan Baru', "Tagihan {$billNumber} telah dibuat. Jatuh tempo: {$dueDate}");
    }

    public static function billCreatedOwner(int $ownerId, string $tenantName, string $billNumber, string $amount, string $dueDate): void
    {
        self::create($ownerId, NotificationType::BILLING, 'Tagihan Dibuat Otomatis', "Tagihan {$billNumber} untuk {$tenantName} sebesar {$amount} telah dibuat. Jatuh tempo: {$dueDate}");
    }

    public static function billDueSoon(int $tenantId, string $billNumber, string $dueDate, ?string $uniqueKey = null): void
    {
        self::create($tenantId, NotificationType::BILLING, 'Pengingat Jatuh Tempo', "Tagihan {$billNumber} akan jatuh tempo pada {$dueDate}. Segera lakukan pembayaran.", null, $uniqueKey);
    }

    public static function billDueSoonOwner(int $ownerId, string $tenantName, string $billNumber, string $dueDate, ?string $uniqueKey = null): void
    {
        self::create($ownerId, NotificationType::BILLING, 'Pengingat Jatuh Tempo', "Tagihan {$billNumber} milik {$tenantName} akan jatuh tempo pada {$dueDate}. Pengingat sudah dikirim ke penyewa.", null, $uniqueKey);
    }

    public static function billOverdue(int $tenantId, string $billNumber, string $penaltyAmount, string $totalAmount, ?string $uniqueKey = null): void
    {
        self::create($tenantId, NotificationType::BILLING, 'Tagihan Terlambat', "Tagihan {$billNumber} telah melewati jatuh tempo. Denda keterlambatan sebesar {$penaltyAmount} telah dikenakan. Total yang harus dibayar: {$totalAmount}. Segera lakukan pembayaran untuk menghindari denda tambahan.", ['url' => url('/tenant/tagihan')], $uniqueKey);
    }

    public static function billOverdueOwner(int $ownerId, string $tenantName, string $billNumber, string $amount, ?string $uniqueKey = null): void
    {
        self::create($ownerId, NotificationType::BILLING, 'Tagihan Overdue', "Tagihan {$billNumber} milik {$tenantName} sebesar {$amount} telah melewati jatuh tempo! Pembayaran belum diterima.", null, $uniqueKey);
    }

    public static function billOverdueEscalation(int $ownerId, string $billNumber, string $tenantName, int $daysOverdue, ?string $uniqueKey = null): void
    {
        self::create($ownerId, NotificationType::BILLING, 'Tagihan Overdue - Perlu Perhatian', "{$tenantName} memiliki tagihan {$billNumber} yang sudah overdue {$daysOverdue} hari. Segera tindak lanjuti.", null, $uniqueKey);
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

    public static function bookingCancellationRequested(int $ownerId, string $tenantName, string $bookingCode): void
    {
        self::create($ownerId, NotificationType::BOOKING, 'Pengajuan Pembatalan Booking', "{$tenantName} mengajukan pembatalan booking {$bookingCode}. Silakan review.");
    }

    public static function bookingCancellationApproved(int $tenantId, string $bookingCode, string $roomNumber): void
    {
        self::create($tenantId, NotificationType::BOOKING, 'Pembatalan Booking Disetujui', "Pengajuan pembatalan booking {$bookingCode} untuk kamar {$roomNumber} telah disetujui.");
    }

    public static function bookingCancellationRejected(int $tenantId, string $bookingCode, string $roomNumber, ?string $reason = null): void
    {
        $message = "Pengajuan pembatalan booking {$bookingCode} untuk kamar {$roomNumber} ditolak.";
        if ($reason) {
            $message .= " Alasan: {$reason}";
        }
        self::create($tenantId, NotificationType::BOOKING, 'Pembatalan Booking Ditolak', $message);
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

    public static function messageSent(int $ownerId, string $tenantName, string $kosName, string $url): void
    {
        self::create($ownerId, NotificationType::MESSAGE, 'Pesan Baru', "{$tenantName} mengirim pesan terkait {$kosName}", ['url' => $url]);
    }

    public static function adminMessageSent(int $ownerId, string $adminName, string $kosName, string $url): void
    {
        self::create($ownerId, NotificationType::MESSAGE, 'Pesan dari Admin', "{$adminName} (Admin) mengirim pesan operasional terkait {$kosName}", ['url' => $url]);
    }

    public static function ownerRepliedToAdmin(int $adminId, string $ownerName, string $kosName, string $url): void
    {
        self::create($adminId, NotificationType::MESSAGE, 'Balasan dari Owner', "{$ownerName} membalas pesan operasional terkait {$kosName}", ['url' => $url]);
    }

    public static function messageReplied(int $tenantId, string $ownerName, string $url): void
    {
        self::create($tenantId, NotificationType::MESSAGE, 'Balasan Pesan', "{$ownerName} membalas pesan Anda", ['url' => $url]);
    }

    public static function cleanup(int $olderThanDays = 90): int
    {
        return Notification::where('created_at', '<', now()->subDays($olderThanDays))->delete();
    }
}
