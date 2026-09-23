<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkflowCoverageTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $admin;
    private User $tenant;
    private Kos $assignedKos;
    private Kos $foreignKos;
    private Kamar $assignedKamar;
    private Kamar $foreignKamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);

        $this->assignedKos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->foreignKos = Kos::factory()->create(['owner_id' => $this->owner->id]);

        $this->admin->assignedKos()->attach($this->assignedKos->id);

        $this->assignedKamar = Kamar::factory()->create([
            'kos_id' => $this->assignedKos->id,
            'status' => 'available',
            'capacity' => 1,
        ]);
        $this->foreignKamar = Kamar::factory()->create([
            'kos_id' => $this->foreignKos->id,
            'status' => 'available',
            'capacity' => 1,
        ]);
    }

    // ─── HELPER: makeStay ────────────────────────────────────

    private function makeStay(Kos $kos, Kamar $kamar, User $tenant): array
    {
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        return [$penghuni, $kontrak];
    }

    // ─── P0-1: BOOKING INDEX ──────────────────────────────────

    public function test_admin_assigned_can_view_booking_index(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->assignedKos->id,
            'kamar_id' => $this->assignedKamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.booking.index'));
        $response->assertOk();
        $response->assertSee($booking->booking_code);
    }

    public function test_admin_booking_index_excludes_foreign_kos(): void
    {
        $assignedBooking = Booking::factory()->create([
            'kos_id' => $this->assignedKos->id,
            'kamar_id' => $this->assignedKamar->id,
            'status' => 'approved',
        ]);
        $foreignBooking = Booking::factory()->create([
            'kos_id' => $this->foreignKos->id,
            'kamar_id' => $this->foreignKamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.booking.index'));
        $response->assertOk();
        $response->assertSee($assignedBooking->booking_code);
        $response->assertDontSee($foreignBooking->booking_code);
    }

    // ─── P0-2: PENGHUNI INDEX / SHOW ──────────────────────────

    public function test_admin_assigned_can_view_penghuni_index(): void
    {
        [$penghuni] = $this->makeStay($this->assignedKos, $this->assignedKamar, $this->tenant);

        $response = $this->actingAs($this->admin)->get(route('admin.penghuni.index'));
        $response->assertOk();
        $response->assertSee($this->tenant->name);
    }

    public function test_admin_penghuni_index_excludes_foreign_kos(): void
    {
        $assignedTenant = User::factory()->create(['role' => 'tenant']);
        [$penghuniAssigned] = $this->makeStay($this->assignedKos, $this->assignedKamar, $assignedTenant);

        $foreignTenant = User::factory()->create(['role' => 'tenant']);
        [$penghuniForeign] = $this->makeStay($this->foreignKos, $this->foreignKamar, $foreignTenant);

        $response = $this->actingAs($this->admin)->get(route('admin.penghuni.index'));
        $response->assertOk();
        $response->assertSee($assignedTenant->name);
        $response->assertDontSee($foreignTenant->name);
    }

    public function test_admin_assigned_can_view_penghuni_show(): void
    {
        [$penghuni] = $this->makeStay($this->assignedKos, $this->assignedKamar, $this->tenant);

        $response = $this->actingAs($this->admin)->get(route('admin.penghuni.show', $penghuni));
        $response->assertOk();
    }

    public function test_admin_foreign_penghuni_denied_show(): void
    {
        $foreignTenant = User::factory()->create(['role' => 'tenant']);
        [$penghuniForeign] = $this->makeStay($this->foreignKos, $this->foreignKamar, $foreignTenant);

        $response = $this->actingAs($this->admin)->get(route('admin.penghuni.show', $penghuniForeign));
        $response->assertForbidden();
    }

    // ─── P0-3: KONTRAK INDEX / SHOW ───────────────────────────

    public function test_admin_assigned_can_view_kontrak_index(): void
    {
        [$penghuni, $kontrak] = $this->makeStay($this->assignedKos, $this->assignedKamar, $this->tenant);

        $response = $this->actingAs($this->admin)->get(route('admin.kontrak.index'));
        $response->assertOk();
        $response->assertSee($kontrak->contract_number);
    }

    public function test_admin_kontrak_index_excludes_foreign_kos(): void
    {
        $assignedTenant = User::factory()->create(['role' => 'tenant']);
        [$penghuniA, $kontrakA] = $this->makeStay($this->assignedKos, $this->assignedKamar, $assignedTenant);

        $foreignTenant = User::factory()->create(['role' => 'tenant']);
        [$penghuniB, $kontrakB] = $this->makeStay($this->foreignKos, $this->foreignKamar, $foreignTenant);

        $response = $this->actingAs($this->admin)->get(route('admin.kontrak.index'));
        $response->assertOk();
        $response->assertSee($kontrakA->contract_number);
        $response->assertDontSee($kontrakB->contract_number);
    }

    public function test_admin_assigned_can_view_kontrak_show(): void
    {
        [$penghuni, $kontrak] = $this->makeStay($this->assignedKos, $this->assignedKamar, $this->tenant);

        $response = $this->actingAs($this->admin)->get(route('admin.kontrak.show', $kontrak));
        $response->assertOk();
    }

    public function test_admin_foreign_kontrak_denied_show(): void
    {
        $foreignTenant = User::factory()->create(['role' => 'tenant']);
        [$penghuniB, $kontrakB] = $this->makeStay($this->foreignKos, $this->foreignKamar, $foreignTenant);

        $response = $this->actingAs($this->admin)->get(route('admin.kontrak.show', $kontrakB));
        $response->assertForbidden();
    }

    // ─── P0-4: CHECK-IN CONFIRM / REJECT ──────────────────────

    public function test_admin_assigned_can_confirm_checkin_identity(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->assignedKos->id, 'status' => 'booked', 'capacity' => 1]);
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->assignedKos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
            'identity_confirmed_at' => null,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.checkin.confirm', $booking));
        $response->assertRedirect();
        $this->assertNotNull($booking->fresh()->identity_confirmed_at);
    }

    public function test_admin_unassigned_cannot_confirm_checkin_identity(): void
    {
        $unassignedAdmin = User::factory()->create(['role' => 'admin']);
        $kamar = Kamar::factory()->create(['kos_id' => $this->assignedKos->id, 'status' => 'booked', 'capacity' => 1]);
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->assignedKos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'identity_number' => '3201234567890002',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
        ]);

        $response = $this->actingAs($unassignedAdmin)->post(route('admin.checkin.confirm', $booking));
        $response->assertForbidden();
    }

    public function test_admin_assigned_can_reject_checkin_identity(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->assignedKos->id, 'status' => 'booked', 'capacity' => 1]);
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->assignedKos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'identity_number' => '3201234567890003',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
            'identity_confirmed_at' => null,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.checkin.reject', $booking), [
            'reason' => 'Data identitas tidak valid',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
        $this->assertNotNull($booking->fresh()->identity_rejected_at);
    }

    public function test_admin_unassigned_cannot_reject_checkin_identity(): void
    {
        $unassignedAdmin = User::factory()->create(['role' => 'admin']);
        $kamar = Kamar::factory()->create(['kos_id' => $this->assignedKos->id, 'status' => 'booked', 'capacity' => 1]);
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->assignedKos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'identity_number' => '3201234567890004',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
        ]);

        $response = $this->actingAs($unassignedAdmin)->post(route('admin.checkin.reject', $booking), [
            'reason' => 'Data identitas tidak valid karena format KTP salah',
        ]);
        $response->assertForbidden();
    }

    // ─── P1-1: TAGIHAN CREATION ──────────────────────────────

    public function test_admin_assigned_can_create_tagihan(): void
    {
        [$penghuni, $kontrak] = $this->makeStay($this->assignedKos, $this->assignedKamar, $this->tenant);

        $response = $this->actingAs($this->admin)->post(route('admin.tagihan.store'), [
            'kontrak_id' => $kontrak->id,
            'bill_type' => 'rent',
            'period_start' => now()->startOfMonth()->format('Y-m-d'),
            'period_end' => now()->endOfMonth()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('admin.tagihan.index'));
        $this->assertDatabaseHas('tagihans', [
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
        ]);
    }

    public function test_admin_foreign_kos_cannot_create_tagihan(): void
    {
        $foreignTenant = User::factory()->create(['role' => 'tenant']);
        [$penghuniB, $kontrakB] = $this->makeStay($this->foreignKos, $this->foreignKamar, $foreignTenant);

        $response = $this->actingAs($this->admin)->post(route('admin.tagihan.store'), [
            'kontrak_id' => $kontrakB->id,
            'bill_type' => 'rent',
            'period_start' => now()->startOfMonth()->format('Y-m-d'),
            'period_end' => now()->endOfMonth()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertForbidden();
    }

    // ─── P1-2: PEMBAYARAN SHOW / REJECT ──────────────────────

    public function test_admin_assigned_can_view_pembayaran(): void
    {
        [$penghuni, $kontrak] = $this->makeStay($this->assignedKos, $this->assignedKamar, $this->tenant);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $this->assignedKamar->id,
            'status' => 'unpaid',
            'total' => 1000000,
        ]);

        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 1000000,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.pembayaran.show', $pembayaran));
        $response->assertOk();
    }

    public function test_admin_foreign_pembayaran_denied_show(): void
    {
        $foreignTenant = User::factory()->create(['role' => 'tenant']);
        [$penghuniB, $kontrakB] = $this->makeStay($this->foreignKos, $this->foreignKamar, $foreignTenant);

        $tagihanB = Tagihan::factory()->create([
            'penghuni_id' => $penghuniB->id,
            'kontrak_id' => $kontrakB->id,
            'kamar_id' => $this->foreignKamar->id,
            'status' => 'unpaid',
            'total' => 1000000,
        ]);

        $pembayaranB = Pembayaran::factory()->create([
            'tagihan_id' => $tagihanB->id,
            'penghuni_id' => $penghuniB->id,
            'amount' => 1000000,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.pembayaran.show', $pembayaranB));
        $response->assertForbidden();
    }

    public function test_admin_assigned_can_reject_pembayaran(): void
    {
        [$penghuni, $kontrak] = $this->makeStay($this->assignedKos, $this->assignedKamar, $this->tenant);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $this->assignedKamar->id,
            'status' => 'unpaid',
            'total' => 1000000,
        ]);

        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 1000000,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.pembayaran.reject', $pembayaran), [
            'reason' => 'Bukti pembayaran tidak jelas',
        ]);

        $response->assertRedirect(route('admin.pembayaran.index'));
        $this->assertDatabaseHas('pembayarans', ['id' => $pembayaran->id, 'verification_status' => 'rejected']);
    }

    public function test_admin_unassigned_cannot_reject_pembayaran(): void
    {
        $unassignedAdmin = User::factory()->create(['role' => 'admin']);

        [$penghuni, $kontrak] = $this->makeStay($this->assignedKos, $this->assignedKamar, $this->tenant);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $this->assignedKamar->id,
            'status' => 'unpaid',
            'total' => 1000000,
        ]);

        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 1000000,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($unassignedAdmin)->post(route('admin.pembayaran.reject', $pembayaran), [
            'reason' => 'Bukti tidak valid karena format salah',
        ]);

        $response->assertForbidden();
    }

    // ─── P1-3: CHECK-OUT REJECT ──────────────────────────────

    public function test_admin_assigned_can_reject_checkout(): void
    {
        [$penghuni] = $this->makeStay($this->assignedKos, $this->assignedKamar, $this->tenant);

        $checkOut = CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $this->assignedKamar->id,
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.checkout.reject', $checkOut));
        $response->assertRedirect(route('admin.checkout.index'));
        $this->assertDatabaseHas('check_outs', ['id' => $checkOut->id, 'status' => 'rejected']);
    }

    public function test_admin_unassigned_cannot_reject_checkout(): void
    {
        $unassignedAdmin = User::factory()->create(['role' => 'admin']);

        [$penghuni] = $this->makeStay($this->assignedKos, $this->assignedKamar, $this->tenant);

        $checkOut = CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $this->assignedKamar->id,
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($unassignedAdmin)->post(route('admin.checkout.reject', $checkOut));
        $response->assertForbidden();
    }

    // ─── P1-4: CHECK-OUT REQUEST ─────────────────────────────

    public function test_admin_assigned_can_request_checkout(): void
    {
        [$penghuni] = $this->makeStay($this->assignedKos, $this->assignedKamar, $this->tenant);

        $response = $this->actingAs($this->admin)->post(route('admin.checkout.request', $penghuni));
        $response->assertRedirect();
        $this->assertDatabaseHas('check_outs', [
            'penghuni_id' => $penghuni->id,
            'status' => 'pending',
        ]);
    }

    public function test_admin_unassigned_cannot_request_checkout(): void
    {
        $unassignedAdmin = User::factory()->create(['role' => 'admin']);

        [$penghuni] = $this->makeStay($this->assignedKos, $this->assignedKamar, $this->tenant);

        $response = $this->actingAs($unassignedAdmin)->post(route('admin.checkout.request', $penghuni));
        $response->assertForbidden();
    }

    // ─── P1-5: ADVERTISING REJECT / SUSPEND POSITIVE ─────────

    public function test_admin_assigned_can_reject_campaign(): void
    {
        $package = \App\Models\AdvertisingPackage::factory()->create(['price' => 100000, 'is_active' => true]);
        $campaign = \App\Models\AdvertisingCampaign::factory()->create([
            'owner_id' => $this->owner->id,
            'kos_id' => $this->assignedKos->id,
            'package_id' => $package->id,
            'status' => 'pending_review',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.advertising.reject', $campaign), [
            'reason' => 'Konten tidak sesuai kebijakan platform',
        ]);

        $response->assertRedirect(route('admin.advertising.index'));
        $this->assertDatabaseHas('advertising_campaigns', ['id' => $campaign->id, 'status' => 'rejected']);
    }

    public function test_admin_assigned_can_suspend_active_campaign(): void
    {
        $package = \App\Models\AdvertisingPackage::factory()->create(['price' => 100000, 'is_active' => true]);
        $campaign = \App\Models\AdvertisingCampaign::factory()->create([
            'owner_id' => $this->owner->id,
            'kos_id' => $this->assignedKos->id,
            'package_id' => $package->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.advertising.suspend', $campaign), [
            'reason' => 'Melanggar kebijakan konten iklan',
        ]);

        $this->assertDatabaseHas('advertising_campaigns', ['id' => $campaign->id, 'status' => 'suspended']);
    }

    public function test_admin_unassigned_cannot_reject_foreign_campaign(): void
    {
        $package = \App\Models\AdvertisingPackage::factory()->create(['price' => 100000, 'is_active' => true]);
        $campaign = \App\Models\AdvertisingCampaign::factory()->create([
            'owner_id' => $this->owner->id,
            'kos_id' => $this->foreignKos->id,
            'package_id' => $package->id,
            'status' => 'pending_review',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.advertising.reject', $campaign), [
            'reason' => 'Test reject foreign',
        ]);

        $response->assertForbidden();
    }

    // ─── DESTROY REDIRECT REGRESSION (Phase D) ───────────────

    public function test_admin_delete_empty_kos_redirects_to_admin_index(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->admin->assignedKos()->attach($kos->id);

        $response = $this->actingAs($this->admin)->delete(route('admin.kos.destroy', $kos));
        $response->assertRedirect(route('admin.kos.index'));

        $this->assertSoftDeleted('kos', ['id' => $kos->id]);
    }

    public function test_owner_delete_empty_kos_still_redirects_to_owner_index(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->actingAs($this->owner)->delete(route('owner.kos.destroy', $kos));
        $response->assertRedirect(route('owner.kos.index'));

        $this->assertSoftDeleted('kos', ['id' => $kos->id]);
    }

    public function test_admin_cannot_delete_foreign_kos(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.kos.destroy', $this->foreignKos));
        $response->assertForbidden();

        $this->assertDatabaseHas('kos', ['id' => $this->foreignKos->id]);
    }

    // ─── SUPER ADMIN BOUNDARY ────────────────────────────────

    public function test_super_admin_retains_global_access_on_new_routes(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        [$penghuni] = $this->makeStay($this->assignedKos, $this->assignedKamar, $this->tenant);

        $response = $this->actingAs($superAdmin)->get(route('admin.penghuni.index'));
        $response->assertOk();

        $response = $this->actingAs($superAdmin)->get(route('admin.kontrak.index'));
        $response->assertOk();

        $response = $this->actingAs($superAdmin)->get(route('admin.booking.index'));
        $response->assertOk();
    }
}
