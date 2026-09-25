<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Kontrak;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTestSuite extends TestCase
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

    private function createKos(User $owner, string $status = 'active'): Kos
    {
        return Kos::factory()->create(['owner_id' => $owner->id, 'status' => $status]);
    }

    private function createKamar(User $owner): Kamar
    {
        $kos = $this->createKos($owner);
        return Kamar::factory()->create(['kos_id' => $kos->id]);
    }

    private function assignKosToAdmin(User $admin, Kos $kos): void
    {
        $admin->assignedKos()->attach($kos->id);
    }

    // ========== USER POLICY ==========

    public function test_super_admin_can_view_any_user(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($this->superAdmin)->get(route('super-admin.users.show', $user))->assertOk();
    }

    public function test_admin_cannot_view_user_detail(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($this->admin)->get(route('super-admin.users.show', $user))->assertForbidden();
    }

    public function test_owner_can_only_see_own_profile(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $other = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($owner)->get(route('super-admin.users.show', $other))->assertForbidden();
        $this->actingAs($owner)->get(route('super-admin.users.show', $owner))->assertOk();
    }

    public function test_admin_cannot_create_user(): void
    {
        $this->actingAs($this->admin)->post(route('super-admin.users.store'), [
            'name' => 'New', 'email' => 'new@test.com', 'role' => 'tenant', 'password' => 'pass123',
        ])->assertForbidden();
    }

    public function test_admin_cannot_delete_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($this->admin)->delete(route('super-admin.users.destroy', $user))->assertForbidden();
    }

    public function test_super_admin_can_manage_roles(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($this->superAdmin)->put(route('super-admin.users.update', $user), [
            'name' => 'Updated', 'email' => $user->email, 'role' => 'owner', 'is_active' => true,
        ])->assertRedirect();
    }

    public function test_admin_cannot_manage_roles(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($this->admin)->put(route('super-admin.users.update', $user), [
            'name' => 'Updated', 'email' => $user->email, 'role' => 'owner', 'is_active' => true,
        ])->assertForbidden();
    }

    public function test_guest_cannot_view_user_list(): void
    {
        $this->get(route('super-admin.users.index'))->assertRedirect('/login');
    }

    // ========== FASILITAS POLICY ==========

    public function test_super_admin_can_manage_any_fasilitas(): void
    {
        $fasilitas = Fasilitas::factory()->create();
        $this->actingAs($this->superAdmin)->get(route('admin.fasilitas.edit', $fasilitas))->assertOk();
    }

    public function test_admin_can_only_see_assigned_kos_fasilitas(): void
    {
        $owner = $this->owner;
        $kos = $this->createKos($owner);
        $fasilitas = Fasilitas::factory()->create();
        $fasilitas->kos()->attach($kos->id);

        $this->assignKosToAdmin($this->admin, $kos);
        $this->actingAs($this->admin)->get(route('admin.fasilitas.edit', $fasilitas))->assertOk();

        $otherKos = $this->createKos($this->ownerB);
        $otherFasilitas = Fasilitas::factory()->create();
        $otherFasilitas->kos()->attach($otherKos->id);
        $this->actingAs($this->admin)->get(route('admin.fasilitas.edit', $otherFasilitas))->assertForbidden();
    }

    public function test_admin_cannot_update_unassigned_fasilitas(): void
    {
        $kos = $this->createKos($this->owner);
        $fasilitas = Fasilitas::factory()->create();
        $fasilitas->kos()->attach($kos->id);

        $otherFasilitas = Fasilitas::factory()->create();
        $otherKos = $this->createKos($this->ownerB);
        $otherFasilitas->kos()->attach($otherKos->id);

        $this->actingAs($this->admin)->put(route('admin.fasilitas.update', $otherFasilitas), [
            'name' => 'Hacked', 'type' => 'kamar',
        ])->assertForbidden();
    }

    public function test_admin_cannot_delete_unassigned_fasilitas(): void
    {
        $fasilitas = Fasilitas::factory()->create();
        $this->actingAs($this->admin)->delete(route('admin.fasilitas.destroy', $fasilitas))->assertForbidden();
    }

    public function test_guest_cannot_access_fasilitas(): void
    {
        $this->get(route('admin.fasilitas.index'))->assertRedirect('/login');
    }

    public function test_tenant_cannot_access_fasilitas(): void
    {
        $this->actingAs($this->tenant)->get(route('admin.fasilitas.index'))->assertForbidden();
    }

    // ========== KOS POLICY ==========

    public function test_owner_can_only_manage_own_kos(): void
    {
        $kos = $this->createKos($this->owner);
        $otherKos = $this->createKos($this->ownerB);

        $this->actingAs($this->owner)->get(route('owner.kos.show', $kos))->assertOk();
        $this->actingAs($this->owner)->get(route('owner.kos.show', $otherKos))->assertForbidden();
    }

    public function test_admin_can_only_manage_assigned_kos(): void
    {
        $kos = $this->createKos($this->owner);
        $otherKos = $this->createKos($this->ownerB);
        $this->assignKosToAdmin($this->admin, $kos);

        $this->actingAs($this->admin)->get(route('admin.kos.show', $kos))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.kos.show', $otherKos))->assertForbidden();
    }

    public function test_tenant_can_access_own_kos_marketplace(): void
    {
        $kos = $this->createKos($this->owner);
        $this->actingAs($this->tenant)->get(route('tenant.kos.show', $kos))->assertOk();
    }

    // ========== KAMAR POLICY ==========

    public function test_admin_can_only_manage_assigned_kamar(): void
    {
        $kos = $this->createKos($this->owner);
        $this->assignKosToAdmin($this->admin, $kos);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);

        $otherKos = $this->createKos($this->ownerB);
        $otherKamar = Kamar::factory()->create(['kos_id' => $otherKos->id]);

        $this->actingAs($this->admin)->get(route('admin.kamar.show', $kamar))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.kamar.show', $otherKamar))->assertForbidden();
    }

    public function test_owner_can_only_manage_own_kamar(): void
    {
        $kos = $this->createKos($this->owner);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);

        $otherKos = $this->createKos($this->ownerB);
        $otherKamar = Kamar::factory()->create(['kos_id' => $otherKos->id]);

        $this->actingAs($this->owner)->get(route('owner.kamar.show', $kamar))->assertOk();
        $this->actingAs($this->owner)->get(route('owner.kamar.show', $otherKamar))->assertForbidden();
    }

    public function test_tenant_can_view_available_kamar(): void
    {
        $kos = $this->createKos($this->owner);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $this->actingAs($this->tenant)->get(route('tenant.kamar.show', $kamar))->assertOk();
    }

    public function test_tenant_cannot_view_maintenance_kamar(): void
    {
        $kos = $this->createKos($this->owner);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'maintenance']);
        $this->actingAs($this->tenant)->get(route('tenant.kamar.show', $kamar))->assertForbidden();
    }

    // ========== BOOKING POLICY ==========

    public function test_tenant_can_only_see_own_booking(): void
    {
        $booking = Booking::factory()->create(['user_id' => $this->tenant->id]);
        $otherBooking = Booking::factory()->create(['user_id' => $this->tenantB->id]);

        $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking))->assertOk();
        $this->actingAs($this->tenant)->get(route('tenant.booking.show', $otherBooking))->assertForbidden();
    }

    public function test_owner_can_see_bookings_for_own_kos(): void
    {
        $kos = $this->createKos($this->owner);
        $booking = Booking::factory()->create(['kos_id' => $kos->id]);
        $otherKos = $this->createKos($this->ownerB);
        $otherBooking = Booking::factory()->create(['kos_id' => $otherKos->id]);

        $this->actingAs($this->owner)->get(route('owner.booking.show', $booking))->assertOk();
        $this->actingAs($this->owner)->get(route('owner.booking.show', $otherBooking))->assertForbidden();
    }

    public function test_admin_can_see_bookings_for_assigned_kos(): void
    {
        $kos = $this->createKos($this->owner);
        $this->assignKosToAdmin($this->admin, $kos);
        $booking = Booking::factory()->create(['kos_id' => $kos->id]);

        $otherKos = $this->createKos($this->ownerB);
        $otherBooking = Booking::factory()->create(['kos_id' => $otherKos->id]);

        $this->actingAs($this->admin)->get(route('admin.booking.show', $booking))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.booking.show', $otherBooking))->assertForbidden();
    }

    public function test_tenant_can_only_cancel_own_booking(): void
    {
        $booking = Booking::factory()->create(['user_id' => $this->tenant->id, 'status' => 'approved']);
        $otherBooking = Booking::factory()->create(['user_id' => $this->tenantB->id, 'status' => 'approved']);

        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking))->assertRedirect();
        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $otherBooking))->assertForbidden();
    }

    // ========== PENGHUNI POLICY ==========

    public function test_owner_can_see_penghuni_for_own_kos(): void
    {
        $kos = $this->createKos($this->owner);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create(['kos_id' => $kos->id, 'kamar_id' => $kamar->id]);

        $otherKos = $this->createKos($this->ownerB);
        $otherPenghuni = Penghuni::factory()->create(['kos_id' => $otherKos->id]);

        $this->actingAs($this->owner)->get(route('owner.penghuni.show', $penghuni))->assertOk();
        $this->actingAs($this->owner)->get(route('owner.penghuni.show', $otherPenghuni))->assertForbidden();
    }

    // ========== KONTRAK POLICY ==========

    public function test_owner_can_see_kontrak_for_own_kos(): void
    {
        $kos = $this->createKos($this->owner);
        $penghuni = Penghuni::factory()->create(['kos_id' => $kos->id]);
        $kontrak = Kontrak::factory()->create(['kos_id' => $kos->id, 'penghuni_id' => $penghuni->id]);

        $otherKos = $this->createKos($this->ownerB);
        $otherKontrak = Kontrak::factory()->create(['kos_id' => $otherKos->id]);

        $this->actingAs($this->owner)->get(route('owner.kontrak.show', $kontrak))->assertOk();
        $this->actingAs($this->owner)->get(route('owner.kontrak.show', $otherKontrak))->assertForbidden();
    }

    // ========== TAGIHAN POLICY ==========

    public function test_tenant_can_only_see_own_tagihan(): void
    {
        $kos = $this->createKos($this->owner);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create(['kos_id' => $kos->id, 'user_id' => $this->tenant->id]);
        $tagihan = Tagihan::factory()->create(['kamar_id' => $kamar->id, 'penghuni_id' => $penghuni->id]);

        $otherTenant = Penghuni::factory()->create(['kos_id' => $kos->id, 'user_id' => $this->tenantB->id]);
        $otherTagihan = Tagihan::factory()->create(['kamar_id' => $kamar->id, 'penghuni_id' => $otherTenant->id]);

        $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $tagihan))->assertOk();
        $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $otherTagihan))->assertForbidden();
    }

    // ========== PEMBAYARAN POLICY ==========

    public function test_tenant_can_only_see_own_pembayaran(): void
    {
        $kos = $this->createKos($this->owner);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create(['kos_id' => $kos->id, 'user_id' => $this->tenant->id]);
        $tagihan = Tagihan::factory()->create(['kamar_id' => $kamar->id, 'penghuni_id' => $penghuni->id]);
        $pembayaran = \App\Models\Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id]);

        $otherTenant = Penghuni::factory()->create(['kos_id' => $kos->id, 'user_id' => $this->tenantB->id]);
        $otherPembayaran = \App\Models\Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $otherTenant->id]);

        $this->actingAs($this->tenant)->get(route('tenant.pembayaran.show', $pembayaran))->assertOk();
        $this->actingAs($this->tenant)->get(route('tenant.pembayaran.show', $otherPembayaran))->assertForbidden();
    }

    public function test_owner_can_verify_only_own_kos_payment(): void
    {
        $kos = $this->createKos($this->owner);
        $kosB = $this->createKos($this->ownerB);
        $penghuni = Penghuni::factory()->create(['kos_id' => $kos->id]);
        $tagihan = Tagihan::factory()->create(['kamar_id' => Kamar::factory()->create(['kos_id' => $kos->id])->id, 'penghuni_id' => $penghuni->id]);
        $pembayaran = \App\Models\Pembayaran::factory()->create(['tagihan_id' => $tagihan->id]);

        $this->actingAs($this->owner)->post(route('owner.pembayaran.verify', $pembayaran))->assertOk();

        $otherPenghuni = Penghuni::factory()->create(['kos_id' => $kosB->id]);
        $otherTagihan = Tagihan::factory()->create(['kamar_id' => Kamar::factory()->create(['kos_id' => $kosB->id])->id, 'penghuni_id' => $otherPenghuni->id]);
        $otherPembayaran = \App\Models\Pembayaran::factory()->create(['tagihan_id' => $otherTagihan->id]);

        $this->actingAs($this->owner)->post(route('owner.pembayaran.verify', $otherPembayaran))->assertForbidden();
    }

    // ========== DAMAGE REPORT POLICY ==========

    public function test_tenant_can_only_see_own_damage_report(): void
    {
        $kos = $this->createKos($this->owner);
        $report = \App\Models\DamageReport::factory()->create(['kos_id' => $kos->id, 'user_id' => $this->tenant->id]);
        $otherReport = \App\Models\DamageReport::factory()->create(['kos_id' => $kos->id, 'user_id' => $this->tenantB->id]);

        $this->actingAs($this->tenant)->get(route('tenant.damage-reports.show', $report))->assertOk();
        $this->actingAs($this->tenant)->get(route('tenant.damage-reports.show', $otherReport))->assertForbidden();
    }

    // ========== MESSAGE POLICY ==========

    public function test_user_can_only_see_own_messages(): void
    {
        $sender = User::factory()->create(['role' => 'tenant']);
        $receiver = User::factory()->create(['role' => 'owner']);
        $message = \App\Models\Message::factory()->create(['sender_id' => $sender->id, 'receiver_id' => $receiver->id]);

        $this->actingAs($sender)->get(route('messages.show', $receiver))->assertOk();
        $this->actingAs($receiver)->get(route('messages.show', $sender))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('messages.show', $receiver))->assertForbidden();
    }
}
