<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdorBoundaryTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $admin;
    private User $owner;
    private User $ownerB;
    private User $tenant;
    private User $tenantB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->ownerB = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->tenantB = User::factory()->create(['role' => 'tenant']);
    }

    private function makeStay(User $owner): array
    {
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active',
        ]);
        return [$tenant, $kos, $kamar, $penghuni];
    }

    private function assignKosToAdmin(User $admin, Kos $kos): void
    {
        $admin->assignedKos()->attach($kos->id);
    }

    // ========== KOS IDOR ==========

    public function test_owner_a_cannot_access_kos_of_owner_b(): void
    {
        [$_, $kosA] = $this->makeStay($this->owner);
        $kosB = Kos::factory()->create(['owner_id' => $this->ownerB->id]);

        $this->actingAs($this->owner)->get(route('owner.kos.show', $kosB))->assertForbidden();
        $this->actingAs($this->owner)->get(route('owner.kos.edit', $kosB))->assertForbidden();
        $this->actingAs($this->owner)->put(route('owner.kos.update', $kosB), [
            'name' => 'Hacked', 'address' => 'Jl. Hack', 'phone' => '08111111111', 'status' => 'active',
        ])->assertForbidden();
        $this->actingAs($this->owner)->delete(route('owner.kos.destroy', $kosB))->assertForbidden();
    }

    public function test_admin_cannot_access_kos_outside_assignment(): void
    {
        [$_, $kosA] = $this->makeStay($this->owner);
        $kosB = Kos::factory()->create(['owner_id' => $this->ownerB->id]);
        $this->assignKosToAdmin($this->admin, $kosA);

        $this->actingAs($this->admin)->get(route('admin.kos.show', $kosB))->assertForbidden();
    }

    // ========== KAMAR IDOR ==========

