<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\BookingStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regression: Tenant Request Cancellation ≠ Booking Cancelled.
 *
 * Mengunci schema existing (`booking_cancellation_requests`) dan aturan:
 * - request tidak mengubah booking/tenant/payment/room
 * - hanya owner approve yang membatalkan booking
 * - tenant tidak bisa manipulate status
 */
class BookingCancellationRequestTest extends TestCase
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
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'booked',
            'monthly_price' => 1500000,
        ]);
    }

    private function makeBooking(string $status): Booking
    {
        return Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => $status,
            'start_date' => today()->addDays(3),
            'end_date' => today()->addMonths(2),
        ]);
    }

    private function makePenghuni(string $status = 'active'): Penghuni
    {
        return Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => $status,
        ]);
    }

    // ── Schema existing ────────────────────────────────────────

    public function test_existing_cancellation_request_schema_is_used(): void
    {
        $this->assertTrue(Schema::hasTable('booking_cancellation_requests'));
        $this->assertTrue(Schema::hasColumn('booking_cancellation_requests', 'booking_id'));
        $this->assertTrue(Schema::hasColumn('booking_cancellation_requests', 'requested_by'));
        $this->assertTrue(Schema::hasColumn('booking_cancellation_requests', 'status'));
        $this->assertTrue(Schema::hasColumn('booking_cancellation_requests', 'reason'));
        $this->assertTrue(Schema::hasColumn('booking_cancellation_requests', 'reviewed_by'));
        $this->assertTrue(Schema::hasColumn('booking_cancellation_requests', 'reviewed_at'));
        $this->assertTrue(Schema::hasColumn('booking_cancellation_requests', 'rejection_reason'));
        // Booking status enum TIDAK ditambah dengan cancel_requested
        $columns = Schema::getColumnListing('bookings');
        $this->assertNotContains('cancellation_status', $columns);
        $this->assertNotContains('cancel_requested', $columns);
    }

    // ── Scenario A: Tenant cancel pending booking ──────────────

    public function test_scenario_a_tenant_request_cancel_pending_booking(): void
    {
        $booking = $this->makeBooking('pending');
        $this->kamar->update(['status' => 'booked']);

        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), [
            'reason' => 'Tidak jadi sewa',
            // Manipulasi — harus diabaikan
            'status' => 'cancelled',
            'cancellation_status' => 'approved',
            'tenant_status' => 'inactive',
        ]);

        $response->assertRedirect();

        // Booking status TIDAK berubah
        $this->assertEquals('pending', $booking->fresh()->status);

        // Cancellation request = pending di schema existing
        $request = BookingCancellationRequest::where('booking_id', $booking->id)->first();
        $this->assertNotNull($request);
        $this->assertSame('pending', $request->status);
        $this->assertSame($this->tenant->id, $request->requested_by);

        // Tenant tidak otomatis inactive
        $this->assertDatabaseMissing('penghunis', ['user_id' => $this->tenant->id, 'status' => 'active']);
        // Room reservation tidak berubah
        $this->assertEquals('booked', $this->kamar->fresh()->status);
    }

    // ── Scenario B: Tenant cancel approved booking ─────────────

    public function test_scenario_b_tenant_request_cancel_approved_booking(): void
    {
        $booking = $this->makeBooking('approved');
        $penghuni = $this->makePenghuni('active');
        $this->kamar->update(['status' => 'occupied']);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
        ]);
        $pembayaran = Pembayaran::factory()->create([
            'penghuni_id' => $penghuni->id,
            'tagihan_id' => $tagihan->id,
            'verification_status' => 'approved',
            'amount' => $tagihan->total,
        ]);

        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), [
            'reason' => 'Pindah kota',
        ])->assertRedirect();

        $this->assertEquals('approved', $booking->fresh()->status);
        $this->assertSame('pending', BookingCancellationRequest::where('booking_id', $booking->id)->first()->status);
        // Tenant & payment & room tidak berubah
        $this->assertEquals('active', $penghuni->fresh()->status);
        $this->assertEquals('approved', $pembayaran->fresh()->verification_status);
        $this->assertEquals('occupied', $this->kamar->fresh()->status);
    }

    // ── Scenario C: Owner approve ──────────────────────────────

    public function test_scenario_c_owner_approve_cancels_booking(): void
    {
        $booking = $this->makeBooking('approved');
        $this->kamar->update(['status' => 'booked']);

        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), [
            'reason' => 'Minta pembatalan',
        ]);
        $cancellationRequest = BookingCancellationRequest::where('booking_id', $booking->id)->firstOrFail();

        $this->actingAs($this->owner)
            ->post(route('owner.booking.cancellation.approve', $cancellationRequest))
            ->assertRedirect();

        $this->assertSame('approved', $cancellationRequest->fresh()->status);
        $this->assertSame($this->owner->id, $cancellationRequest->fresh()->reviewed_by);
        $this->assertEquals('cancelled', $booking->fresh()->status);
        // Release reservation hanya setelah approve
        $this->assertEquals('available', $this->kamar->fresh()->status);
    }

    // ── Scenario D: Owner reject ───────────────────────────────

    public function test_scenario_d_owner_reject_keeps_booking_status(): void
    {
        $booking = $this->makeBooking('approved');
        $this->kamar->update(['status' => 'booked']);

        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), [
            'reason' => 'Minta pembatalan',
        ]);
        $cancellationRequest = BookingCancellationRequest::where('booking_id', $booking->id)->firstOrFail();

        $this->actingAs($this->owner)
            ->post(route('owner.booking.cancellation.reject', $cancellationRequest), [
                'reason' => 'Masa sewa hampir selesai, mohon selesaikan.',
            ])->assertRedirect();

        $this->assertSame('rejected', $cancellationRequest->fresh()->status);
        $this->assertEquals('approved', $booking->fresh()->status);
        $this->assertEquals('booked', $this->kamar->fresh()->status);
    }

    // ── Scenario E: Tenant auto-cancel via POST ────────────────

    public function test_scenario_e_tenant_post_does_not_cancel_booking(): void
    {
        $booking = $this->makeBooking('pending');

        $this->actingAs($this->tenant)
            ->post(route('tenant.booking.cancel', $booking))
            ->assertRedirect();

        $this->assertEquals('pending', $booking->fresh()->status);
        $this->assertSame('pending', BookingCancellationRequest::where('booking_id', $booking->id)->first()->status);
        $this->assertNotEquals('cancelled', $booking->fresh()->status);
    }

    // ── Scenario F: Tenant manipulate status ───────────────────

    public function test_scenario_f_tenant_cannot_manipulate_cancellation_status(): void
    {
        $booking = $this->makeBooking('approved');

        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), [
            'reason' => 'manipulasi',
            'status' => 'cancelled',
            'cancellation_status' => 'approved',
            'reviewed_by' => $this->owner->id,
            'reviewed_at' => now(),
        ])->assertRedirect();

        $request = BookingCancellationRequest::where('booking_id', $booking->id)->firstOrFail();

        $this->assertEquals('approved', $booking->fresh()->status);
        $this->assertSame('pending', $request->status);
        $this->assertNull($request->reviewed_by);
        $this->assertNull($request->reviewed_at);
    }

    // ── Authorization ──────────────────────────────────────────

    public function test_tenant_cannot_approve_own_cancellation_request(): void
    {
        $booking = $this->makeBooking('approved');
        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), ['reason' => 'x']);
        $request = BookingCancellationRequest::where('booking_id', $booking->id)->firstOrFail();

        $this->actingAs($this->tenant)
            ->post(route('owner.booking.cancellation.approve', $request))
            ->assertForbidden();

        $this->assertSame('pending', $request->fresh()->status);
        $this->assertEquals('approved', $booking->fresh()->status);
    }

    public function test_owner_b_cannot_approve_cancellation_of_owner_a(): void
    {
        $booking = $this->makeBooking('approved');
        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), ['reason' => 'x']);
        $request = BookingCancellationRequest::where('booking_id', $booking->id)->firstOrFail();

        $ownerB = User::factory()->create(['role' => 'owner']);
        $this->actingAs($ownerB)
            ->post(route('owner.booking.cancellation.approve', $request))
            ->assertForbidden();

        $this->assertSame('pending', $request->fresh()->status);
        $this->assertEquals('approved', $booking->fresh()->status);
    }

    public function test_tenant_cannot_request_cancel_terminal_booking(): void
    {
        $completed = $this->makeBooking('completed');

        $this->actingAs($this->tenant)
            ->post(route('tenant.booking.cancel', $completed), ['reason' => 'x'])
            ->assertForbidden();

        $this->assertDatabaseCount('booking_cancellation_requests', 0);
        $this->assertEquals('completed', $completed->fresh()->status);
    }

    public function test_duplicate_pending_cancellation_request_is_blocked(): void
    {
        $booking = $this->makeBooking('pending');

        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), ['reason' => 'pertama']);
        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), ['reason' => 'kedua']);

        $this->assertSame(1, BookingCancellationRequest::where('booking_id', $booking->id)->count());
        $this->assertEquals('pending', $booking->fresh()->status);
    }

    // ── API path ───────────────────────────────────────────────

    public function test_api_tenant_cancel_creates_request_not_cancelled(): void
    {
        $booking = $this->makeBooking('approved');
        $token = $this->tenant->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson("/api/v1/bookings/{$booking->id}/cancel", [
            'reason' => 'via api',
            'status' => 'cancelled',
            'tenant_status' => 'inactive',
        ])->assertOk();

        $this->assertEquals('approved', $booking->fresh()->status);
        $request = BookingCancellationRequest::where('booking_id', $booking->id)->firstOrFail();
        $this->assertSame('pending', $request->status);
    }

    public function test_api_owner_approve_cancellation(): void
    {
        $booking = $this->makeBooking('approved');
        $this->kamar->update(['status' => 'booked']);

        $service = app(BookingStatusService::class);
        $result = $service->requestCancellation($booking, $this->tenant, 'via service');
        $this->assertTrue($result['ok']);
        $request = $result['request'];

        $token = $this->owner->createToken('test')->plainTextToken;
        $this->withToken($token)
            ->postJson("/api/v1/owner/booking-cancellation/{$request->id}/approve")
            ->assertOk();

        $this->assertSame('approved', $request->fresh()->status);
        $this->assertEquals('cancelled', $booking->fresh()->status);
    }

    public function test_api_tenant_cannot_approve_cancellation(): void
    {
        $booking = $this->makeBooking('pending');
        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), ['reason' => 'x']);
        $request = BookingCancellationRequest::where('booking_id', $booking->id)->firstOrFail();

        $token = $this->tenant->createToken('test')->plainTextToken;
        $this->withToken($token)
            ->postJson("/api/v1/owner/booking-cancellation/{$request->id}/approve")
            ->assertForbidden();

        $this->assertSame('pending', $request->fresh()->status);
        $this->assertEquals('pending', $booking->fresh()->status);
    }

    // ── Audit trail ────────────────────────────────────────────

    public function test_cancellation_request_writes_audit_log(): void
    {
        $booking = $this->makeBooking('pending');

        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), [
            'reason' => 'audit test',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'Booking',
        ]);
        $log = AuditLog::where('module', 'Booking')
            ->where('description', 'like', '%mengajukan pembatalan%')
            ->first();
        $this->assertNotNull($log);
    }
}
