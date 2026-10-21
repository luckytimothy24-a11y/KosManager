<?php

namespace Tests\Feature;

use App\Models\CheckOut;
use App\Models\DamageReport;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantSelfServiceTest extends TestCase
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

    // ========== T1 LAPORAN ==========

    public function test_tenant_can_open_personal_laporan(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);
        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
            'total' => 1000000,
            'subtotal' => 1000000,
        ]);

        $response = $this->actingAs($tenant)->get(route('tenant.laporan.index'));
        $response->assertOk();
        $response->assertSee('Laporan Saya');
        $response->assertSee('1.000.000');
    }

    public function test_laporan_excludes_other_tenant_data(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);
        [$tenantB, , , $penghuniB] = $this->makeStay($owner);
        $tagihanB = Tagihan::factory()->create([
            'penghuni_id' => $penghuniB->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
            'total' => 999000,
            'subtotal' => 999000,
            'bill_number' => 'TB-RAHASIA-B',
        ]);

        $response = $this->actingAs($tenant)->get(route('tenant.laporan.index'));
        $response->assertOk();
        $response->assertDontSee('TB-RAHASIA-B');
        $response->assertDontSee('999.000');
    }

    public function test_owner_and_admin_laporan_routes_still_deny_tenant(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant] = $this->makeStay($owner);

        $this->actingAs($tenant)->get(route('owner.laporan.index'))->assertForbidden();
        $this->actingAs($owner)->get(route('tenant.laporan.index'))->assertForbidden();
    }

    // ========== T2 CHECKOUT CANCEL (WEB) ==========

    public function test_tenant_can_cancel_own_pending_checkout(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, , , $penghuni] = $this->makeStay($owner);
        $checkOut = CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $penghuni->kamar_id,
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $this->actingAs($tenant)->post(route('tenant.checkout.cancel', $checkOut))
            ->assertRedirect(route('tenant.kontrak.index'));

        $this->assertSame('cancelled', $checkOut->fresh()->status);
        $this->assertSame('active', $penghuni->fresh()->status);
    }

    public function test_tenant_cannot_cancel_other_tenant_checkout(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant] = $this->makeStay($owner);
        [$tenantB, , , $penghuniB] = $this->makeStay($owner);
        $checkOutB = CheckOut::create([
            'penghuni_id' => $penghuniB->id,
            'kamar_id' => $penghuniB->kamar_id,
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $this->actingAs($tenant)->post(route('tenant.checkout.cancel', $checkOutB))->assertForbidden();
        $this->assertSame('pending', $checkOutB->fresh()->status);
    }

    public function test_tenant_cannot_cancel_final_checkout(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, , , $penghuni] = $this->makeStay($owner);
        $checkOut = CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $penghuni->kamar_id,
            'request_date' => now(),
            'status' => 'approved',
        ]);

        $this->actingAs($tenant)->post(route('tenant.checkout.cancel', $checkOut))->assertStatus(400);
    }

    public function test_kontrak_index_shows_cancel_button_for_pending(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);
        Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $penghuni->kamar_id,
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $this->actingAs($tenant)->get(route('tenant.kontrak.index'))
            ->assertOk()
            ->assertSee('Batalkan Pengajuan');
    }

    // ========== T3 DAMAGE DELETE (WEB) ==========

    public function test_tenant_can_delete_own_pending_report(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar] = $this->makeStay($owner);
        $report = DamageReport::factory()->create([
            'kos_id' => $kos->id, 'kamar_id' => $kamar->id,
            'user_id' => $tenant->id, 'status' => 'pending',
        ]);

        $this->actingAs($tenant)->delete(route('tenant.damage-reports.destroy', $report))
            ->assertRedirect(route('tenant.damage-reports.index'));

        $this->assertSoftDeleted('damage_reports', ['id' => $report->id]);
        $this->actingAs($tenant)->get(route('tenant.damage-reports.show', $report->id))->assertNotFound();
    }

    public function test_tenant_cannot_delete_processed_or_foreign_report(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar] = $this->makeStay($owner);
        $approved = DamageReport::factory()->create([
            'kos_id' => $kos->id, 'kamar_id' => $kamar->id,
            'user_id' => $tenant->id, 'status' => 'approved',
        ]);
        $otherTenant = User::factory()->create(['role' => 'tenant']);
        $foreign = DamageReport::factory()->create([
            'kos_id' => $kos->id, 'kamar_id' => $kamar->id,
            'user_id' => $otherTenant->id, 'status' => 'pending',
        ]);

        $this->actingAs($tenant)->delete(route('tenant.damage-reports.destroy', $approved))->assertForbidden();
        $this->actingAs($tenant)->delete(route('tenant.damage-reports.destroy', $foreign))->assertForbidden();
        $this->assertDatabaseHas('damage_reports', ['id' => $approved->id, 'deleted_at' => null]);
    }

    // ========== T4 API ==========

    public function test_api_checkout_request_and_cancel(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, , , $penghuni] = $this->makeStay($owner);
        Sanctum::actingAs($tenant);

        $this->postJson(route('api.v1.checkout.request', $penghuni), ['notes' => 'Pindah kerja.'])
            ->assertCreated();

        $checkOut = CheckOut::where('penghuni_id', $penghuni->id)->first();
        $this->assertNotNull($checkOut);

        $this->postJson(route('api.v1.checkout.cancel', $checkOut))->assertOk();
        $this->assertSame('cancelled', $checkOut->fresh()->status);

        // Tenant lain ditolak.
        [$tenantB] = $this->makeStay($owner);
        Sanctum::actingAs($tenantB);
        $this->postJson(route('api.v1.checkout.request', $penghuni))->assertForbidden();
    }

    public function test_api_inquiry_flow(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $seeker = User::factory()->create(['role' => 'tenant']);
        Sanctum::actingAs($seeker);

        $this->postJson(route('api.v1.kos.inquiry.store', $kos), ['message' => 'Ada kamar kosong?'])
            ->assertCreated();

        $this->getJson(route('api.v1.inquiries.index'))
            ->assertOk()
            ->assertJsonPath('data.0.kos_id', $kos->id);

        $this->getJson(route('api.v1.inquiries.show', $kos))
            ->assertOk()
            ->assertJsonPath('data.0.message', 'Ada kamar kosong?');
    }

    public function test_api_damage_flow_with_isolation(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos] = $this->makeStay($owner);
        Sanctum::actingAs($tenant);

        $create = $this->postJson(route('api.v1.damage-reports.store'), [
            'kos_id' => $kos->id,
            'category' => 'Kamar',
            'description' => 'AC bocor.',
        ])->assertCreated();
        $id = $create->json('data.id');

        $this->getJson(route('api.v1.damage-reports.index'))->assertOk();
        $this->getJson(route('api.v1.damage-reports.show', $id))->assertOk();
        $this->deleteJson(route('api.v1.damage-reports.destroy', $id))->assertOk();
        $this->assertSoftDeleted('damage_reports', ['id' => $id]);

        // Tenant lain tidak bisa melihat.
        [$tenantB] = $this->makeStay($owner);
        $reportB = DamageReport::factory()->create([
            'kos_id' => $kos->id, 'user_id' => $tenantB->id, 'status' => 'pending',
        ]);
        $this->getJson(route('api.v1.damage-reports.show', $reportB->id))->assertForbidden();
        $this->deleteJson(route('api.v1.damage-reports.destroy', $reportB->id))->assertForbidden();
    }

    public function test_api_rejects_guest_and_owner_on_tenant_routes(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos] = $this->makeStay($owner);

        $this->getJson(route('api.v1.inquiries.index'))->assertUnauthorized();

        Sanctum::actingAs($owner);
        $this->getJson(route('api.v1.inquiries.index'))->assertForbidden();
        $this->getJson(route('api.v1.damage-reports.index'))->assertForbidden();
    }
}