public function test_owner_a_cannot_access_kamar_of_owner_b_kos(): void
    {
        [$_, $kosA] = $this->makeStay($this->owner);
        $kosB = Kos::factory()->create(['owner_id' => $this->ownerB->id]);
        $kamarB = Kamar::factory()->create(['kos_id' => $kosB->id]);

        // Owner should be denied from another owner's kamar - policy blocks access
        // Use valid data that passes FormRequest validation so authorization (403) is actually tested
        $this->actingAs($this->owner)->get(route('owner.kamar.show', $kamarB))->assertStatus(403);
        $this->actingAs($this->owner)->put(route('owner.kamar.update', $kamarB), [
            'room_number' => '999',
            'room_name' => 'Hacked',
            'room_type' => 'Standard',
            'daily_price' => 100000,
            'monthly_price' => 1000000,
        ])->assertStatus(403);
    }

    public function test_admin_cannot_access_kamar_outside_assigned_kos(): void
    {
        [$tenantA, $kosA, $kamarA] = $this->makeStay($this->owner);
        [$_, $kosB, $kamarB] = $this->makeStay($this->ownerB);
        $this->assignKosToAdmin($this->admin, $kosA);

        $this->actingAs($this->admin)->get(route('admin.kamar.show', $kamarA))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.kamar.show', $kamarB))->assertForbidden();
    }

    // ========== BOOKING IDOR ==========

    public function test_user_a_cannot_access_booking_of_user_b(): void
    {
        $bookingA = Booking::factory()->create(['user_id' => $this->tenant->id]);
        $bookingB = Booking::factory()->create(['user_id' => $this->tenantB->id]);

        $this->actingAs($this->tenant)->get(route('tenant.booking.show', $bookingA))->assertOk();
        $this->actingAs($this->tenant)->get(route('tenant.booking.show', $bookingB))->assertForbidden();
        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $bookingB))->assertForbidden();
    }

    public function test_owner_cannot_access_booking_of_another_owner(): void
    {
        [$tenantA, $kosA] = $this->makeStay($this->owner);
        $bookingA = Booking::factory()->create(['kos_id' => $kosA->id, 'user_id' => $tenantA->id]);

        [$tenantB, $kosB] = $this->makeStay($this->ownerB);
        $bookingB = Booking::factory()->create(['kos_id' => $kosB->id, 'user_id' => $tenantB->id]);

        $this->actingAs($this->owner)->get(route('owner.booking.show', $bookingA))->assertOk();
        $this->actingAs($this->owner)->get(route('owner.booking.show', $bookingB))->assertForbidden();
    }

    public function test_admin_cannot_access_booking_outside_assigned_kos(): void
    {
        [$tenantA, $kosA, $kamarA] = $this->makeStay($this->owner);
        [$tenantB, $kosB, $kamarB] = $this->makeStay($this->ownerB);
        $bookingA = Booking::factory()->create(['kos_id' => $kosA->id, 'user_id' => $tenantA->id]);
        $bookingB = Booking::factory()->create(['kos_id' => $kosB->id, 'user_id' => $tenantB->id]);
        $this->assignKosToAdmin($this->admin, $kosA);

        $this->actingAs($this->admin)->get(route('admin.booking.show', $bookingA))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.booking.show', $bookingB))->assertForbidden();
    }

    // ========== PENGHUNI IDOR ==========

    public function test_owner_a_cannot_access_penghuni_of_owner_b_kos(): void
    {
        [$tenantA, $kosA, $kamarA, $penghuniA] = $this->makeStay($this->owner);
        [$_, $kosB, $kamarB, $penghuniB] = $this->makeStay($this->ownerB);

        $this->actingAs($this->owner)->get(route('owner.penghuni.show', $penghuniA))->assertOk();
        $this->actingAs($this->owner)->get(route('owner.penghuni.show', $penghuniB))->assertForbidden();
    }

    public function test_admin_cannot_access_penghuni_outside_assigned_kos(): void
    {
        [$tenantA, $kosA, $kamarA, $penghuniA] = $this->makeStay($this->owner);
        [$_, $kosB, $kamarB, $penghuniB] = $this->makeStay($this->ownerB);
        $this->assignKosToAdmin($this->admin, $kosA);

        $this->actingAs($this->admin)->get(route('admin.penghuni.show', $penghuniA))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.penghuni.show', $penghuniB))->assertForbidden();
    }

    // ========== KONTRAK IDOR ==========

    public function test_owner_cannot_access_kontrak_of_another_owner(): void
    {
        [$tenantA, $kosA, $kamarA, $penghuniA] = $this->makeStay($this->owner);
        $kontrakA = Kontrak::factory()->create(['kos_id' => $kosA->id, 'penghuni_id' => $penghuniA->id]);

        [$_, $kosB] = $this->makeStay($this->ownerB);
        $penghuniB = Penghuni::factory()->create(['kos_id' => $kosB->id, 'user_id' => User::factory()->create(['role' => 'tenant'])->id]);
        $kontrakB = Kontrak::factory()->create(['kos_id' => $kosB->id, 'penghuni_id' => $penghuniB->id]);

        $this->actingAs($this->owner)->get(route('owner.kontrak.show', $kontrakA))->assertOk();
        $this->actingAs($this->owner)->get(route('owner.kontrak.show', $kontrakB))->assertForbidden();
    }

    // ========== TAGIHAN IDOR ==========

    public function test_tenant_a_cannot_access_tagihan_of_tenant_b(): void
    {
        [$_, $kos, $kamar] = $this->makeStay($this->owner);
        $penghuniA = Penghuni::factory()->create(['kos_id' => $kos->id, 'user_id' => $this->tenant->id]);
        $tagihanA = Tagihan::factory()->create(['kamar_id' => $kamar->id, 'penghuni_id' => $penghuniA->id]);

        $penghuniB = Penghuni::factory()->create(['kos_id' => $kos->id, 'user_id' => $this->tenantB->id]);
        $tagihanB = Tagihan::factory()->create(['kamar_id' => $kamar->id, 'penghuni_id' => $penghuniB->id]);

        $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $tagihanA))->assertOk();
        $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $tagihanB))->assertForbidden();
    }

    public function test_owner_cannot_access_tagihan_of_another_owner_kos(): void
    {
        [$_, $kosA] = $this->makeStay($this->owner);
        [$_, $kosB] = $this->makeStay($this->ownerB);
        $tagihanA = Tagihan::factory()->create(['kamar_id' => Kamar::factory()->create(['kos_id' => $kosA->id])->id]);
        $tagihanB = Tagihan::factory()->create(['kamar_id' => Kamar::factory()->create(['kos_id' => $kosB->id])->id]);

        $this->actingAs($this->owner)->get(route('owner.tagihan.show', $tagihanA))->assertOk();
        $this->actingAs($this->owner)->get(route('owner.tagihan.show', $tagihanB))->assertForbidden();
    }

    public function test_admin_cannot_access_tagihan_outside_assigned_kos(): void
    {
        [$_, $kosA] = $this->makeStay($this->owner);
        [$_, $kosB] = $this->makeStay($this->ownerB);
        $kamarA = Kamar::factory()->create(['kos_id' => $kosA->id]);
        $kamarB = Kamar::factory()->create(['kos_id' => $kosB->id]);
        $tagihanA = Tagihan::factory()->create(['kamar_id' => $kamarA->id]);
        $tagihanB = Tagihan::factory()->create(['kamar_id' => $kamarB->id]);
        $this->assignKosToAdmin($this->admin, $kosA);

        $this->actingAs($this->admin)->get(route('admin.tagihan.show', $tagihanA))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.tagihan.show', $tagihanB))->assertForbidden();
    }

    // ========== PEMBAYARAN IDOR ==========

    public function test_tenant_a_cannot_access_pembayaran_of_tenant_b(): void
    {
        [$_, $kos, $kamar] = $this->makeStay($this->owner);
        $penghuniA = Penghuni::factory()->create(['kos_id' => $kos->id, 'user_id' => $this->tenant->id]);
        $tagihanA = Tagihan::factory()->create(['kamar_id' => $kamar->id, 'penghuni_id' => $penghuniA->id]);
        $pembayaranA = Pembayaran::factory()->create(['tagihan_id' => $tagihanA->id, 'penghuni_id' => $penghuniA->id]);

        $penghuniB = Penghuni::factory()->create(['kos_id' => $kos->id, 'user_id' => $this->tenantB->id]);
        $tagihanB = Tagihan::factory()->create(['kamar_id' => $kamar->id, 'penghuni_id' => $penghuniB->id]);
        $pembayaranB = Pembayaran::factory()->create(['tagihan_id' => $tagihanB->id, 'penghuni_id' => $penghuniB->id]);

        $this->actingAs($this->tenant)->get(route('tenant.pembayaran.show', $pembayaranA))->assertOk();
        $this->actingAs($this->tenant)->get(route('tenant.pembayaran.show', $pembayaranB))->assertForbidden();
    }

    public function test_owner_can_only_verify_payment_for_own_kos(): void
    {
        [$_, $kosA] = $this->makeStay($this->owner);
        $penghuniA = Penghuni::factory()->create(['kos_id' => $kosA->id, 'user_id' => User::factory()->create(['role' => 'tenant'])->id]);
        $kamarA = Kamar::factory()->create(['kos_id' => $kosA->id]);
        $tagihanA = Tagihan::factory()->create(['kamar_id' => $kamarA->id, 'penghuni_id' => $penghuniA->id, 'total' => 1000000]);
        $pembayaranA = Pembayaran::factory()->create(['tagihan_id' => $tagihanA->id, 'penghuni_id' => $penghuniA->id, 'amount' => 1000000, 'verification_status' => 'pending']);

        [$_, $kosB] = $this->makeStay($this->ownerB);
        $penghuniB = Penghuni::factory()->create(['kos_id' => $kosB->id, 'user_id' => User::factory()->create(['role' => 'tenant'])->id]);
        $kamarB = Kamar::factory()->create(['kos_id' => $kosB->id]);
        $tagihanB = Tagihan::factory()->create(['kamar_id' => $kamarB->id, 'penghuni_id' => $penghuniB->id, 'total' => 1000000]);
        $pembayaranB = Pembayaran::factory()->create(['tagihan_id' => $tagihanB->id, 'penghuni_id' => $penghuniB->id, 'amount' => 1000000, 'verification_status' => 'pending']);

        // Owner can verify own kos payment -> redirect (success) or forbidden if policy denies
        // Owner cannot verify another owner's kos payment -> forbidden
        $this->actingAs($this->owner)->post(route('owner.pembayaran.verify', $pembayaranB))->assertForbidden();
    }

    public function test_admin_cannot_verify_payment_outside_assigned_kos(): void
    {
        [$_, $kosA] = $this->makeStay($this->owner);
        [$_, $kosB] = $this->makeStay($this->ownerB);
        $penghuniA = Penghuni::factory()->create(['kos_id' => $kosA->id, 'user_id' => User::factory()->create(['role' => 'tenant'])->id]);
        $penghuniB = Penghuni::factory()->create(['kos_id' => $kosB->id, 'user_id' => User::factory()->create(['role' => 'tenant'])->id]);
        $kamarA = Kamar::factory()->create(['kos_id' => $kosA->id]);
        $kamarB = Kamar::factory()->create(['kos_id' => $kosB->id]);
        $tagihanA = Tagihan::factory()->create(['kamar_id' => $kamarA->id, 'penghuni_id' => $penghuniA->id, 'total' => 1000000]);
        $tagihanB = Tagihan::factory()->create(['kamar_id' => $kamarB->id, 'penghuni_id' => $penghuniB->id, 'total' => 1000000]);
        $pembayaranA = Pembayaran::factory()->create(['tagihan_id' => $tagihanA->id, 'penghuni_id' => $penghuniA->id, 'amount' => 1000000, 'verification_status' => 'pending']);
        $pembayaranB = Pembayaran::factory()->create(['tagihan_id' => $tagihanB->id, 'penghuni_id' => $penghuniB->id, 'amount' => 1000000, 'verification_status' => 'pending']);
        $this->assignKosToAdmin($this->admin, $kosA);

        // Admin cannot verify unassigned kos payment -> forbidden
        $this->actingAs($this->admin)->post(route('admin.pembayaran.verify', $pembayaranB))->assertForbidden();
    }

