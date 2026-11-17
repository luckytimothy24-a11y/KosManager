<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\PaymentDistribution;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Laporan read-only untuk data lama C3:
 * booking cancelled + DP yang sudah terlanjur refund requested/approved.
 */
class CancelledDownPaymentRefundReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_reports_legacy_cancelled_dp_refunds_without_changing_data(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);

        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => Booking::STATUS_CANCELLED,
            'down_payment_amount' => 100000,
            'price' => 1500000,
        ]);

        $payment = Pembayaran::factory()->create([
            'booking_id' => $booking->id,
            'tagihan_id' => null,
            'penghuni_id' => null,
            'amount' => 100000,
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'refund_status' => Pembayaran::REFUND_REQUESTED,
            'refund_amount' => 100000,
            'refund_reason' => 'Legacy cancellation refund',
        ]);

        $distribution = PaymentDistribution::create([
            'pembayaran_id' => $payment->id,
            'booking_id' => $booking->id,
            'owner_id' => $owner->id,
            'gross_amount' => 100000,
            'platform_fee' => 0,
            'owner_amount' => 100000,
            'status' => PaymentDistribution::STATUS_READY,
        ]);

        // Booking cancelled ber-DP tanpa refund request tidak boleh masuk laporan.
        $cleanBooking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => Booking::STATUS_CANCELLED,
            'down_payment_amount' => 100000,
            'price' => 1500000,
        ]);
        Pembayaran::factory()->create([
            'booking_id' => $cleanBooking->id,
            'tagihan_id' => null,
            'penghuni_id' => null,
            'amount' => 100000,
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'refund_status' => Pembayaran::REFUND_NONE,
        ]);

        // PendingCommand dapat mencocokkan satu substring per write; jalankan
        // command beberapa kali (read-only) untuk memeriksa kolom yang berbeda.
        $this->artisan('refunds:report-cancelled-dp')
            ->expectsOutputToContain('requested | ready')
            ->assertExitCode(0);

        $this->artisan('refunds:report-cancelled-dp')
            ->expectsOutputToContain($booking->booking_code)
            ->assertExitCode(0);

        $this->artisan('refunds:report-cancelled-dp')
            ->doesntExpectOutputToContain($cleanBooking->booking_code)
            ->assertExitCode(0);

        // Read-only: data legacy tidak diubah.
        $this->assertSame(Pembayaran::REFUND_REQUESTED, $payment->fresh()->refund_status);
        $this->assertSame(PaymentDistribution::STATUS_READY, $distribution->fresh()->status);
    }

    public function test_command_can_output_json_for_audit(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => Booking::STATUS_CANCELLED,
            'down_payment_amount' => 100000,
            'price' => 1500000,
        ]);
        Pembayaran::factory()->create([
            'booking_id' => $booking->id,
            'tagihan_id' => null,
            'penghuni_id' => null,
            'amount' => 100000,
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'refund_status' => Pembayaran::REFUND_APPROVED,
            'refund_amount' => 100000,
        ]);

        $this->artisan('refunds:report-cancelled-dp', ['--json' => true])
            ->expectsOutputToContain('"refund_status": "approved"')
            ->assertExitCode(0);

        $this->artisan('refunds:report-cancelled-dp', ['--json' => true])
            ->expectsOutputToContain('"booking_code": "'.$booking->booking_code.'"')
            ->assertExitCode(0);
    }
}
