<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\BillingService;
use App\Services\BookingStatusService;
use App\Services\PaymentDistributionService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownPaymentFlowTest extends TestCase
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
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
        ]);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'monthly_price' => 1500000,
        ]);
    }

    private function makeBooking(array $overrides = []): Booking
    {
        return Booking::factory()->create(array_merge([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => Booking::STATUS_PENDING,
            'price' => 1500000,
            'down_payment_amount' => 100000,
            'start_date' => today()->addDays(3),
            'end_date' => today()->addMonths(2),
            'rental_type' => 'monthly',
        ], $overrides));
    }

    private function createActiveContract(float $rentalPrice, float $downPayment = 100000): Kontrak
    {
        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);

        return Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'rental_type' => 'monthly',
            'rental_price' => $rentalPrice,
            'down_payment_amount' => $downPayment,
            'billing_mode' => 'personal',
            'start_date' => '2026-09-01',
            'end_date' => '2027-03-01',
            'status' => 'active',
        ]);
    }

    public function test_booking_creation_uses_server_dp_and_ignores_client_amount(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'rental_type' => 'monthly',
            'phone' => '081234567890',
            'address' => 'Jl. Test No. 1',
            // Nilai dari client tidak boleh dipakai.
            'price' => 1,
            'down_payment_amount' => 1,
            'identity_number' => '1234567890',
        ])->assertRedirect();

        $booking = Booking::where('user_id', $this->tenant->id)->firstOrFail();

        $this->assertEquals(100000, (float) $booking->down_payment_amount);
        $this->assertEquals(1500000, (float) $booking->price);
        $this->assertNull($booking->identity_number);
        $this->assertSame(100000.0, $booking->expectedBookingPaymentAmount());
    }

    public function test_booking_payment_uses_dp_not_full_rent(): void
    {
        $booking = $this->makeBooking();

        $result = app(PaymentService::class)->createForBooking($booking, $this->tenant, 'qris');

        $this->assertTrue($result['ok']);
        $this->assertEquals(100000, (float) $result['pembayaran']->amount);
        $this->assertNull($result['pembayaran']->tagihan_id);
    }

    public function test_owner_cannot_confirm_booking_with_wrong_payment_amount(): void
    {
        $booking = $this->makeBooking();

        $payment = app(PaymentService::class)->createForBooking($booking, $this->tenant, 'qris')['pembayaran'];
        $payment->update([
            'amount' => 50000,
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'verified_at' => now(),
            'verified_by' => $this->owner->id,
        ]);

        $result = app(BookingStatusService::class)->approve($booking->fresh(), $this->owner);

        $this->assertFalse($result['ok']);
        $this->assertSame('payment_required', $result['error']);
        $this->assertSame(Booking::STATUS_PENDING, $booking->fresh()->status);
    }

    public function test_dp_distribution_is_released_to_owner_when_booking_rejected(): void
    {
        $booking = $this->makeBooking();

        $payment = app(PaymentService::class)->createForBooking($booking, $this->tenant, 'qris')['pembayaran'];
        $payment->update([
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'verified_at' => now(),
            'verified_by' => $this->owner->id,
        ]);

        $result = app(PaymentDistributionService::class)->recordFromApprovedPayment($payment->fresh());
        $this->assertTrue($result['ok']);
        $this->assertSame('pending', $result['distribution']->status);
        $this->assertEquals(100000, (float) $result['distribution']->gross_amount);

        $reject = app(BookingStatusService::class)->reject($booking, $this->owner, 'Kamar tidak jadi diambil');

        $this->assertTrue($reject['ok']);
        $this->assertDatabaseHas('payment_distributions', [
            'booking_id' => $booking->id,
            'gross_amount' => 100000,
            'status' => 'ready',
        ]);
    }

    public function test_legacy_booking_without_dp_keeps_full_price_and_pending_distribution(): void
    {
        $booking = $this->makeBooking([
            'price' => 1500000,
            'down_payment_amount' => null,
        ]);

        $payment = app(PaymentService::class)->createForBooking($booking, $this->tenant, 'qris')['pembayaran'];
        $this->assertEquals(1500000, (float) $payment->amount);

        $payment->update([
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'verified_at' => now(),
            'verified_by' => $this->owner->id,
        ]);

        $distribution = app(PaymentDistributionService::class)
            ->recordFromApprovedPayment($payment->fresh())['distribution'];

        $this->assertSame('pending', $distribution->status);
        $this->assertFalse($booking->fresh()->usesDownPayment());

        app(BookingStatusService::class)->reject($booking, $this->owner, 'Ditolak');

        $this->assertSame('pending', $distribution->fresh()->status);
    }

    public function test_first_personal_bill_deducts_dp_once(): void
    {
        $kontrak = $this->createActiveContract(1500000);

        $billing = app(BillingService::class);
        $billing->generateForKontrak($kontrak, 1, 7, Carbon::parse('2026-10-15'));

        $first = Tagihan::where('kontrak_id', $kontrak->id)->firstOrFail();
        $this->assertEquals(100000, (float) $first->down_payment_discount);
        $this->assertEquals(1400000, (float) $first->total);

        $billing->generateForKontrak($kontrak->fresh(), 1, 7, Carbon::parse('2026-11-15'));

        $second = Tagihan::where('kontrak_id', $kontrak->id)
            ->orderByDesc('id')
            ->firstOrFail();

        $this->assertEquals(0, (float) $second->down_payment_discount);
        $this->assertEquals(1500000, (float) $second->total);
        $this->assertEquals(0, (float) $kontrak->fresh()->down_payment_amount);
    }

    public function test_first_bill_caps_dp_at_subtotal_and_marks_zero_total_paid(): void
    {
        $kontrak = $this->createActiveContract(50000);

        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontrak->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'discount' => 0,
            'penalty' => 0,
            'due_date' => now()->addDays(10)->toDateString(),
        ])->assertRedirect();

        $tagihan = Tagihan::where('kontrak_id', $kontrak->id)->firstOrFail();

        $this->assertEquals(50000, (float) $tagihan->subtotal);
        $this->assertEquals(50000, (float) $tagihan->down_payment_discount);
        $this->assertEquals(0, (float) $tagihan->total);
        $this->assertSame(Tagihan::STATUS_PAID, $tagihan->status);
        $this->assertEquals(0, (float) $kontrak->fresh()->down_payment_amount);
    }

    public function test_booking_timeline_keeps_payment_waiting_until_owner_verifies(): void
    {
        $booking = $this->makeBooking();
        app(PaymentService::class)->createForBooking($booking, $this->tenant, 'qris');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertSee('data-step="payment" data-state="waiting"', false);
        $response->assertSee('Pembayaran DP');
    }

    public function test_booking_timeline_marks_payment_done_only_after_verification(): void
    {
        $booking = $this->makeBooking();
        $payment = app(PaymentService::class)->createForBooking($booking, $this->tenant, 'qris')['pembayaran'];
        $payment->update([
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'verified_at' => now(),
            'verified_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertSee('data-step="payment" data-state="done"', false);
        $response->assertSee('Pembayaran DP');
    }

    public function test_legacy_booking_timeline_uses_full_payment_label(): void
    {
        $booking = $this->makeBooking(['down_payment_amount' => null]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertSee('data-step="payment" data-state="current"', false);
        $response->assertSee('Pembayaran Booking');
    }
}
