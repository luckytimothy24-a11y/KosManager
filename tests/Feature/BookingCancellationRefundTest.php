<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Notification;
use App\Models\PaymentDistribution;
use App\Models\Pembayaran;
use App\Models\User;
use App\Services\PaymentDistributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * C3: DP non-refundable saat booking dibatalkan.
 *
 * DP menjadi hak owner (distribution released), tetapi tidak boleh
 * ditandai sebagai refund request dan tenant tidak boleh dijanjikan refund.
 * Pembayaran legacy non-DP tetap refundable penuh.
 */
class BookingCancellationRefundTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'pencari']);
        $this->kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
        ]);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'booked',
            'monthly_price' => 1500000,
        ]);
    }

    private function makeBooking(array $attributes = []): Booking
    {
        return Booking::factory()->create(array_merge([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => Booking::STATUS_APPROVED,
            'price' => 1500000,
            'start_date' => today()->addDays(3),
            'end_date' => today()->addMonths(2),
        ], $attributes));
    }

    private function makeBookingPayment(Booking $booking, float $amount): Pembayaran
    {
        return Pembayaran::factory()->create([
            'booking_id' => $booking->id,
            'tagihan_id' => null,
            'penghuni_id' => null,
            'amount' => $amount,
            'payment_date' => now(),
            'verification_status' => Pembayaran::STATUS_APPROVED,
        ]);
    }

    private function requestAndApproveCancellation(Booking $booking): void
    {
        $this->actingAs($this->tenant)
            ->post(route('tenant.booking.cancel', $booking), ['reason' => 'Pindah kota'])
            ->assertRedirect();

        $cancellationRequest = BookingCancellationRequest::where('booking_id', $booking->id)->firstOrFail();

        $this->actingAs($this->owner)
            ->post(route('owner.booking.cancellation.approve', $cancellationRequest))
            ->assertRedirect();
    }

    public function test_cancelling_dp_booking_keeps_refund_none_and_releases_distribution(): void
    {
        $booking = $this->makeBooking([
            'status' => Booking::STATUS_PENDING,
            'down_payment_amount' => 100000,
        ]);
        $payment = $this->makeBookingPayment($booking, 100000);

        // Distribution dibuat saat booking masih pending → status pending.
        $distribution = app(PaymentDistributionService::class)
            ->recordFromApprovedPayment($payment)['distribution'];
        $this->assertSame(PaymentDistribution::STATUS_PENDING, $distribution->status);

        // Konfirmasi booking tidak boleh membuat distribution ready lebih awal.
        $booking->update(['status' => Booking::STATUS_APPROVED]);

        $this->requestAndApproveCancellation($booking);

        $booking->refresh();
        $payment->refresh();
        $distribution->refresh();

        $this->assertSame(Booking::STATUS_CANCELLED, $booking->status);
        $this->assertSame(Pembayaran::REFUND_NONE, $payment->refund_status);
        $this->assertNull($payment->refund_amount);
        $this->assertNull($payment->refund_reason);
        $this->assertSame(PaymentDistribution::STATUS_READY, $distribution->status);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->tenant->id,
            'type' => 'refund',
        ]);

        $notification = Notification::where('user_id', $this->tenant->id)
            ->where('type', 'booking')
            ->where('title', 'Booking Dibatalkan — DP Tidak Dapat Dikembalikan')
            ->first();

        $this->assertNotNull($notification, 'Notifikasi DP non-refundable tidak ditemukan.');
        $this->assertStringContainsString('Rp 100.000', $notification->message);
        $this->assertStringContainsString('tidak dapat dikembalikan', $notification->message);
        $this->assertStringContainsString('hak pemilik kos', $notification->message);
    }

    public function test_cancelling_legacy_non_dp_booking_creates_tenant_initiated_refund_request(): void
    {
        $booking = $this->makeBooking([
            'status' => Booking::STATUS_APPROVED,
            'down_payment_amount' => null,
            'price' => 1500000,
        ]);
        $payment = $this->makeBookingPayment($booking, 1500000);

        $this->requestAndApproveCancellation($booking);

        $booking->refresh();
        $payment->refresh();

        $this->assertSame(Booking::STATUS_CANCELLED, $booking->status);
        $this->assertSame(Pembayaran::REFUND_REQUESTED, $payment->refund_status);
        $this->assertEquals(1500000.0, (float) $payment->refund_amount);
        $this->assertStringContainsString('penyewa', (string) $payment->refund_reason);
        $this->assertStringNotContainsString('oleh owner', (string) $payment->refund_reason);

        $notification = Notification::where('user_id', $this->tenant->id)
            ->where('type', 'refund')
            ->where('title', 'Refund Booking Diajukan')
            ->first();

        $this->assertNotNull($notification, 'Notifikasi refund non-DP tidak ditemukan.');
        $this->assertStringContainsString('Rp 1.500.000', $notification->message);
        $this->assertStringContainsString('permintaan penyewa', $notification->message);
        $this->assertStringNotContainsString('oleh owner', $notification->message);
    }

    public function test_cancelling_dp_booking_does_not_auto_refund_extra_manual_payment(): void
    {
        $booking = $this->makeBooking([
            'status' => Booking::STATUS_APPROVED,
            'down_payment_amount' => 100000,
        ]);
        $dpPayment = $this->makeBookingPayment($booking, 100000);
        $extraPayment = $this->makeBookingPayment($booking, 400000);

        $this->requestAndApproveCancellation($booking);

        $this->assertSame(Pembayaran::REFUND_NONE, $dpPayment->fresh()->refund_status);
        $this->assertSame(Pembayaran::REFUND_NONE, $extraPayment->fresh()->refund_status);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->tenant->id,
            'type' => 'refund',
        ]);
    }
}
