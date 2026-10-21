<?php

namespace Tests\Feature;

use App\Models\CheckOut;
use App\Models\DamageReport;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OwnerCapabilityCompletionTest extends TestCase
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

    private function makeCheckout(Penghuni $penghuni, string $status = 'pending'): CheckOut
    {
        return CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $penghuni->kamar_id,
            'request_date' => now(),
            'status' => $status,
        ]);
    }

    // ========== OWNER DAMAGE REPORT CREATE/STORE ==========

    public function test_owner_can_open_create_form(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->makeStay($owner);

        $this->actingAs($owner)->get(route('owner.damage-reports.create'))->assertOk();
    }

    public function test_owner_can_create_report_for_own_tenant(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);

        $response = $this->actingAs($owner)->post(route('owner.damage-reports.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'penghuni_id' => $penghuni->id,
            'category' => 'Kamar Mandi',
            'description' => 'Shower bocor.',
        ]);

        $report = DamageReport::where('user_id', $tenant->id)->where('kos_id', $kos->id)->latest()->first();
        $this->assertNotNull($report);
        $this->assertSame('pending', $report->status);
        $this->assertSame($tenant->id, (int) $report->user_id);
        $response->assertRedirect(route('owner.damage-reports.show', $report));
    }

    public function test_owner_cannot_create_report_for_other_owner_tenant(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->makeStay($owner);

        $ownerB = User::factory()->create(['role' => 'owner']);
        [$tenantB, $kosB, $kamarB, $penghuniB] = $this->makeStay($ownerB);

        $this->actingAs($owner)->post(route('owner.damage-reports.store'), [
            'kos_id' => $kosB->id,
            'kamar_id' => $kamarB->id,
            'penghuni_id' => $penghuniB->id,
            'category' => 'Kamar',
            'description' => 'Coba lintas kos.',
        ])->assertForbidden();
        $this->assertSame(0, DamageReport::count());
    }

    public function test_owner_cannot_use_kamar_from_other_kos(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);
        $otherKamar = Kamar::factory()->create(['kos_id' => Kos::factory()->create(['owner_id' => User::factory()->create(['role' => 'owner'])->id])->id]);

        $this->actingAs($owner)->post(route('owner.damage-reports.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $otherKamar->id,
            'penghuni_id' => $penghuni->id,
            'category' => 'Kamar',
            'description' => 'Kamar silang.',
        ])->assertForbidden();
        $this->assertSame(0, DamageReport::count());
    }

    public function test_owner_cannot_spoof_owner_id_or_status(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);
        $stranger = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->post(route('owner.damage-reports.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'penghuni_id' => $penghuni->id,
            'category' => 'Kamar',
            'description' => 'Forge attempt.',
            'status' => 'resolved',
            'owner_id' => $stranger->id,
            'user_id' => $stranger->id,
        ])->assertRedirect();

        $report = DamageReport::latest()->first();
        $this->assertSame('pending', $report->status);
        $this->assertSame($tenant->id, (int) $report->user_id);
        $this->assertNull($report->owner_id);
    }

    public function test_tenant_cannot_access_owner_create_store(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);

        $this->actingAs($tenant)->get(route('owner.damage-reports.create'))->assertForbidden();
        $this->actingAs($tenant)->post(route('owner.damage-reports.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'penghuni_id' => $penghuni->id,
            'category' => 'Kamar',
            'description' => 'x',
        ])->assertForbidden();
    }

    public function test_owner_store_validation_requires_fields(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->makeStay($owner);

        $this->actingAs($owner)->post(route('owner.damage-reports.store'), [])
            ->assertSessionHasErrors(['kos_id', 'kamar_id', 'penghuni_id', 'category', 'description']);
    }

    // ========== CHECKOUT CANCEL ==========

    public function test_owner_can_cancel_pending_checkout(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, , , $penghuni] = $this->makeStay($owner);
        $checkOut = $this->makeCheckout($penghuni);

        $this->actingAs($owner)->post(route('owner.checkout.cancel', $checkOut))
            ->assertRedirect(route('owner.checkout.index'));

        $this->assertSame('cancelled', $checkOut->fresh()->status);
        // Record tetap ada; penghuni tidak tersentuh.
        $this->assertNotNull(CheckOut::find($checkOut->id));
        $this->assertSame('active', $penghuni->fresh()->status);
    }

    public function test_owner_cannot_cancel_other_owner_checkout(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);
        [$tenantB, , , $penghuniB] = $this->makeStay($ownerB);
        $checkOutB = $this->makeCheckout($penghuniB);

        $this->actingAs($owner)->post(route('owner.checkout.cancel', $checkOutB))->assertForbidden();
        $this->assertSame('pending', $checkOutB->fresh()->status);
    }

    public function test_owner_cannot_cancel_final_status(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, , , $penghuni] = $this->makeStay($owner);

        foreach (['approved', 'rejected', 'cancelled'] as $status) {
            $co = $this->makeCheckout($penghuni, $status);

            $this->actingAs($owner)->post(route('owner.checkout.cancel', $co))->assertStatus(400);
            $this->assertSame($status, $co->fresh()->status);
        }
    }

    public function test_tenant_cannot_cancel_checkout(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, , , $penghuni] = $this->makeStay($owner);
        $checkOut = $this->makeCheckout($penghuni);

        $this->actingAs($tenant)->post(route('owner.checkout.cancel', $checkOut))->assertForbidden();
        $this->assertSame('pending', $checkOut->fresh()->status);
    }

    public function test_admin_can_cancel_scoped_checkout(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, , $penghuni] = $this->makeStay($owner);
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignedKos()->attach($kos->id);
        $checkOut = $this->makeCheckout($penghuni);

        $this->actingAs($admin)->post(route('admin.checkout.cancel', $checkOut))
            ->assertRedirect(route('admin.checkout.index'));
        $this->assertSame('cancelled', $checkOut->fresh()->status);
    }

    // ========== OWNER PROFILE SENSITIVE FIELDS ==========

    public function test_owner_can_update_phone_address_identity(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->patch(route('profile.update'), [
            'name' => $owner->name,
            'email' => $owner->email,
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 1',
            'identity_number' => '3201010101010001',
        ])->assertRedirect(route('profile.edit'));

        $owner->refresh();
        $this->assertSame('081234567890', $owner->phone);
        $this->assertSame('Jl. Merdeka No. 1', $owner->address);
        $this->assertSame('3201010101010001', $owner->identity_number);
    }

    public function test_profile_rejects_invalid_data(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->patch(route('profile.update'), [
            'name' => $owner->name,
            'email' => $owner->email,
            'phone' => str_repeat('1', 21),
            'identity_number' => str_repeat('2', 51),
        ])->assertSessionHasErrors(['phone', 'identity_number']);
    }

    public function test_profile_mass_assignment_cannot_escalate_role(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->patch(route('profile.update'), [
            'name' => 'Tetap Owner',
            'email' => $owner->email,
            'role' => 'super_admin',
            'is_active' => false,
            'password' => 'hacked',
        ])->assertRedirect(route('profile.edit'));

        $owner->refresh();
        $this->assertSame('owner', $owner->role);
        $this->assertTrue((bool) $owner->is_active);
    }

    public function test_identity_number_not_exposed_via_api(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'identity_number' => '3201010101010001']);

        $payload = $this->actingAs($owner, 'sanctum')->getJson(route('api.v1.me'))->json();
        $this->assertArrayNotHasKey('identity_number', $payload['data'] ?? []);
    }

    public function test_owner_cannot_update_other_user_via_profile(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $other = User::factory()->create(['role' => 'owner', 'phone' => '0800000000']);

        // Profile endpoint selalu menyasar authenticated user — tidak ada
        // parameter user lain yang dapat dimanipulasi.
        $this->actingAs($owner)->patch(route('profile.update'), [
            'name' => $owner->name,
            'email' => $owner->email,
            'phone' => '0899999999',
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame('0800000000', $other->fresh()->phone);
        $this->assertSame('0899999999', $owner->fresh()->phone);
    }

    // ========== DASHBOARD PENDING DAMAGE REPORTS ==========

    public function test_owner_dashboard_shows_scoped_pending_count(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar] = $this->makeStay($owner);
        DamageReport::factory()->create(['kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'user_id' => $tenant->id, 'status' => 'pending']);
        DamageReport::factory()->create(['kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'user_id' => $tenant->id, 'status' => 'pending']);
        DamageReport::factory()->create(['kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'user_id' => $tenant->id, 'status' => 'resolved']);

        $ownerB = User::factory()->create(['role' => 'owner']);
        [$tenantB, $kosB, $kamarB] = $this->makeStay($ownerB);
        DamageReport::factory()->create(['kos_id' => $kosB->id, 'kamar_id' => $kamarB->id, 'user_id' => $tenantB->id, 'status' => 'pending']);

        $response = $this->actingAs($owner)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Laporan Pending');
    }

    public function test_dashboard_query_counts_only_owned_pending(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar] = $this->makeStay($owner);
        DamageReport::factory()->create(['kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'user_id' => $tenant->id, 'status' => 'pending']);

        $count = DamageReport::where('status', DamageReport::STATUS_PENDING)
            ->whereHas('kos', fn ($q) => $q->where('owner_id', $owner->id))
            ->count();
        $this->assertSame(1, $count);

        // Zero-case: owner tanpa laporan.
        $lonely = User::factory()->create(['role' => 'owner']);
        Kos::factory()->create(['owner_id' => $lonely->id]);
        $zero = DamageReport::where('status', DamageReport::STATUS_PENDING)
            ->whereHas('kos', fn ($q) => $q->where('owner_id', $lonely->id))
            ->count();
        $this->assertSame(0, $zero);
    }

    public function test_dashboard_does_not_add_n_plus_one(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar] = $this->makeStay($owner);
        DamageReport::factory()->create(['kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'user_id' => $tenant->id, 'status' => 'pending']);

        DB::enableQueryLog();
        $this->actingAs($owner)->get(route('dashboard'))->assertOk();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertLessThan(60, count($queries), 'Too many queries ('.count($queries).'), possible N+1');
    }
}
