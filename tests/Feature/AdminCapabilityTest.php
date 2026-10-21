<?php

namespace Tests\Feature;

use App\Models\CheckOut;
use App\Models\DamageReport;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminCapabilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeStay(User $owner): array
    {
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        return [$tenant, $kos, $kamar, $penghuni];
    }

    private function makeAdminFor(Kos $kos): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignedKos()->attach($kos->id);

        return $admin;
    }

    // ========== A1 MESSAGES READ-ONLY ==========

    public function test_admin_can_read_scoped_conversation(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos] = $this->makeStay($owner);
        $admin = $this->makeAdminFor($kos);
        Message::factory()->create([
            'kos_id' => $kos->id,
            'sender_id' => $tenant->id,
            'receiver_id' => $owner->id,
            'message' => 'Butuh bantuan sengketa.',
        ]);

        $this->actingAs($admin)->get(route('admin.messages.index'))
            ->assertOk()
            ->assertSee('Audit Pesan')
            ->assertSee('Butuh bantuan sengketa.');

        $this->actingAs($admin)->get(route('admin.messages.show', $tenant))
            ->assertOk()
            ->assertSee('Mode Baca')
            ->assertSee('Butuh bantuan sengketa.');
    }

    public function test_admin_cannot_read_outside_assignment(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos] = $this->makeStay($owner);
        $ownerB = User::factory()->create(['role' => 'owner']);
        [$tenantB, $kosB] = $this->makeStay($ownerB);
        $admin = $this->makeAdminFor($kos);
        Message::factory()->create([
            'kos_id' => $kosB->id,
            'sender_id' => $tenantB->id,
            'receiver_id' => $ownerB->id,
            'message' => 'Rahasia kos B.',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.messages.index'));
        $response->assertOk();
        $response->assertDontSee('Rahasia kos B.');

        $this->actingAs($admin)->get(route('admin.messages.show', $tenantB))->assertForbidden();
    }

    public function test_admin_has_no_reply_route(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos] = $this->makeStay($owner);
        $admin = $this->makeAdminFor($kos);

        $this->actingAs($admin)->post(route('owner.messages.reply', $tenant), ['message' => 'x'])
            ->assertForbidden();
    }

    public function test_tenant_and_owner_cannot_hit_admin_messages(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant] = $this->makeStay($owner);

        $this->actingAs($tenant)->get(route('admin.messages.index'))->assertForbidden();
        $this->actingAs($owner)->get(route('admin.messages.index'))->assertForbidden();
    }

    // ========== A2 DAMAGE CREATE ==========

    public function test_admin_can_create_report_in_scope(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);
        $admin = $this->makeAdminFor($kos);

        $this->actingAs($admin)->get(route('admin.damage-reports.create'))->assertOk();

        $response = $this->actingAs($admin)->post(route('admin.damage-reports.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'penghuni_id' => $penghuni->id,
            'category' => 'Fasilitas',
            'description' => 'Temuan lapangan admin.',
        ]);

        $report = DamageReport::where('user_id', $tenant->id)->latest()->first();
        $this->assertNotNull($report);
        $this->assertSame('pending', $report->status);
        $response->assertRedirect(route('admin.damage-reports.show', $report));
    }

    public function test_admin_cannot_create_report_outside_scope(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);
        $ownerB = User::factory()->create(['role' => 'owner']);
        [$tenantB, $kosB, $kamarB, $penghuniB] = $this->makeStay($ownerB);
        $admin = $this->makeAdminFor($kos);

        $this->actingAs($admin)->post(route('admin.damage-reports.store'), [
            'kos_id' => $kosB->id,
            'kamar_id' => $kamarB->id,
            'penghuni_id' => $penghuniB->id,
            'category' => 'Kamar',
            'description' => 'Lintas scope.',
        ])->assertForbidden();
        $this->assertSame(0, DamageReport::count());
    }

    // ========== A3 DASHBOARD ==========

    public function test_admin_dashboard_shows_scoped_counts(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);
        $admin = $this->makeAdminFor($kos);
        DamageReport::factory()->create([
            'kos_id' => $kos->id, 'kamar_id' => $kamar->id,
            'user_id' => $tenant->id, 'status' => 'pending',
        ]);
        CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $penghuni->kamar_id,
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $ownerB = User::factory()->create(['role' => 'owner']);
        [$tenantB, $kosB, $kamarB] = $this->makeStay($ownerB);
        DamageReport::factory()->create([
            'kos_id' => $kosB->id, 'kamar_id' => $kamarB->id,
            'user_id' => $tenantB->id, 'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Laporan Pending');
        $response->assertSee('Check-out Pending');
    }

    // ========== A4 API ==========

    public function test_api_admin_messages_and_damage_scoped(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar] = $this->makeStay($owner);
        $admin = $this->makeAdminFor($kos);
        Message::factory()->create([
            'kos_id' => $kos->id,
            'sender_id' => $tenant->id,
            'receiver_id' => $owner->id,
            'message' => 'Minta audit.',
        ]);
        $report = DamageReport::factory()->create([
            'kos_id' => $kos->id, 'kamar_id' => $kamar->id,
            'user_id' => $tenant->id, 'status' => 'pending',
        ]);
        Sanctum::actingAs($admin);

        $this->getJson(route('api.v1.admin.messages.index'))->assertOk();
        $this->getJson(route('api.v1.admin.messages.show', $tenant))
            ->assertOk()
            ->assertJsonPath('data.0.message', 'Minta audit.');
        $this->getJson(route('api.v1.admin.damage-reports.index'))->assertOk();
        $this->getJson(route('api.v1.admin.damage-reports.show', $report))->assertOk();

        // Luar scope ditolak.
        $ownerB = User::factory()->create(['role' => 'owner']);
        [$tenantB, $kosB, $kamarB] = $this->makeStay($ownerB);
        $reportB = DamageReport::factory()->create([
            'kos_id' => $kosB->id, 'kamar_id' => $kamarB->id,
            'user_id' => $tenantB->id, 'status' => 'pending',
        ]);
        $this->getJson(route('api.v1.admin.messages.show', $tenantB))->assertForbidden();
        $this->getJson(route('api.v1.admin.damage-reports.show', $reportB))->assertForbidden();

        // Tenant tidak boleh memakai endpoint admin.
        Sanctum::actingAs($tenant);
        $this->getJson(route('api.v1.admin.messages.index'))->assertForbidden();
    }
}
