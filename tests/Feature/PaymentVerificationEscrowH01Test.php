<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * H-01: semua jalur verifikasi payment booking (web, bulk, API) harus membuat
 * tepat satu escrow pending. Webhook sudah dicakup KosHubPaymentFlowTest.
 *
 * @group escrow
 * @group h01
 */
class PaymentVerificationEscrowH01Test extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'pencari']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'booked']);
    }

    private function makeBooking(array $overrides = []): Booking
    {
        return Booking::factory()->create(array_merge([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => Booking::STATUS_PENDING,
            'price' => 1500000,
            'down_payment_amount' => null,
        ], $overrides));
    }

    private function makePendingPayment(Booking $booking, ?float $amount = null): Pembayaran
    {
        return Pembayaran::factory()->create([
            'booking_id' => $booking->id,
            'tagihan_id' => null,
            'penghuni_id' => null,
            'amount' => $amount ?? $booking->expectedBookingPaymentAmount(),
            'payment_date' => now(),
            'payment_method' => 'qris',
            'verification_status' => Pembayaran::STATUS_PENDING,
        ]);
    }

    private function assertSinglePendingEscrow(Pembayaran $payment, int $expectedAmount): void
    {
        $escrows = WalletTransaction::where('pembayaran_id', $payment->id)
            ->where('type', 'escrow_in')
            ->get();

        $this->assertCount(1, $escrows);
        $this->assertSame('pending', $escrows->first()->status);
        $this->assertSame($expectedAmount, $escrows->first()->amount);
        $this->assertSame((int) $this->owner->id, (int) $escrows->first()->owner_id);
        $this->assertSame((int) $payment->booking_id, (int) $escrows->first()->booking_id);
    }

    public function test_web_verification_creates_one_pending_escrow_using_down_payment_amount(): void
    {
        $booking = $this->makeBooking(['down_payment_amount' => 300000]);
        $payment = $this->makePendingPayment($booking, 300000);

        $this->actingAs($this->owner)
            ->post(route('owner.pembayaran.verify', $payment))
            ->assertRedirect();

        $this->assertSame(Pembayaran::STATUS_APPROVED, $payment->fresh()->verification_status);
        $this->assertSame(Booking::STATUS_PENDING, $booking->fresh()->status);
        $this->assertSinglePendingEscrow($payment, 300000);
    }

    public function test_duplicate_web_verification_does_not_create_second_escrow(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->makePendingPayment($booking);

        $this->actingAs($this->owner)
            ->post(route('owner.pembayaran.verify', $payment))
            ->assertRedirect();

        $this->actingAs($this->owner)
            ->post(route('owner.pembayaran.verify', $payment))
            ->assertStatus(400);

        $this->assertSinglePendingEscrow($payment, 1500000);
    }

    public function test_web_verification_rejects_wrong_amount_without_creating_escrow(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->makePendingPayment($booking, 1499999);

        $this->actingAs($this->owner)
            ->post(route('owner.pembayaran.verify', $payment))
            ->assertStatus(400);

        $this->assertSame(Pembayaran::STATUS_PENDING, $payment->fresh()->verification_status);
        $this->assertSame(0, WalletTransaction::where('pembayaran_id', $payment->id)->count());
    }

    public function test_bulk_verification_creates_one_pending_escrow(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->makePendingPayment($booking);

        $this->actingAs($this->owner)
            ->post(route('owner.pembayaran.bulk-verify'), ['ids' => [$payment->id]])
            ->assertRedirect(route('owner.pembayaran.index'))
            ->assertSessionHas('success');

        $this->assertSame(Pembayaran::STATUS_APPROVED, $payment->fresh()->verification_status);
        $this->assertSinglePendingEscrow($payment, 1500000);
    }

    public function test_bulk_verification_skips_wrong_amount_without_creating_escrow(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->makePendingPayment($booking, 1499999);

        $this->actingAs($this->owner)
            ->post(route('owner.pembayaran.bulk-verify'), ['ids' => [$payment->id]])
            ->assertRedirect(route('owner.pembayaran.index'))
            ->assertSessionHas('warning');

        $this->assertSame(Pembayaran::STATUS_PENDING, $payment->fresh()->verification_status);
        $this->assertSame(0, WalletTransaction::where('pembayaran_id', $payment->id)->count());
    }

    public function test_api_verification_creates_one_pending_escrow(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->makePendingPayment($booking);

        Sanctum::actingAs($this->owner);

        $this->postJson(route('api.v1.owner.pembayaran.verify', $payment))
            ->assertOk()
            ->assertJsonPath('verification_status', Pembayaran::STATUS_APPROVED)
            ->assertJsonPath('booking_status', Booking::STATUS_PENDING);

        $this->assertSinglePendingEscrow($payment, 1500000);
    }

    public function test_api_verification_rejects_wrong_amount_without_creating_escrow(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->makePendingPayment($booking, 1499999);

        Sanctum::actingAs($this->owner);

        $this->postJson(route('api.v1.owner.pembayaran.verify', $payment))
            ->assertStatus(400);

        $this->assertSame(Pembayaran::STATUS_PENDING, $payment->fresh()->verification_status);
        $this->assertSame(0, WalletTransaction::where('pembayaran_id', $payment->id)->count());
    }
}
