<?php

namespace Tests\Feature\Characterization;

use App\Models\DamageReport;
use App\Models\Dispute;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * FASE 0 — KARAKTERISASI DAMAGE REPORT & DISPUTE.
 *
 * DamageReport punya alur status lewat HTTP. Dispute hanya punya route
 * tenant; controller owner ada tetapi tidak terdaftar di routes/web.php.
 * Itu dicatat sebagai perilaku saat ini, bukan diperbaiki di Fase 0.
 */
class DamageDisputeCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $otherOwner;

    private User $admin;

    private User $otherAdmin;

    private User $tenant;

    private User $otherTenant;

    private User $superAdmin;

    private Kos $kos;

    private Kamar $kamar;

    private Penghuni $penghuni;

    protected function setUp(): void
    {
        parent::setUp();

        // Owner pertama sengaja id 1 agar notifikasi lama yang hard-code user 1 tidak error.
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->otherOwner = User::factory()->create(['role' => 'owner']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->otherAdmin = User::factory()->create(['role' => 'admin']);
        $this->tenant = User::factory()->create(['role' => 'pencari']);
        $this->otherTenant = User::factory()->create(['role' => 'pencari']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
        ]);
        $this->admin->assignedKos()->attach($this->kos->id);

        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'occupied',
        ]);
        $this->penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
    }

    public function test_tenant_creates_damage_report_as_pending_and_owner_can_triage(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.damage-reports.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'category' => 'kebocoran',
            'description' => 'Atap kamar bocor saat hujan.',
        ])->assertRedirect();

        $report = DamageReport::firstOrFail();
        $this->assertSame('pending', $report->status);
        $this->assertSame($this->tenant->id, $report->user_id);
        $this->assertNull($report->owner_id);

        $this->actingAs($this->owner)
            ->put(route('owner.damage-reports.update', $report), [
                'status' => 'reviewing',
                'owner_reply' => 'Sedang dijadwalkan teknisi.',
            ])
            ->assertRedirect(route('owner.damage-reports.index'));

        $report->refresh();
        $this->assertSame('reviewing', $report->status);
        $this->assertSame($this->owner->id, $report->owner_id);

        $this->actingAs($this->owner)
            ->put(route('owner.damage-reports.update', $report), [
                'status' => 'resolved',
                'owner_reply' => 'Sudah diperbaiki.',
            ])
            ->assertRedirect();

        $report->refresh();
        $this->assertSame('resolved', $report->status);
        $this->assertNotNull($report->resolved_at);
    }

    public function test_damage_report_visibility_is_scoped_by_owner_admin_and_tenant(): void
    {
        $report = DamageReport::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->tenant)
            ->get(route('tenant.damage-reports.show', $report))
            ->assertOk();

        $this->actingAs($this->owner)
            ->get(route('owner.damage-reports.show', $report))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.damage-reports.show', $report))
            ->assertOk();

        $this->actingAs($this->superAdmin)
            ->get(route('owner.damage-reports.show', $report))
            ->assertOk();

        $this->actingAs($this->otherOwner)
            ->get(route('owner.damage-reports.show', $report))
            ->assertForbidden();

        $this->actingAs($this->otherAdmin)
            ->get(route('admin.damage-reports.show', $report))
            ->assertForbidden();

        $this->actingAs($this->otherTenant)
            ->get(route('tenant.damage-reports.show', $report))
            ->assertForbidden();
    }

    public function test_tenant_can_only_delete_own_pending_damage_report(): void
    {
        $pending = DamageReport::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->tenant)
            ->delete(route('tenant.damage-reports.destroy', $pending))
            ->assertRedirect(route('tenant.damage-reports.index'));

        $this->assertSoftDeleted('damage_reports', ['id' => $pending->id]);

        $resolved = DamageReport::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'resolved',
        ]);

        $this->actingAs($this->tenant)
            ->delete(route('tenant.damage-reports.destroy', $resolved))
            ->assertForbidden();

        $this->assertNotSoftDeleted('damage_reports', ['id' => $resolved->id]);
    }

    public function test_admin_can_create_damage_report_on_behalf_of_tenant_in_assigned_kos(): void
    {
        $this->actingAs($this->admin)->post(route('admin.damage-reports.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'penghuni_id' => $this->penghuni->id,
            'category' => 'listrik',
            'description' => 'Stop kontak kamar tidak berfungsi.',
        ])->assertRedirect();

        $report = DamageReport::firstOrFail();
        $this->assertSame($this->tenant->id, $report->user_id);
        $this->assertSame('pending', $report->status);
    }

    public function test_dispute_creation_and_tenant_visibility(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.dispute.store'), [
            'type' => 'billing',
            'title' => 'Tagihan tidak sesuai',
            'description' => 'Nominal tagihan berbeda dari kontrak.',
        ])->assertRedirect(route('tenant.dispute.index'));

        $dispute = Dispute::firstOrFail();
        $this->assertSame('open', $dispute->status);
        $this->assertSame($this->tenant->id, $dispute->user_id);

        $this->actingAs($this->tenant)
            ->get(route('tenant.dispute.show', $dispute))
            ->assertOk();

        $this->actingAs($this->otherTenant)
            ->get(route('tenant.dispute.show', $dispute))
            ->assertForbidden();
    }

    public function test_dispute_owner_and_admin_routes_are_not_registered_yet(): void
    {
        // karakterisasi, bukan perilaku yang diinginkan.
        $this->assertFalse(Route::has('owner.dispute.index'));
        $this->assertFalse(Route::has('owner.dispute.show'));
        $this->assertFalse(Route::has('owner.dispute.resolve'));
        $this->assertFalse(Route::has('admin.dispute.index'));
        $this->assertFalse(Route::has('admin.dispute.show'));
    }
}
