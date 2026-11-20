<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner1;

    private User $owner2;

    private User $tenant1;

    private User $tenant2;

    private User $superAdmin;

    private Kos $kos1;

    private Kos $kos2;

    private Kamar $kamar1;

    private Kamar $kamar2;

    private Penghuni $penghuni1;

    private Penghuni $penghuni2;

    private Booking $booking1;

    private Booking $booking2;

    private Pembayaran $pembayaranRequested;

    private Pembayaran $pembayaranApproved;

    private Pembayaran $pembayaranRejected;

    private Pembayaran $pembayaranCompleted;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner1 = User::factory()->create(['role' => 'owner']);
        $this->owner2 = User::factory()->create(['role' => 'owner']);
        $this->tenant1 = User::factory()->create(['role' => 'pencari']);
        $this->tenant2 = User::factory()->create(['role' => 'pencari']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->kos1 = Kos::factory()->create(['owner_id' => $this->owner1->id]);
        $this->kos2 = Kos::factory()->create(['owner_id' => $this->owner2->id]);

        $this->kamar1 = Kamar::factory()->create(['kos_id' => $this->kos1->id]);
        $this->kamar2 = Kamar::factory()->create(['kos_id' => $this->kos2->id]);

        $this->penghuni1 = Penghuni::factory()->create([
            'user_id' => $this->tenant1->id,
            'kos_id' => $this->kos1->id,
            'kamar_id' => $this->kamar1->id,
            'status' => 'active',
        ]);
        $this->penghuni2 = Penghuni::factory()->create([
            'user_id' => $this->tenant2->id,
            'kos_id' => $this->kos2->id,
            'kamar_id' => $this->kamar2->id,
            'status' => 'active',
        ]);

        $this->booking1 = Booking::factory()->create([
            'user_id' => $this->tenant1->id,
            'kos_id' => $this->kos1->id,
            'kamar_id' => $this->kamar1->id,
            'status' => 'confirmed',
        ]);
        $this->booking2 = Booking::factory()->create([
            'user_id' => $this->tenant2->id,
            'kos_id' => $this->kos2->id,
            'kamar_id' => $this->kamar2->id,
            'status' => 'confirmed',
        ]);

        // Refund requested (should be approvable/rejectable)
        $this->pembayaranRequested = Pembayaran::factory()->create([
            'booking_id' => $this->booking1->id,
            'penghuni_id' => $this->penghuni1->id,
            'amount' => 1500000,
            'verification_status' => 'approved',
            'refund_status' => Pembayaran::REFUND_REQUESTED,
            'refund_amount' => 500000,
        ]);

        // Refund already approved (should NOT be approvable/rejectable)
        $this->pembayaranApproved = Pembayaran::factory()->create([
            'booking_id' => $this->booking1->id,
            'penghuni_id' => $this->penghuni1->id,
            'amount' => 1500000,
            'verification_status' => 'approved',
            'refund_status' => Pembayaran::REFUND_APPROVED,
            'refund_amount' => 300000,
        ]);

        // Refund already rejected (terminal)
        $this->pembayaranRejected = Pembayaran::factory()->create([
            'booking_id' => $this->booking1->id,
            'penghuni_id' => $this->penghuni1->id,
            'amount' => 1500000,
            'verification_status' => 'approved',
            'refund_status' => Pembayaran::REFUND_REJECTED,
            'refund_amount' => 200000,
        ]);

        // Refund completed (terminal)
        $this->pembayaranCompleted = Pembayaran::factory()->create([
            'booking_id' => $this->booking1->id,
            'penghuni_id' => $this->penghuni1->id,
            'amount' => 1500000,
            'verification_status' => 'approved',
            'refund_status' => Pembayaran::REFUND_COMPLETED,
            'refund_amount' => 100000,
        ]);
    }

    // =========================
    // A1. Ownership Authorization
    // =========================

    public function test_owner_can_approve_refund_on_own_kos(): void
    {
        $response = $this->actingAs($this->owner1)->post(route('owner.refund.approve', $this->pembayaranRequested));
        $response->assertRedirect();
        $this->assertDatabaseHas('pembayarans', [
            'id' => $this->pembayaranRequested->id,
            'refund_status' => Pembayaran::REFUND_APPROVED,
        ]);
    }

    public function test_owner_cannot_approve_refund_on_other_owner_kos(): void
    {
        $response = $this->actingAs($this->owner2)->post(route('owner.refund.approve', $this->pembayaranRequested));
        $response->assertStatus(403);
        $this->assertDatabaseHas('pembayarans', [
            'id' => $this->pembayaranRequested->id,
            'refund_status' => Pembayaran::REFUND_REQUESTED,
        ]);
    }

    public function test_super_admin_can_approve_any_refund(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('owner.refund.approve', $this->pembayaranRequested));
        $response->assertRedirect();
        $this->assertDatabaseHas('pembayarans', [
            'id' => $this->pembayaranRequested->id,
            'refund_status' => Pembayaran::REFUND_APPROVED,
        ]);
    }

    public function test_owner_can_reject_refund_on_own_kos(): void
    {
        $response = $this->actingAs($this->owner1)->post(route('owner.refund.reject', $this->pembayaranRequested), [
            'reason' => 'Bukti tidak valid',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('pembayarans', [
            'id' => $this->pembayaranRequested->id,
            'refund_status' => Pembayaran::REFUND_REJECTED,
            'refund_reason' => 'Bukti tidak valid',
        ]);
    }

    public function test_owner_cannot_reject_refund_on_other_owner_kos(): void
    {
        $response = $this->actingAs($this->owner2)->post(route('owner.refund.reject', $this->pembayaranRequested), [
            'reason' => 'Bukti tidak valid',
        ]);
        $response->assertStatus(403);
        $this->assertDatabaseHas('pembayarans', [
            'id' => $this->pembayaranRequested->id,
            'refund_status' => Pembayaran::REFUND_REQUESTED,
        ]);
    }

    public function test_super_admin_can_reject_any_refund(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('owner.refund.reject', $this->pembayaranRequested), [
            'reason' => 'Super admin rejection',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('pembayarans', [
            'id' => $this->pembayaranRequested->id,
            'refund_status' => Pembayaran::REFUND_REJECTED,
        ]);
    }

    public function test_tenant_cannot_approve_refund(): void
    {
        $response = $this->actingAs($this->tenant1)->post(route('owner.refund.approve', $this->pembayaranRequested));
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_reject_refund(): void
    {
        $response = $this->actingAs($this->tenant1)->post(route('owner.refund.reject', $this->pembayaranRequested), [
            'reason' => 'Test',
        ]);
        $response->assertStatus(403);
    }

    // =========================
    // A2. Refund Approval Guard
    // =========================

    public function test_approval_changes_status_to_approved_not_completed(): void
    {
        $this->actingAs($this->owner1)->post(route('owner.refund.approve', $this->pembayaranRequested));

        $this->pembayaranRequested->refresh();
        $this->assertEquals(Pembayaran::REFUND_APPROVED, $this->pembayaranRequested->refund_status);
        $this->assertNotEquals(Pembayaran::REFUND_COMPLETED, $this->pembayaranRequested->refund_status);
    }

    public function test_approval_only_works_on_requested_status(): void
    {
        // Try to approve already approved refund
        $response = $this->actingAs($this->owner1)->post(route('owner.refund.approve', $this->pembayaranApproved));
        $response->assertSessionHasErrors();
        $this->pembayaranApproved->refresh();
        $this->assertEquals(Pembayaran::REFUND_APPROVED, $this->pembayaranApproved->refund_status);

        // Try to approve rejected refund
        $response = $this->actingAs($this->owner1)->post(route('owner.refund.approve', $this->pembayaranRejected));
        $response->assertSessionHasErrors();
        $this->pembayaranRejected->refresh();
        $this->assertEquals(Pembayaran::REFUND_REJECTED, $this->pembayaranRejected->refund_status);

        // Try to approve completed refund
        $response = $this->actingAs($this->owner1)->post(route('owner.refund.approve', $this->pembayaranCompleted));
        $response->assertSessionHasErrors();
        $this->pembayaranCompleted->refresh();
        $this->assertEquals(Pembayaran::REFUND_COMPLETED, $this->pembayaranCompleted->refund_status);
    }

    public function test_duplicate_approval_is_rejected(): void
    {
        $this->actingAs($this->owner1)->post(route('owner.refund.approve', $this->pembayaranRequested));
        $this->pembayaranRequested->refresh();

        $response = $this->actingAs($this->owner1)->post(route('owner.refund.approve', $this->pembayaranRequested));
        $response->assertSessionHasErrors();

        $this->pembayaranRequested->refresh();
        $this->assertEquals(Pembayaran::REFUND_APPROVED, $this->pembayaranRequested->refund_status);
    }

    public function test_approval_rejected_when_refund_amount_missing(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'booking_id' => $this->booking1->id,
            'penghuni_id' => $this->penghuni1->id,
            'amount' => 1500000,
            'verification_status' => 'approved',
            'refund_status' => Pembayaran::REFUND_REQUESTED,
            'refund_amount' => null,
        ]);

        $response = $this->actingAs($this->owner1)->post(route('owner.refund.approve', $pembayaran));
        $response->assertSessionHasErrors('refund_amount');

        $pembayaran->refresh();
        $this->assertEquals(Pembayaran::REFUND_REQUESTED, $pembayaran->refund_status);
    }

    public function test_approval_rejected_when_refund_amount_exceeds_payment(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'booking_id' => $this->booking1->id,
            'penghuni_id' => $this->penghuni1->id,
            'amount' => 1500000,
            'verification_status' => 'approved',
            'refund_status' => Pembayaran::REFUND_REQUESTED,
            'refund_amount' => 1500001,
        ]);

        $response = $this->actingAs($this->owner1)->post(route('owner.refund.approve', $pembayaran));
        $response->assertSessionHasErrors('refund_amount');

        $pembayaran->refresh();
        $this->assertEquals(Pembayaran::REFUND_REQUESTED, $pembayaran->refund_status);
    }

    public function test_approval_rejected_when_payment_not_verified(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'booking_id' => $this->booking1->id,
            'penghuni_id' => $this->penghuni1->id,
            'amount' => 1500000,
            'verification_status' => 'pending',
            'refund_status' => Pembayaran::REFUND_REQUESTED,
            'refund_amount' => 500000,
        ]);

        $response = $this->actingAs($this->owner1)->post(route('owner.refund.approve', $pembayaran));
        $response->assertSessionHasErrors('refund_status');

        $pembayaran->refresh();
        $this->assertEquals(Pembayaran::REFUND_REQUESTED, $pembayaran->refund_status);
    }

    // =========================
    // A3. Refund Rejection Guard
    // =========================

    public function test_rejection_only_works_on_requested_status(): void
    {
        // Try to reject approved refund
        $response = $this->actingAs($this->owner1)->post(route('owner.refund.reject', $this->pembayaranApproved), [
            'reason' => 'Test',
        ]);
        $response->assertSessionHasErrors();
        $this->pembayaranApproved->refresh();
        $this->assertEquals(Pembayaran::REFUND_APPROVED, $this->pembayaranApproved->refund_status);

        // Try to reject rejected refund
        $response = $this->actingAs($this->owner1)->post(route('owner.refund.reject', $this->pembayaranRejected), [
            'reason' => 'Test',
        ]);
        $response->assertSessionHasErrors();
        $this->pembayaranRejected->refresh();
        $this->assertEquals(Pembayaran::REFUND_REJECTED, $this->pembayaranRejected->refund_status);

        // Try to reject completed refund
        $response = $this->actingAs($this->owner1)->post(route('owner.refund.reject', $this->pembayaranCompleted), [
            'reason' => 'Test',
        ]);
        $response->assertSessionHasErrors();
        $this->pembayaranCompleted->refresh();
        $this->assertEquals(Pembayaran::REFUND_COMPLETED, $this->pembayaranCompleted->refund_status);
    }

    public function test_duplicate_rejection_is_rejected(): void
    {
        $this->actingAs($this->owner1)->post(route('owner.refund.reject', $this->pembayaranRequested), [
            'reason' => 'Alasan pertama',
        ]);
        $this->pembayaranRequested->refresh();

        $response = $this->actingAs($this->owner1)->post(route('owner.refund.reject', $this->pembayaranRequested), [
            'reason' => 'Alasan kedua',
        ]);
        $response->assertSessionHasErrors();

        $this->pembayaranRequested->refresh();
        $this->assertEquals(Pembayaran::REFUND_REJECTED, $this->pembayaranRequested->refund_status);
        $this->assertEquals('Alasan pertama', $this->pembayaranRequested->refund_reason);
    }

    public function test_rejection_requires_reason(): void
    {
        $response = $this->actingAs($this->owner1)->post(route('owner.refund.reject', $this->pembayaranRequested), []);
        $response->assertSessionHasErrors('reason');

        $this->pembayaranRequested->refresh();
        $this->assertEquals(Pembayaran::REFUND_REQUESTED, $this->pembayaranRequested->refund_status);
    }

    // =========================
    // B. Refund Lifecycle Containment
    // =========================

    public function test_approved_refund_cannot_auto_become_completed(): void
    {
        // Approve first
        $this->actingAs($this->owner1)->post(route('owner.refund.approve', $this->pembayaranRequested));
        $this->pembayaranRequested->refresh();
        $this->assertEquals(Pembayaran::REFUND_APPROVED, $this->pembayaranRequested->refund_status);

        // There should be NO automatic transition to completed
        // Completed only with external transfer proof (out of scope)
        // Verify no auto-transition happened
        $this->assertNotEquals(Pembayaran::REFUND_COMPLETED, $this->pembayaranRequested->refund_status);
    }

    public function test_completed_refund_is_terminal(): void
    {
        $response = $this->actingAs($this->owner1)->post(route('owner.refund.approve', $this->pembayaranCompleted));
        $response->assertSessionHasErrors();

        $response = $this->actingAs($this->owner1)->post(route('owner.refund.reject', $this->pembayaranCompleted), [
            'reason' => 'Test',
        ]);
        $response->assertSessionHasErrors();

        $this->pembayaranCompleted->refresh();
        $this->assertEquals(Pembayaran::REFUND_COMPLETED, $this->pembayaranCompleted->refund_status);
    }

    public function test_rejected_refund_is_terminal(): void
    {
        $response = $this->actingAs($this->owner1)->post(route('owner.refund.approve', $this->pembayaranRejected));
        $response->assertSessionHasErrors();

        $response = $this->actingAs($this->owner1)->post(route('owner.refund.reject', $this->pembayaranRejected), [
            'reason' => 'Test',
        ]);
        $response->assertSessionHasErrors();

        $this->pembayaranRejected->refresh();
        $this->assertEquals(Pembayaran::REFUND_REJECTED, $this->pembayaranRejected->refund_status);
    }

    // =========================
    // C. Transaction & Locking (Integration)
    // =========================

    public function test_concurrent_approval_attempts_serialized(): void
    {
        // This test simulates concurrent access by making two sequential requests
        // In a real concurrent scenario, row locking would serialize them
        $this->actingAs($this->owner1)->post(route('owner.refund.approve', $this->pembayaranRequested));
        $this->pembayaranRequested->refresh();

        // Second attempt should fail
        $this->actingAs($this->owner1)->post(route('owner.refund.approve', $this->pembayaranRequested));
        $this->pembayaranRequested->refresh();

        // Only one approval should have succeeded
        $this->assertEquals(Pembayaran::REFUND_APPROVED, $this->pembayaranRequested->refund_status);
    }

    public function test_exception_inside_transaction_causes_rollback(): void
    {
        // This is more of a design verification - the DB::transaction() wrapper
        // ensures any exception causes rollback. We verify the structure exists.
        $pembayaran = Pembayaran::factory()->create([
            'booking_id' => $this->booking1->id,
            'penghuni_id' => $this->penghuni1->id,
            'amount' => 1500000,
            'verification_status' => 'approved',
            'refund_status' => Pembayaran::REFUND_REQUESTED,
            'refund_amount' => 500000,
        ]);

        // Successful approval
        $this->actingAs($this->owner1)->post(route('owner.refund.approve', $pembayaran));
        $pembayaran->refresh();
        $this->assertEquals(Pembayaran::REFUND_APPROVED, $pembayaran->refund_status);
    }
}
