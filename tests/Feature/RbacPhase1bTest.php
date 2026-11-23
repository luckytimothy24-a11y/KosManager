<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPhase1bTest extends TestCase
{
    use RefreshDatabase;

    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->technician = User::factory()->create(['role' => 'technician', 'is_active' => true]);
    }

    public function test_technician_cannot_access_tenant_data_area(): void
    {
        $this->actingAs($this->technician)
            ->get(route('tenant.tagihan.index'))
            ->assertForbidden();

        $this->actingAs($this->technician)
            ->get(route('tenant.damage-reports.index'))
            ->assertForbidden();
    }

    public function test_technician_cannot_access_admin_area(): void
    {
        $this->actingAs($this->technician)
            ->get(route('admin.kos.index'))
            ->assertForbidden();

        $this->actingAs($this->technician)
            ->get(route('admin.pembayaran.index'))
            ->assertForbidden();
    }

    public function test_technician_cannot_access_owner_area(): void
    {
        $this->actingAs($this->technician)
            ->get(route('owner.kos.index'))
            ->assertForbidden();

        $this->actingAs($this->technician)
            ->get(route('owner.pembayaran.index'))
            ->assertForbidden();
    }

    public function test_technician_cannot_access_super_admin_area(): void
    {
        $this->actingAs($this->technician)
            ->get(route('super-admin.users.index'))
            ->assertForbidden();
    }

    public function test_technician_can_access_profile_and_notifications(): void
    {
        $this->actingAs($this->technician)
            ->get(route('profile.edit'))
            ->assertOk();

        $this->actingAs($this->technician)
            ->get(route('notifications.index'))
            ->assertOk();
    }

    public function test_technician_is_denied_by_role_middleware_for_owner_route(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);

        $this->actingAs($this->technician)
            ->get(route('owner.kos.show', $kos))
            ->assertForbidden();
    }

    public function test_technician_cannot_verify_payment(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => User::factory()->create(['role' => 'pencari'])->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
        ]);
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'verification_status' => 'pending',
        ]);

        $this->actingAs($this->technician)
            ->post(route('owner.pembayaran.verify', $pembayaran))
            ->assertForbidden();
    }
}
