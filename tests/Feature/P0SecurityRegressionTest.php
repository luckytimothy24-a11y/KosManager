<?php

namespace Tests\Feature;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingPackage;
use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class P0SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    private User $ownerA;
    private User $ownerB;
    private User $adminAssigned;
    private User $adminUnassigned;
    private User $superAdmin;
    private User $tenant;
    private Kos $kosA;
    private Kos $kosB;
    private Kamar $kamarA;
    private Kamar $kamarB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ownerA = User::factory()->create(['role' => 'owner']);
        $this->ownerB = User::factory()->create(['role' => 'owner']);
        $this->adminAssigned = User::factory()->create(['role' => 'admin']);
        $this->adminUnassigned = User::factory()->create(['role' => 'admin']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kosA = Kos::factory()->create(['owner_id' => $this->ownerA->id, 'status' => 'active']);
        $this->kosB = Kos::factory()->create(['owner_id' => $this->ownerB->id, 'status' => 'active']);
        $this->kamarA = Kamar::factory()->create(['kos_id' => $this->kosA->id, 'status' => 'booked', 'capacity' => 2, 'billing_mode' => 'personal']);
        $this->kamarB = Kamar::factory()->create(['kos_id' => $this->kosB->id, 'status' => 'booked', 'capacity' => 2, 'billing_mode' => 'personal']);
        $this->kosA->admins()->attach($this->adminAssigned->id);
    }

    private function confirmedBooking(Kos $kos, Kamar $kamar, User $tenant, User $confirmer): Booking
    {
        return Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test No 1',
            'identity_data_submitted_at' => now(),
            'identity_confirmed_at' => now(),
            'identity_confirmed_by' => $confirmer->id,
            'end_date' => now()->addDays(5)->toDateString(),
        ]);
    }

    private function penghuniInKos(Kos $kos, Kamar $kamar, User $user): Penghuni
    {
        return Penghuni::factory()->create([
            'user_id' => $user->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
    }

    // A. CHECKIN PROCESS

    public function test_owner_can_process_own_booking(): void
    {
        $booking = $this->confirmedBooking($this->kosA, $this->kamarA, $this->tenant, $this->ownerA);
        $this->actingAs($this->ownerA)->post(route('owner.checkin.process', $booking), [
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test No 1',
        ])->assertRedirect(route('owner.booking.index'));
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'completed']);
    }

    public function test_owner_cannot_process_foreign_booking(): void
    {
        $bookingB = $this->confirmedBooking($this->kosB, $this->kamarB, $this->tenant, $this->ownerB);
        $this->actingAs($this->ownerA)->post(route('owner.checkin.process', $bookingB), [
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test No 1',
        ])->assertForbidden();
        $this->assertDatabaseHas('bookings', ['id' => $bookingB->id, 'status' => 'approved']);
    }

    public function test_admin_assigned_can_process(): void
    {
        $booking = $this->confirmedBooking($this->kosA, $this->kamarA, $this->tenant, $this->ownerA);
        $this->actingAs($this->adminAssigned)->post(route('admin.checkin.process', $booking), [
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test No 1',
        ])->assertRedirect(route('admin.booking.index'));
    }

    public function test_admin_unassigned_cannot_process(): void
    {
        $booking = $this->confirmedBooking($this->kosA, $this->kamarA, $this->tenant, $this->ownerA);
        $this->actingAs($this->adminUnassigned)->post(route('admin.checkin.process', $booking), [
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test No 1',
        ])->assertForbidden();
    }

    public function test_tenant_cannot_process(): void
    {
        $booking = $this->confirmedBooking($this->kosA, $this->kamarA, $this->tenant, $this->ownerA);
        $this->actingAs($this->tenant)->post(route('owner.checkin.process', $booking), [
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test No 1',
        ])->assertStatus(403);
    }

    public function test_super_admin_can_process(): void
    {
        $booking = $this->confirmedBooking($this->kosB, $this->kamarB, $this->tenant, $this->ownerB);
        $this->actingAs($this->superAdmin)->post(route('owner.checkin.process', $booking), [
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test No 1',
        ])->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'completed']);
    }

    // B. ADVERTISING REVIEW

    public function test_admin_assigned_can_review_kos_campaign(): void
    {
        $package = AdvertisingPackage::factory()->create();
        $camp = AdvertisingCampaign::factory()->pendingReview()->create([
            'owner_id' => $this->ownerA->id,
            'kos_id' => $this->kosA->id,
            'package_id' => $package->id,
        ]);
        $this->actingAs($this->adminAssigned)->post(route('admin.advertising.approve', $camp))->assertRedirect();
        $this->assertDatabaseHas('advertising_campaigns', ['id' => $camp->id, 'status' => 'active']);
    }

    public function test_admin_cannot_review_foreign_kos_campaign(): void
    {
        $package = AdvertisingPackage::factory()->create();
        $camp = AdvertisingCampaign::factory()->pendingReview()->create([
            'owner_id' => $this->ownerB->id,
            'kos_id' => $this->kosB->id,
            'package_id' => $package->id,
        ]);
        $this->actingAs($this->adminAssigned)->post(route('admin.advertising.approve', $camp))->assertForbidden();
        $this->actingAs($this->adminUnassigned)->post(route('admin.advertising.approve', $camp))->assertForbidden();
    }

    public function test_admin_can_review_global_third_party_campaign(): void
    {
        $package = AdvertisingPackage::factory()->create();
        $camp = AdvertisingCampaign::factory()->pendingReview()->thirdParty('marketplace')->create([
            'owner_id' => $this->ownerB->id,
            'package_id' => $package->id,
        ]);
        $this->actingAs($this->adminUnassigned)->post(route('admin.advertising.approve', $camp))->assertRedirect();
        $this->assertDatabaseHas('advertising_campaigns', ['id' => $camp->id, 'status' => 'active']);
    }

    public function test_tenant_cannot_review_campaign(): void
    {
        $package = AdvertisingPackage::factory()->create();
        $camp = AdvertisingCampaign::factory()->pendingReview()->create([
            'owner_id' => $this->ownerA->id,
            'kos_id' => $this->kosA->id,
            'package_id' => $package->id,
        ]);
        $this->actingAs($this->tenant)->post(route('admin.advertising.approve', $camp))->assertStatus(403);
        $this->actingAs($this->tenant)->post(route('super-admin.advertising.campaigns.approve', $camp))->assertStatus(403);
    }

    public function test_super_admin_can_review_any_campaign(): void
    {
        $package = AdvertisingPackage::factory()->create();
        $camp = AdvertisingCampaign::factory()->pendingReview()->create([
            'owner_id' => $this->ownerB->id,
            'kos_id' => $this->kosB->id,
            'package_id' => $package->id,
        ]);
        $this->actingAs($this->superAdmin)->post(route('super-admin.advertising.campaigns.approve', $camp))->assertRedirect();
    }

    // C. CHECKOUT CANONICAL

    public function test_owner_can_approve_own_checkout(): void
    {
        $ph = $this->penghuniInKos($this->kosA, $this->kamarA, $this->tenant);
        $co = CheckOut::create(['penghuni_id' => $ph->id, 'kamar_id' => $ph->kamar_id, 'request_date' => now(), 'status' => 'pending']);
        $this->kamarA->update(['status' => 'occupied']);
        $this->actingAs($this->ownerA)->post(route('owner.checkout.approve', $co))->assertRedirect(route('owner.checkout.index'));
    }

    public function test_owner_cannot_approve_foreign_checkout(): void
    {
        $phB = $this->penghuniInKos($this->kosB, $this->kamarB, $this->tenant);
        $co = CheckOut::create(['penghuni_id' => $phB->id, 'kamar_id' => $phB->kamar_id, 'request_date' => now(), 'status' => 'pending']);
        $this->actingAs($this->ownerA)->post(route('owner.checkout.approve', $co))->assertForbidden();
        $this->actingAs($this->ownerA)->post(route('owner.checkout.reject', $co))->assertForbidden();
    }

    public function test_admin_assigned_can_approve_checkout(): void
    {
        $ph = $this->penghuniInKos($this->kosA, $this->kamarA, $this->tenant);
        $co = CheckOut::create(['penghuni_id' => $ph->id, 'kamar_id' => $ph->kamar_id, 'request_date' => now(), 'status' => 'pending']);
        $this->kamarA->update(['status' => 'occupied']);
        $this->actingAs($this->adminAssigned)->post(route('admin.checkout.approve', $co))->assertRedirect(route('admin.checkout.index'));
    }

    public function test_admin_unassigned_cannot_approve_checkout(): void
    {
        $phB = $this->penghuniInKos($this->kosB, $this->kamarB, $this->tenant);
        $co = CheckOut::create(['penghuni_id' => $phB->id, 'kamar_id' => $phB->kamar_id, 'request_date' => now(), 'status' => 'pending']);
        $this->actingAs($this->adminUnassigned)->post(route('admin.checkout.approve', $co))->assertForbidden();
        $this->actingAs($this->adminAssigned)->post(route('admin.checkout.approve', $co))->assertForbidden();
    }

    public function test_tenant_cannot_approve_checkout(): void
    {
        $ph = $this->penghuniInKos($this->kosA, $this->kamarA, $this->tenant);
        $co = CheckOut::create(['penghuni_id' => $ph->id, 'kamar_id' => $ph->kamar_id, 'request_date' => now(), 'status' => 'pending']);
        $this->actingAs($this->tenant)->post(route('owner.checkout.approve', $co))->assertStatus(403);
        $this->actingAs($this->tenant)->post(route('admin.checkout.approve', $co))->assertStatus(403);
    }

    public function test_mismatch_kamar_does_not_bypass_penghuni_scope(): void
    {
        $phA = $this->penghuniInKos($this->kosA, $this->kamarA, $this->tenant);
        $mismatch = CheckOut::create(['penghuni_id' => $phA->id, 'kamar_id' => $this->kamarB->id, 'request_date' => now(), 'status' => 'pending']);
        $this->actingAs($this->ownerB)->post(route('owner.checkout.approve', $mismatch))->assertForbidden();
        $this->actingAs($this->ownerA)->post(route('owner.checkout.approve', $mismatch))->assertRedirect();
    }

    public function test_checkout_index_scoped_via_penghuni(): void
    {
        $phA = $this->penghuniInKos($this->kosA, $this->kamarA, $this->tenant);
        $phB = $this->penghuniInKos($this->kosB, $this->kamarB, $this->tenant);
        CheckOut::create(['penghuni_id' => $phA->id, 'kamar_id' => $phA->kamar_id, 'request_date' => now(), 'status' => 'pending']);
        CheckOut::create(['penghuni_id' => $phB->id, 'kamar_id' => $phB->kamar_id, 'request_date' => now(), 'status' => 'pending']);
        $this->actingAs($this->ownerA)->get(route('owner.checkout.index'))->assertOk();
        $this->actingAs($this->adminAssigned)->get(route('admin.checkout.index'))->assertOk();
    }
}