// ========== DAMAGE REPORT IDOR ==========

    public function test_tenant_cannot_access_damage_report_of_another_tenant(): void
    {
        [, $kos] = $this->makeStay($this->owner);
        $reportA = \App\Models\DamageReport::factory()->create(['kos_id' => $kos->id, 'user_id' => $this->tenant->id]);
        $reportB = \App\Models\DamageReport::factory()->create(['kos_id' => $kos->id, 'user_id' => User::factory()->create(['role' => 'tenant'])->id]);

        // DamageReportPolicy::view denies access to other tenant's report
        // Verify policy denies via direct policy check
        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($this->tenant)->allows('view', $reportB));
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($this->tenant)->allows('view', $reportA));
    }

    public function test_admin_cannot_access_damage_report_outside_assigned_kos(): void
    {
        [, $kosA] = $this->makeStay($this->owner);
        [, $kosB] = $this->makeStay($this->ownerB);
        $reportA = \App\Models\DamageReport::factory()->create(['kos_id' => $kosA->id]);
        $reportB = \App\Models\DamageReport::factory()->create(['kos_id' => $kosB->id]);
        $this->assignKosToAdmin($this->admin, $kosA);

        // Admin can access assigned kos report, cannot access unassigned
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($this->admin)->allows('view', $reportA));
        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($this->admin)->allows('view', $reportB));
    }

    // ========== MESSAGE IDOR ==========

    public function test_user_cannot_access_message_of_another_user(): void
    {
        $sender = User::factory()->create(['role' => 'tenant']);
        $receiver = User::factory()->create(['role' => 'owner']);
        $message = Message::factory()->create(['sender_id' => $sender->id, 'receiver_id' => $receiver->id]);

        // MessagePolicy::view allows sender and receiver
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($sender)->allows('view', $message));
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($receiver)->allows('view', $message));
        // Third party cannot access
        $stranger = User::factory()->create();
        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($stranger)->allows('view', $message));
    }

    // ========== BOOKING OCCUPANT IDOR ==========

    public function test_tenant_a_cannot_access_occupant_data_of_tenant_b(): void
    {
        [$tenantA, $kos, $kamar, $penghuniA] = $this->makeStay($this->owner);
        $booking = Booking::factory()->create(['kos_id' => $kos->id, 'user_id' => $tenantA->id]);

        [$tenantB, , , $penghuniB] = $this->makeStay($this->ownerB);

        // BookingOccupantPolicy::view denies access for different tenant
        $this->actingAs($this->tenantB)->get(route('owner.booking-occupants.index', ['booking' => $booking]))->assertForbidden();
    }

    // ========== GUEST ACCESS ==========

    public function test_guest_cannot_access_any_protected_resource(): void
    {
        $kos = Kos::factory()->create();
        $booking = Booking::factory()->create(['kos_id' => $kos->id]);

        $this->get(route('owner.kos.show', $kos))->assertRedirect('/login');
        $this->get(route('tenant.booking.show', $booking))->assertRedirect('/login');
        $this->get(route('admin.kamar.index'))->assertRedirect('/login');
    }
}
