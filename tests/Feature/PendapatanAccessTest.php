<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendapatanAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeStay(Kos $kos, ?User $tenant = null): array
    {
        $tenant ??= User::factory()->create(['role' => 'tenant']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => Kontrak::factory()->create(['penghuni_id' => $penghuni->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id])->id,
            'kamar_id' => $kamar->id,
            'status' => 'paid',
        ]);

        return [$penghuni, $tagihan, $kamar];
    }

    private function approvedPayment(Penghuni $penghuni, Tagihan $tagihan, int $amount): Pembayaran
    {
        return Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => $amount,
            'verification_status' => 'approved',
            'payment_date' => now()->toDateString(),
        ]);
    }

    // ---- Owner ----

    public function test_owner_can_view_pendapatan(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        Kos::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($owner)->get('/owner/pendapatan')->assertOk()->assertSee('Pendapatan');
    }

    public function test_owner_only_sees_own_revenue(): void
    {
        $ownerA = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);
        $kosA = Kos::factory()->create(['owner_id' => $ownerA->id]);
        $kosB = Kos::factory()->create(['owner_id' => $ownerB->id]);
        [$pA, $tA] = $this->makeStay($kosA);
        [$pB, $tB] = $this->makeStay($kosB);
        $this->approvedPayment($pA, $tA, 100000);
        $this->approvedPayment($pB, $tB, 200000);

        $res = $this->actingAs($ownerA)->get('/owner/pendapatan');
        $res->assertOk();
        $res->assertSee('Rp 100.000', false);
        $res->assertDontSee('Rp 200.000', false);
    }

    public function test_owner_cannot_filter_other_owner_kos(): void
    {
        $ownerA = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);
        Kos::factory()->create(['owner_id' => $ownerA->id]);
        $kosB = Kos::factory()->create(['owner_id' => $ownerB->id]);

        $this->actingAs($ownerA)->get("/owner/pendapatan?kos_id={$kosB->id}")->assertForbidden();
        $this->actingAs($ownerA)->get("/owner/laporan/export-excel/gabungan?kos_id={$kosB->id}")->assertForbidden();
    }

    public function test_tenant_cannot_access_pendapatan(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($tenant)->get('/owner/laporan')->assertForbidden();
        $this->actingAs($tenant)->get('/admin/laporan')->assertForbidden();
        $this->actingAs($tenant)->get('/super-admin/laporan')->assertForbidden();
    }

    // ---- Admin ----

    public function test_admin_sees_only_assigned_kos_revenue(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kosA = Kos::factory()->create(['owner_id' => $owner->id, 'name' => 'KosA']);
        $kosB = Kos::factory()->create(['owner_id' => $owner->id, 'name' => 'KosB']);
        [$pA, $tA] = $this->makeStay($kosA);
        [$pB, $tB] = $this->makeStay($kosB);
        $this->approvedPayment($pA, $tA, 100000);
        $this->approvedPayment($pB, $tB, 200000);

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignedKos()->attach($kosA->id);

        $res = $this->actingAs($admin)->get('/admin/laporan/export-excel/gabungan');
        $res->assertOk();
        $res->assertSee('Rp 100.000', false);
        $res->assertDontSee('Rp 200.000', false);
    }

    public function test_admin_cannot_filter_unassigned_kos(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kosA = Kos::factory()->create(['owner_id' => $owner->id]);
        $kosB = Kos::factory()->create(['owner_id' => $owner->id]);
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignedKos()->attach($kosA->id);

        $this->actingAs($admin)->get("/admin/laporan/export-excel/gabungan?kos_id={$kosB->id}")->assertForbidden();
    }

    public function test_admin_without_assignment_sees_zero_not_global(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        [$p, $t] = $this->makeStay($kos);
        $this->approvedPayment($p, $t, 500000);

        $admin = User::factory()->create(['role' => 'admin']);

        $res = $this->actingAs($admin)->get('/admin/laporan/export-excel/gabungan');
        $res->assertOk();
        $res->assertSee('Rp 0', false);
        $res->assertSee('Belum ada data');
        $res->assertDontSee('Rp 500.000', false);
    }

    public function test_admin_dashboard_revenue_is_assigned_only(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kosA = Kos::factory()->create(['owner_id' => $owner->id]);
        $kosB = Kos::factory()->create(['owner_id' => $owner->id]);
        [$pA, $tA] = $this->makeStay($kosA);
        [$pB, $tB] = $this->makeStay($kosB);
        $this->approvedPayment($pA, $tA, 100000);
        $this->approvedPayment($pB, $tB, 200000);

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignedKos()->attach($kosA->id);

        $res = $this->actingAs($admin)->get('/dashboard');
        $res->assertOk();
        // total_revenue should reflect only assigned (100k), rendered as Rp 100.000
        $res->assertSee('Rp 100.000', false);
        $res->assertDontSee('Rp 300.000', false);
    }

    public function test_admin_dashboard_without_assignment_is_zero(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        [$p, $t] = $this->makeStay($kos);
        $this->approvedPayment($p, $t, 500000);

        $admin = User::factory()->create(['role' => 'admin']);

        $res = $this->actingAs($admin)->get('/dashboard');
        $res->assertOk();
        $res->assertSee('Rp 0', false);
    }

    // ---- Super Admin ----

    public function test_super_admin_sees_global_pendapatan(): void
    {
        $o1 = User::factory()->create(['role' => 'owner']);
        $o2 = User::factory()->create(['role' => 'owner']);
        $k1 = Kos::factory()->create(['owner_id' => $o1->id]);
        $k2 = Kos::factory()->create(['owner_id' => $o2->id]);
        [$p1, $t1] = $this->makeStay($k1);
        [$p2, $t2] = $this->makeStay($k2);
        $this->approvedPayment($p1, $t1, 100000);
        $this->approvedPayment($p2, $t2, 200000);

        $super = User::factory()->create(['role' => 'super_admin']);

        $res = $this->actingAs($super)->get('/super-admin/laporan/export-excel/gabungan');
        $res->assertOk();
        $res->assertSee('Rp 300.000', false);
    }

    public function test_pending_rejected_not_counted(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        [$p, $t] = $this->makeStay($kos);
        Pembayaran::factory()->create(['tagihan_id' => $t->id, 'penghuni_id' => $p->id, 'amount' => 99999, 'verification_status' => 'pending', 'payment_date' => now()]);
        Pembayaran::factory()->create(['tagihan_id' => $t->id, 'penghuni_id' => $p->id, 'amount' => 88888, 'verification_status' => 'rejected', 'payment_date' => now()]);
        $this->approvedPayment($p, $t, 100000);

        $res = $this->actingAs($owner)->get('/owner/laporan/export-excel/gabungan');
        $res->assertOk();
        $res->assertSee('Rp 100.000', false);
        $res->assertDontSee('Rp 99.999', false);
    }
}
