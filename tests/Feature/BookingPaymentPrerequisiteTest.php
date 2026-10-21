<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Booking wajib bayar sebelum konfirmasi.
 * Payment source of truth: pembayarans.verification_status
 * Payment approved ≠ booking approved
 */
class BookingPaymentPrerequisiteTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        Storage::fake('public');

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'booked',
            'monthly_price' => 1500000,
        ]);
    }

    private function makeBooking(string $status = 'pending', float $price = 1500000.0): Booking
    {
        return Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => $status,
            'price' => $price,
            'start_date' => today()->addDays(3),
            'end_date' => today()->addMonths(2),
            'rental_type' => 'monthly',
        ]);
    }

    private function submitBookingPayment(Booking $booking, array $overrides = []): TestResponse
    {
        return $this->actingAs($this->tenant)->post(route('tenant.booking.payment.store', $booking), array_merge([
            'payment_method' => 'cash',
        ], $overrides));
    }

    private function approveBookingPayment(Booking $booking): Pembayaran
    {
        $payment = Pembayaran::where('booking_id', $booking->id)->firstOrFail();
        $this->actingAs($this->owner)->post(route('owner.pembayaran.verify', $payment));

        return $payment->fresh();
    }

    // ── A: Tenant membuat booking ─────────────────────────────

    public function test_scenario_a_create_booking_is_pending(): void
    {
        $booking = $this->makeBooking();

        $this->assertEquals('pending', $booking->status);
        $this->assertNotEquals('approved', $booking->status);
        $this->assertDatabaseMissing('penghunis', ['user_id' => $this->tenant->id, 'status' => 'active']);
    }

    // ── B: Tenant belum membayar ──────────────────────────────

    public function test_scenario_b_owner_cannot_confirm_without_payment(): void
    {
        $booking = $this->makeBooking('pending');

        $this->actingAs($this->owner)
            ->post(route('owner.booking.approve', $booking))
            ->assertSessionHas('error');

        $this->assertEquals('pending', $booking->fresh()->status);
        $this->assertFalse($booking->fresh()->hasApprovedPayment());
    }

    // ── C: Tenant submit payment ──────────────────────────────

    public function test_scenario_c_submit_payment_keeps_booking_pending(): void
    {
        $booking = $this->makeBooking('pending');

        $this->submitBookingPayment($booking)->assertRedirect();

        $payment = Pembayaran::where('booking_id', $booking->id)->firstOrFail();
        $this->assertSame('pending', $payment->verification_status);
        $this->assertEquals('pending', $booking->fresh()->status);
        // Nominal dari server
        $this->assertEquals(1500000.0, (float) $payment->amount);
    }

    public function test_amount_manipulation_is_ignored(): void
    {
        $booking = $this->makeBooking('pending', 1000000.0);

        $this->submitBookingPayment($booking, ['amount' => 50000])->assertRedirect();

        $payment = Pembayaran::where('booking_id', $booking->id)->firstOrFail();
        $this->assertEquals(1000000.0, (float) $payment->amount);
    }

    // ── D: Owner approve payment — TIDAK auto-confirm ─────────

    public function test_scenario_d_payment_approved_does_not_auto_confirm_booking(): void
    {
        $booking = $this->makeBooking('pending');
        $this->submitBookingPayment($booking);

        $payment = $this->approveBookingPayment($booking);

        $this->assertSame('approved', $payment->verification_status);
        $this->assertEquals('pending', $booking->fresh()->status);
        $this->assertDatabaseMissing('penghunis', ['user_id' => $this->tenant->id, 'status' => 'active']);
    }

    // ── E: Owner confirm booking setelah payment approved ─────

    public function test_scenario_e_owner_confirm_after_payment_approved(): void
    {
        $booking = $this->makeBooking('pending');
        $this->submitBookingPayment($booking);
        $this->approveBookingPayment($booking);

        $this->actingAs($this->owner)
            ->post(route('owner.booking.approve', $booking))
            ->assertSessionHas('success');

        $this->assertEquals('approved', $booking->fresh()->status);
        // Tenant belum active — check-in tetap terpisah
        $this->assertDatabaseMissing('penghunis', ['user_id' => $this->tenant->id, 'status' => 'active']);
    }

    // ── F: Payment rejected ───────────────────────────────────

    public function test_scenario_f_rejected_payment_cannot_confirm_booking(): void
    {
        $booking = $this->makeBooking('pending');
        $this->submitBookingPayment($booking);
        $payment = Pembayaran::where('booking_id', $booking->id)->firstOrFail();

        $this->actingAs($this->owner)->post(route('owner.pembayaran.reject', $payment), [
            'reason' => 'Bukti tidak jelas',
        ]);

        $this->assertSame('rejected', $payment->fresh()->verification_status);
        $this->assertEquals('pending', $booking->fresh()->status);

        $this->actingAs($this->owner)
            ->post(route('owner.booking.approve', $booking))
            ->assertSessionHas('error');
        $this->assertEquals('pending', $booking->fresh()->status);
    }

    // ── G: Tenant manipulasi booking status ───────────────────

    public function test_scenario_g_tenant_cannot_set_status_approved(): void
    {
        $kamar2 = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'monthly_price' => 900000,
        ]);

        $this->actingAs($this->tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'start_date' => today()->addDays(10)->toDateString(),
            'end_date' => today()->addMonths(3)->toDateString(),
            'rental_type' => 'monthly',
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'status' => 'approved',
        ]);

        $newBooking = Booking::where('user_id', $this->tenant->id)->where('kamar_id', $kamar2->id)->first();
        $this->assertNotNull($newBooking);
        $this->assertEquals('pending', $newBooking->status);
    }

    // ── H: Tenant manipulasi payment status ───────────────────

    public function test_scenario_h_tenant_cannot_approve_own_payment(): void
    {
        $booking = $this->makeBooking('pending');
        $this->submitBookingPayment($booking);
        $payment = Pembayaran::where('booking_id', $booking->id)->firstOrFail();

        // Tenant mencoba verify payment sendiri via endpoint owner — harus ditolak
        $this->actingAs($this->tenant)
            ->post(route('owner.pembayaran.verify', $payment))
            ->assertForbidden();

        $this->assertSame('pending', $payment->fresh()->verification_status);
        $this->assertEquals('pending', $booking->fresh()->status);
    }

    public function test_api_tenant_cannot_verify_payment(): void
    {
        $booking = $this->makeBooking('pending');
        $this->submitBookingPayment($booking);
        $payment = Pembayaran::where('booking_id', $booking->id)->firstOrFail();

        $token = $this->tenant->createToken('test')->plainTextToken;
        $this->withToken($token)
            ->postJson("/api/v1/owner/pembayaran/{$payment->id}/verify")
            ->assertForbidden();

        $this->assertSame('pending', $payment->fresh()->verification_status);
    }

    // ── I/J: Cancellation tetap kompatibel ────────────────────

    public function test_scenario_i_cancellation_before_payment(): void
    {
        $booking = $this->makeBooking('pending');

        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), [
            'reason' => 'belum bayar',
        ]);

        $this->assertEquals('pending', $booking->fresh()->status);
        $this->assertSame('pending', BookingCancellationRequest::where('booking_id', $booking->id)->first()->status);
        $this->assertDatabaseMissing('pembayarans', ['booking_id' => $booking->id]);
    }

    public function test_scenario_j_cancellation_after_payment_approved(): void
    {
        $booking = $this->makeBooking('pending');
        $this->submitBookingPayment($booking);
        $payment = $this->approveBookingPayment($booking);

        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), [
            'reason' => 'setelah bayar',
        ]);

        $this->assertEquals('pending', $booking->fresh()->status);
        $this->assertSame('pending', BookingCancellationRequest::where('booking_id', $booking->id)->first()->status);
        // Payment TIDAK berubah otomatis
        $this->assertSame('approved', $payment->fresh()->verification_status);
    }

    public function test_owner_approve_cancellation_keeps_payment_state(): void
    {
        $booking = $this->makeBooking('pending');
        $this->submitBookingPayment($booking);
        $payment = $this->approveBookingPayment($booking);

        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), ['reason' => 'x']);
        $request = BookingCancellationRequest::where('booking_id', $booking->id)->firstOrFail();

        $this->actingAs($this->owner)->post(route('owner.booking.cancellation.approve', $request));

        $this->assertEquals('cancelled', $booking->fresh()->status);
        $this->assertSame('approved', $payment->fresh()->verification_status);
    }

    // ── Security: IDOR / authorization ────────────────────────

    public function test_owner_b_cannot_confirm_paid_booking_of_owner_a(): void
    {
        $booking = $this->makeBooking('pending');
        $this->submitBookingPayment($booking);
        $this->approveBookingPayment($booking);

        $ownerB = User::factory()->create(['role' => 'owner']);
        $this->actingAs($ownerB)
            ->post(route('owner.booking.approve', $booking))
            ->assertForbidden();

        $this->assertEquals('pending', $booking->fresh()->status);
    }

    public function test_tenant_cannot_approve_own_paid_booking(): void
    {
        $booking = $this->makeBooking('pending');
        $this->submitBookingPayment($booking);
        $this->approveBookingPayment($booking);

        $this->actingAs($this->tenant)
            ->post(route('owner.booking.approve', $booking))
            ->assertForbidden();

        $this->assertEquals('pending', $booking->fresh()->status);
    }

    // ── API flows ─────────────────────────────────────────────

    public function test_api_booking_payment_and_confirm_flow(): void
    {
        $booking = $this->makeBooking('pending');

        $tenantToken = $this->tenant->createToken('test')->plainTextToken;
        $this->withToken($tenantToken)
            ->postJson("/api/v1/bookings/{$booking->id}/payment", [
                'payment_method' => 'cash',
                'verification_status' => 'approved',
                'amount' => 1,
            ])
            ->assertStatus(201);

        $payment = Pembayaran::where('booking_id', $booking->id)->firstOrFail();
        $this->assertSame('pending', $payment->verification_status);
        $this->assertEquals(1500000.0, (float) $payment->amount);

        // Verify via web endpoint (authorization + amount check)
        $this->actingAs($this->owner)->post(route('owner.pembayaran.verify', $payment));
        $this->assertSame('approved', $payment->fresh()->verification_status);
        $this->assertEquals('pending', $booking->fresh()->status);

        $this->actingAs($this->owner)
            ->post(route('owner.booking.approve', $booking))
            ->assertSessionHas('success');

        $this->assertEquals('approved', $booking->fresh()->status);
    }

    // ── Labels alignment ──────────────────────────────────────

    public function test_payment_labels_are_consistent(): void
    {
        $this->assertSame('Menunggu Verifikasi', \PaymentLabels::verificationLabel('pending'));
        $this->assertSame('Terverifikasi', \PaymentLabels::verificationLabel('approved'));
        $this->assertSame('Pembayaran Ditolak', \PaymentLabels::verificationLabel('rejected'));
    }

    // ── Idempotent verify ─────────────────────────────────────

    public function test_duplicate_payment_verify_is_idempotent(): void
    {
        $booking = $this->makeBooking('pending');
        $this->submitBookingPayment($booking);
        $payment = $this->approveBookingPayment($booking);

        $countAfterFirst = AuditLog::where('module', 'Pembayaran')
            ->where('action', 'Approve')
            ->count();

        $this->actingAs($this->owner)
            ->post(route('owner.pembayaran.verify', $payment))
            ->assertStatus(400);

        $this->assertSame('approved', $payment->fresh()->verification_status);
        $this->assertSame(
            $countAfterFirst,
            AuditLog::where('module', 'Pembayaran')->where('action', 'Approve')->count()
        );
        $this->assertEquals('pending', $booking->fresh()->status);
    }

    // ── Existing tagihan payment flow tetap jalan ─────────────

    public function test_existing_tagihan_payment_flow_remains_compatible(): void
    {
        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
        ]);

        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $tagihan->id,
            'amount' => $tagihan->total,
            'payment_method' => 'cash',
            'proof_file' => $proof,
        ])->assertRedirect();

        $pembayaran = Pembayaran::where('tagihan_id', $tagihan->id)->firstOrFail();
        $this->assertSame('pending', $pembayaran->verification_status);
        $this->assertNull($pembayaran->booking_id);
        $this->assertEquals('pending_verification', $tagihan->fresh()->status);

        $this->actingAs($this->owner)->post(route('owner.pembayaran.verify', $pembayaran));
        $this->assertSame('approved', $pembayaran->fresh()->verification_status);
        $this->assertEquals('paid', $tagihan->fresh()->status);
    }
}
