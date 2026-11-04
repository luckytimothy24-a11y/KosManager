<?php

namespace Tests\Feature;

use App\Models\CheckOut;
use App\Models\DamageReport;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TenantCapabilityFollowUpTest extends TestCase
{
    use RefreshDatabase;

    private function makeStay(User $owner, ?User $tenant = null): array
    {
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $tenant ??= User::factory()->create(['role' => 'tenant']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        return [$tenant, $kos, $kamar, $penghuni];
    }

    // ========== P0.1 DAMAGE REPORT ==========

    public function test_tenant_can_create_and_view_own_damage_report(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar] = $this->makeStay($owner);

        $this->actingAs($tenant)->get(route('tenant.damage-reports.create'))->assertOk();
        $this->actingAs($tenant)->get(route('tenant.damage-reports.index'))->assertOk();

        $response = $this->actingAs($tenant)->post(route('tenant.damage-reports.store'), [
            'kos_id' => $kos->id,
            'category' => 'Kamar',
            'description' => 'AC tidak dingin sama sekali.',
        ]);

        $report = DamageReport::where('user_id', $tenant->id)->first();
        $this->assertNotNull($report);
        $response->assertRedirect(route('tenant.damage-reports.show', $report));

        $this->assertSame('pending', $report->status);
        $this->assertSame($kos->id, (int) $report->kos_id);

        $this->actingAs($tenant)->get(route('tenant.damage-reports.show', $report))->assertOk();
    }

    public function test_tenant_cannot_view_or_store_report_for_unrelated_kos(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant] = $this->makeStay($owner);

        $otherOwner = User::factory()->create(['role' => 'owner']);
        $otherKos = Kos::factory()->create(['owner_id' => $otherOwner->id, 'status' => 'active']);
        $otherKamar = Kamar::factory()->create(['kos_id' => $otherKos->id]);
        $otherTenant = User::factory()->create(['role' => 'tenant']);
        $otherReport = DamageReport::factory()->create([
            'kos_id' => $otherKos->id,
            'kamar_id' => $otherKamar->id,
            'user_id' => $otherTenant->id,
        ]);

        // View milik tenant lain → DENY (bukan 500).
        $this->actingAs($tenant)->get(route('tenant.damage-reports.show', $otherReport))->assertForbidden();

        // Store untuk kos tanpa hunian aktif → DENY, tidak ada baris baru.
        $this->actingAs($tenant)->post(route('tenant.damage-reports.store'), [
            'kos_id' => $otherKos->id,
            'category' => 'Kamar',
            'description' => 'Coba bypass kos lain.',
        ])->assertForbidden();
        $this->assertSame(1, DamageReport::count());

        // Mass-assignment: status/user_id kiriman client diabaikan.
        $this->actingAs($tenant)->post(route('tenant.damage-reports.store'), [
            'kos_id' => $otherKos->id,
            'category' => 'Kamar',
            'description' => 'Coba forge.',
            'status' => 'resolved',
            'user_id' => $otherTenant->id,
        ])->assertForbidden();
    }

    public function test_owner_can_moderate_only_own_kos_report(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar] = $this->makeStay($owner);
        $report = DamageReport::factory()->create([
            'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'user_id' => $tenant->id,
        ]);

        $ownerB = User::factory()->create(['role' => 'owner']);
        [$tenantB, $kosB, $kamarB] = $this->makeStay($ownerB);
        $reportB = DamageReport::factory()->create([
            'kos_id' => $kosB->id, 'kamar_id' => $kamarB->id, 'user_id' => $tenantB->id,
        ]);

        $this->actingAs($owner)->get(route('owner.damage-reports.index'))->assertOk();
        $this->actingAs($owner)->get(route('owner.damage-reports.show', $report))->assertOk();
        $this->actingAs($owner)->get(route('owner.damage-reports.show', $reportB))->assertForbidden();

        // Tenant tidak boleh menyentuh endpoint moderasi owner.
        $this->actingAs($tenant)->put(route('owner.damage-reports.update', $report), [
            'status' => 'resolved',
        ])->assertForbidden();

        $this->actingAs($owner)->put(route('owner.damage-reports.update', $report), [
            'status' => 'reviewing',
        ])->assertRedirect(route('owner.damage-reports.index'));
        $this->assertSame('reviewing', $report->fresh()->status);
    }

    public function test_damage_report_policy_is_null_safe(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar] = $this->makeStay($owner);
        $report = DamageReport::factory()->create([
            'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'user_id' => $tenant->id,
        ]);

        // Relasi kos di-unset secara paksa untuk simulasi data yatim.
        $report->setRelation('kos', null);

        $this->assertTrue(Gate::forUser($tenant)->allows('view', $report));
        $stranger = User::factory()->create(['role' => 'tenant']);
        $this->assertFalse(Gate::forUser($stranger)->allows('view', $report));
    }

    // ========== P0.2 BILLING BLOCKED ==========

    public function test_overdue_tenant_redirects_to_valid_billing_destination(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);
        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => Tagihan::STATUS_OVERDUE,
        ]);

        // Jalur billing tetap ALLOW.
        $this->actingAs($tenant)->get(route('tenant.tagihan.index'))->assertOk();

        // Jalur non-billing → redirect ke tujuan billing yang valid (bukan 500).
        $this->actingAs($tenant)->get(route('tenant.booking.index'))
            ->assertRedirect(route('tenant.tagihan.index'));

        // API/JSON → 403 dengan pesan, bukan redirect.
        $this->actingAs($tenant)->getJson(route('tenant.booking.index'))->assertForbidden();
    }

    public function test_non_tenant_is_not_billing_blocked(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner)->get(route('owner.booking.index'))->assertOk();
    }

    // ========== P1.1 LAPORAN ==========

    public function test_tenant_cannot_access_owner_or_admin_reports(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant] = $this->makeStay($owner);

        $this->actingAs($tenant)->get(route('owner.laporan.index'))->assertForbidden();
        $this->actingAs($tenant)->get(route('admin.laporan.index'))->assertForbidden();
        $this->actingAs($tenant)->get(route('super-admin.laporan.index'))->assertForbidden();

        // Owner tetap ALLOW pada laporannya sendiri.
        $this->actingAs($owner)->get(route('owner.laporan.index'))->assertOk();
    }

    // ========== P1.2 MESSAGES ==========

    public function test_tenant_message_scope_and_owner_boundary(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant] = $this->makeStay($owner);

        $response = $this->actingAs($tenant)->post(route('tenant.messages.store'), [
            'message' => 'Lampu kamar mati.',
        ]);
        $response->assertRedirect();
        $this->assertStringContainsString('/tenant/messages', $response->headers->get('Location'));

        // Tenant tanpa hunian aktif tidak bisa mengirim.
        $loner = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($loner)->post(route('tenant.messages.store'), [
            'message' => 'Halo.',
        ])->assertForbidden();

        // Owner lain tidak bisa membaca percakapan ini.
        $ownerB = User::factory()->create(['role' => 'owner']);
        $this->actingAs($ownerB)->get(route('owner.messages.show', $tenant))->assertForbidden();
        $this->actingAs($ownerB)->post(route('owner.messages.reply', $tenant), [
            'message' => 'Halo.',
        ])->assertForbidden();
    }

    // ========== P1.3 CHECKOUT ==========

    public function test_tenant_checkout_request_and_owner_separation(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, , , $penghuni] = $this->makeStay($owner);
        [$tenantB] = $this->makeStay($owner);

        // Tenant request milik sendiri → OK.
        $this->actingAs($tenant)->post(route('tenant.checkout.request', $penghuni), [])
            ->assertSessionHasNoErrors();

        // Tenant request milik tenant lain → DENY.
        $otherPenghuni = Penghuni::where('user_id', $tenantB->id)->first();
        $this->actingAs($tenant)->post(route('tenant.checkout.request', $otherPenghuni), [])
            ->assertForbidden();

        // Tenant tidak boleh approve (bukan super/owner/admin scope).
        $checkOut = CheckOut::where('penghuni_id', $penghuni->id)->first();
        $this->assertNotNull($checkOut);
        $this->actingAs($tenant)->post(route('owner.checkout.approve', $checkOut))->assertForbidden();

        // Owner bisa mengajukan atas nama penghuni kosnya (regression StoreCheckOutRequest).
        $owner2 = User::factory()->create(['role' => 'owner']);
        [$tenant2, , , $penghuni2] = $this->makeStay($owner2);
        $this->actingAs($owner2)->post(route('owner.checkout.request', $penghuni2), [])
            ->assertSessionHasNoErrors();
    }

    // ========== P1.4 PROFILE ==========

    public function test_tenant_can_only_update_own_profile_without_escalation(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($tenant)->patch(route('profile.update'), [
            'name' => 'Nama Baru',
            'email' => $tenant->email,
            'role' => 'super_admin',
        ])->assertRedirect(route('profile.edit'));

        $tenant->refresh();
        $this->assertSame('Nama Baru', $tenant->name);
        $this->assertSame('tenant', $tenant->role);
    }

    // ========== P1.5 PEMBAYARAN ==========

    public function test_tenant_payment_isolation_and_verify_boundary(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
            'total' => 1500000,
            'subtotal' => 1500000,
        ]);

        [$tenantB, , , $penghuniB] = $this->makeStay($owner);
        $tagihanB = Tagihan::factory()->create([
            'penghuni_id' => $penghuniB->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
            'total' => 1500000,
            'subtotal' => 1500000,
        ]);

        // View milik sendiri OK, milik tenant lain DENY.
        $this->actingAs($tenant)->get(route('tenant.pembayaran.index'))->assertOk();
        $pembayaranB = Pembayaran::factory()->create([
            'tagihan_id' => $tagihanB->id,
            'penghuni_id' => $penghuniB->id,
            'amount' => 1500000,
        ]);
        $this->actingAs($tenant)->get(route('tenant.pembayaran.show', $pembayaranB))->assertForbidden();

        // Create untuk tagihan sendiri OK.
        $this->actingAs($tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $tagihan->id,
            'amount' => 1500000,
            'payment_method' => 'qris',
        ])->assertRedirect();

        // Create untuk tagihan tenant lain DENY.
        $this->actingAs($tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $tagihanB->id,
            'amount' => 1500000,
            'payment_method' => 'qris',
        ])->assertForbidden();

        // Tenant tidak boleh verify (hanya owner/admin).
        $pembayaran = Pembayaran::where('tagihan_id', $tagihan->id)->first();
        $this->assertNotNull($pembayaran);
        $this->actingAs($tenant)->post(route('owner.pembayaran.verify', $pembayaran))->assertForbidden();
    }
}
