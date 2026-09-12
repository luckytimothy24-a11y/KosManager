<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * §16 Security Tests — 10 scenarios for new pending -> owner review flow.
 */
class CheckInSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $ownerA;

    private User $ownerB;

    private User $tenantA;

    private User $tenantB;

    private Kos $kosA;

    private Kos $kosB;

    private Kamar $kamarA;

    private Kamar $kamarB;

    private Booking $bookingA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ownerA = User::factory()->create(['role' => 'owner']);
        $this->ownerB = User::factory()->create(['role' => 'owner']);
        $this->tenantA = User::factory()->create(['role' => 'tenant']);
        $this->tenantB = User::factory()->create(['role' => 'tenant']);

        $this->kosA = Kos::factory()->create(['owner_id' => $this->ownerA->id]);
        $this->kosB = Kos::factory()->create(['owner_id' => $this->ownerB->id]);

        $this->kamarA = Kamar::factory()->create(['kos_id' => $this->kosA->id, 'status' => 'booked']);
        $this->kamarB = Kamar::factory()->create(['kos_id' => $this->kosB->id, 'status' => 'booked']);

        $this->bookingA = Booking::factory()->create([
            'user_id' => $this->tenantA->id,
            'kos_id' => $this->kosA->id,
            'kamar_id' => $this->kamarA->id,
            'status' => 'approved',
        ]);
    }

    /** Test 1: Tenant A cannot view data check-in Tenant B. */
    public function test_tenant_a_cannot_view_tenant_b_checkin(): void
    {
        $this->actingAs($this->tenantB)->get(route('tenant.checkin.identity', $this->bookingA))->assertForbidden();
    }

    /** Test 2: Tenant A cannot modify check-in Tenant B. */
    public function test_tenant_a_cannot_modify_tenant_b_checkin(): void
    {
        $this->actingAs($this->tenantB)->post(route('tenant.checkin.identity.confirm', $this->bookingA), [
            'identity_number' => '3374012345670001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
        ])->assertForbidden();

        $this->assertNull($this->bookingA->fresh()->identity_data_submitted_at);
    }

    /** Test 3: Tenant cannot call confirm endpoint directly. */
    public function test_tenant_cannot_call_confirm_endpoint(): void
    {
        // prepare pending so owner could confirm
        $this->bookingA->update([
            'identity_number' => '3374012345670001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
        ]);

        $this->actingAs($this->tenantA)->post(route('owner.checkin.confirm', $this->bookingA))->assertStatus(403);
        $this->actingAs($this->tenantA)->post(route('admin.checkin.confirm', $this->bookingA))->assertStatus(403);
        $this->assertFalse($this->bookingA->fresh()->identityConfirmed());
    }

    /** Test 4: Owner A cannot confirm tenant in owner B scope. */
    public function test_owner_a_cannot_confirm_tenant_of_owner_b(): void
    {
        $bookingB = Booking::factory()->create([
            'user_id' => $this->tenantA->id,
            'kos_id' => $this->kosB->id,
            'kamar_id' => $this->kamarB->id,
            'status' => 'approved',
            'identity_number' => '3374012345670001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
        ]);

        $this->actingAs($this->ownerA)->post(route('owner.checkin.confirm', $bookingB))->assertForbidden();
        $this->assertFalse($bookingB->fresh()->identityConfirmed());
    }

    /** Test 5: Unauthenticated request rejected. */
    public function test_unauthenticated_rejected(): void
    {
        $this->get(route('tenant.checkin.identity', $this->bookingA))->assertRedirect(route('login'));
        $this->post(route('tenant.checkin.identity.confirm', $this->bookingA))->assertRedirect(route('login'));
        $this->post(route('owner.checkin.confirm', $this->bookingA))->assertRedirect(route('login'));
    }

    /** Test 6: Invalid KTP rejected. */
    public function test_invalid_ktp_rejected(): void
    {
        $response = $this->actingAs($this->tenantA)->post(route('tenant.checkin.identity.confirm', $this->bookingA), [
            'identity_number' => 'abc', // too short + non-numeric
            'phone' => '08123456789',
            'address' => 'Jl. Test No 1',
        ]);

        $response->assertSessionHasErrors('identity_number');
        $this->assertNull($this->bookingA->fresh()->identity_data_submitted_at);
    }

    /** Test 7: Invalid WhatsApp rejected. */
    public function test_invalid_whatsapp_rejected(): void
    {
        $response = $this->actingAs($this->tenantA)->post(route('tenant.checkin.identity.confirm', $this->bookingA), [
            'identity_number' => '3374012345670001',
            'phone' => '123', // too short
            'address' => 'Jl. Test No 1',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertNull($this->bookingA->fresh()->identity_data_submitted_at);
    }

    /** Test 8: Already confirmed cannot be confirmed again. */
    public function test_confirmed_cannot_be_reconfirmed(): void
    {
        $this->bookingA->update([
            'identity_number' => '3374012345670001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
            'identity_confirmed_at' => now(),
            'identity_confirmed_by' => $this->ownerA->id,
        ]);

        $this->actingAs($this->ownerA)->post(route('owner.checkin.confirm', $this->bookingA))->assertStatus(400);
    }

    /** Test 9: Reject only on pending state. */
    public function test_reject_only_on_pending(): void
    {
        // not_submitted
        $this->actingAs($this->ownerA)->post(route('owner.checkin.reject', $this->bookingA), ['reason' => 'Data tidak sesuai'])->assertStatus(400);

        // confirmed
        $this->bookingA->update([
            'identity_number' => '3374012345670001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
            'identity_confirmed_at' => now(),
            'identity_confirmed_by' => $this->ownerA->id,
        ]);
        $this->actingAs($this->ownerA)->post(route('owner.checkin.reject', $this->bookingA), ['reason' => 'Data tidak sesuai'])->assertStatus(400);

        // rejected already
        $booking2 = Booking::factory()->create([
            'user_id' => $this->tenantA->id,
            'kos_id' => $this->kosA->id,
            'kamar_id' => $this->kamarA->id,
            'status' => 'approved',
            'identity_number' => '3374012345670001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
            'identity_rejected_at' => now(),
            'identity_rejection_reason' => 'WA salah',
        ]);
        $this->actingAs($this->ownerA)->post(route('owner.checkin.reject', $booking2), ['reason' => 'Coba lagi'])->assertStatus(400);

        // pending should succeed
        $booking3 = Booking::factory()->create([
            'user_id' => $this->tenantA->id,
            'kos_id' => $this->kosA->id,
            'kamar_id' => $this->kamarA->id,
            'status' => 'approved',
            'identity_number' => '3374012345670001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
        ]);
        $this->actingAs($this->ownerA)->post(route('owner.checkin.reject', $booking3), ['reason' => 'Nomor WhatsApp tidak valid'])->assertRedirect();
        $this->assertTrue($booking3->fresh()->identityRejected());
    }

    /** Test 10: Tenant sees updated status after owner confirmation. */
    public function test_tenant_sees_updated_status_after_owner_confirm(): void
    {
        // tenant submits
        $this->actingAs($this->tenantA)->post(route('tenant.checkin.identity.confirm', $this->bookingA), [
            'identity_number' => '3374012345670001',
            'phone' => '08123456789',
            'address' => 'Jl. Test No 1',
        ]);
        $this->assertEquals('pending', $this->bookingA->fresh()->identityStatus());

        // owner confirms
        $this->actingAs($this->ownerA)->post(route('owner.checkin.confirm', $this->bookingA))->assertRedirect();
        $this->assertEquals('confirmed', $this->bookingA->fresh()->identityStatus());

        // tenant view shows confirmed
        $response = $this->actingAs($this->tenantA)->get(route('tenant.checkin.identity', $this->bookingA));
        $response->assertOk();
        $response->assertSee('Check-In Dikonfirmasi');
        $response->assertDontSee('Menunggu Konfirmasi Owner');
        // rejected reason not visible
        $response->assertDontSee('Alasan Penolakan');
    }

    /** Extra: reject reason required. */
    public function test_reject_requires_reason(): void
    {
        $this->bookingA->update([
            'identity_number' => '3374012345670001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
        ]);

        $this->actingAs($this->ownerA)->post(route('owner.checkin.reject', $this->bookingA), ['reason' => 'a'])->assertSessionHasErrors('reason');
        $this->actingAs($this->ownerA)->post(route('owner.checkin.reject', $this->bookingA), [])->assertSessionHasErrors('reason');
    }

    /** Extra: tenant can resubmit after reject, then owner can confirm. */
    public function test_tenant_can_resubmit_after_reject_then_owner_confirm(): void
    {
        $this->bookingA->update([
            'identity_number' => '3374012345670001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
        ]);
        $this->actingAs($this->ownerA)->post(route('owner.checkin.reject', $this->bookingA), ['reason' => 'Nomor WA salah, perbaiki 08...'])->assertRedirect();
        $this->assertEquals('rejected', $this->bookingA->fresh()->identityStatus());

        // tenant resubmit (clears rejected)
        $this->actingAs($this->tenantA)->post(route('tenant.checkin.identity.confirm', $this->bookingA), [
            'identity_number' => '3374012345670002',
            'phone' => '081987654321',
            'address' => 'Jl. Baru No 2',
        ])->assertRedirect();
        $fresh = $this->bookingA->fresh();
        $this->assertEquals('pending', $fresh->identityStatus());
        $this->assertNull($fresh->identity_rejected_at);
        $this->assertNull($fresh->identity_rejection_reason);
        $this->assertEquals('3374012345670002', $fresh->identity_number);

        // owner confirm now succeeds
        $this->actingAs($this->ownerA)->post(route('owner.checkin.confirm', $this->bookingA))->assertRedirect();
        $this->assertEquals('confirmed', $this->bookingA->fresh()->identityStatus());
    }
}
