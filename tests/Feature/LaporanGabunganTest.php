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

class LaporanGabunganTest extends TestCase
{
    use RefreshDatabase;

    private function createOwnerWithKos(string $name): array
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'name' => $name]);
        return [$owner, $kos];
    }

    private function seedOwnershipData(User $ownerA, Kos $kosA, Kos $kosB): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kamarA = Kamar::factory()->create(['kos_id' => $kosA->id]);
        $kamarB = Kamar::factory()->create(['kos_id' => $kosB->id]);
        $penghuniA = Penghuni::factory()->create(['user_id' => $tenant->id, 'kos_id' => $kosA->id, 'kamar_id' => $kamarA->id]);
        $penghuniB = Penghuni::factory()->create(['kos_id' => $kosB->id, 'kamar_id' => $kamarB->id]);
        $tagihanA = Tagihan::factory()->create(['penghuni_id' => $penghuniA->id, 'kamar_id' => $kamarA->id, 'status' => 'paid']);
        Tagihan::factory()->create(['penghuni_id' => $penghuniB->id, 'kamar_id' => $kamarB->id, 'status' => 'paid']);
        Pembayaran::factory()->create(['tagihan_id' => $tagihanA->id, 'penghuni_id' => $penghuniA->id, 'verification_status' => 'approved']);
    }

    public function test_super_admin_gabungan_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        Kos::factory()->create();
        $res = $this->actingAs($admin)->get('/super-admin/laporan/export-pdf');
        $res->assertOk();
        $res->assertHeader('Content-Type', 'application/pdf');
        $this->assertGreaterThan(1000, strlen($res->getContent()));
    }

    public function test_super_admin_gabungan_excel(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        Kos::factory()->create();
        $res = $this->actingAs($admin)->get('/super-admin/laporan/export-excel');
        $res->assertOk();
        $this->assertStringContainsString('text/csv', $res->headers->get('Content-Type'));
        $csv = $res->getContent();
        $this->assertStringContainsString('Laporan Gabungan', $csv);
        $this->assertGreaterThan(200, strlen($csv));
    }

    public function test_owner_gabungan_only_own_kos(): void
    {
        [$ownerA, $kosA] = $this->createOwnerWithKos('KOS-A-GAB');
        [, $kosB] = $this->createOwnerWithKos('KOS-B-GAB');
        $this->seedOwnershipData($ownerA, $kosA, $kosB);

        $res = $this->actingAs($ownerA)->get('/owner/laporan/export-excel');
        $res->assertOk();
        $csv = $res->getContent();
        $this->assertStringContainsString('KOS-A-GAB', $csv);
        $this->assertStringNotContainsString('KOS-B-GAB', $csv);
    }

    public function test_owner_cannot_export_gabungan_other_kos(): void
    {
        [$ownerA] = $this->createOwnerWithKos('KOS-A-GAB2');
        [, $kosB] = $this->createOwnerWithKos('KOS-B-GAB2');
        $res = $this->actingAs($ownerA)->get("/owner/laporan/export-excel?kos_id={$kosB->id}");
        $res->assertForbidden();
        $res2 = $this->actingAs($ownerA)->get("/owner/laporan/export-pdf?kos_id={$kosB->id}");
        $res2->assertForbidden();
    }

    public function test_admin_gabungan_only_assigned(): void
    {
        [$owner, $kosA] = $this->createOwnerWithKos('KOS-ADMIN-A');
        [, $kosB] = $this->createOwnerWithKos('KOS-ADMIN-B');
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignedKos()->attach($kosA->id);
        $this->seedOwnershipData($owner, $kosA, $kosB);

        $res = $this->actingAs($admin)->get('/admin/laporan/export-excel');
        $res->assertOk();
        $csv = $res->getContent();
        $this->assertStringContainsString('KOS-ADMIN-A', $csv);
        $this->assertStringNotContainsString('KOS-ADMIN-B', $csv);
    }

    public function test_admin_cannot_export_gabungan_unassigned(): void
    {
        [, $kosB] = $this->createOwnerWithKos('KOS-ADMIN-B2');
        $admin = User::factory()->create(['role' => 'admin']);
        $res = $this->actingAs($admin)->get("/admin/laporan/export-excel?kos_id={$kosB->id}");
        $res->assertForbidden();
    }

    public function test_super_admin_gabungan_global(): void
    {
        [$owner, $kosA] = $this->createOwnerWithKos('KOS-GLOBAL-A');
        $this->seedOwnershipData($owner, $kosA, $kosA);
        $admin = User::factory()->create(['role' => 'super_admin']);
        $res = $this->actingAs($admin)->get('/super-admin/laporan/export-excel');
        $res->assertOk();
        $this->assertStringContainsString('KOS-GLOBAL-A', $res->getContent());
    }

    public function test_gabungan_respects_filters(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        Kos::factory()->create();
        $res = $this->actingAs($admin)->get('/super-admin/laporan/export-pdf?start_date=2026-01-01&end_date=2026-12-31');
        $res->assertOk();
        $this->assertGreaterThan(1000, strlen($res->getContent()));
    }

    public function test_super_admin_can_view_laporan_page(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        Kos::factory()->count(3)->create();
        $res = $this->actingAs($admin)->get('/super-admin/laporan');
        $res->assertOk();
        $res->assertSee('Laporan Kos');
    }

    public function test_admin_can_view_laporan_page(): void
    {
        [$owner, $kosA] = $this->createOwnerWithKos('KOS-ADMIN-TEST');
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignedKos()->attach($kosA->id);
        $res = $this->actingAs($admin)->get('/admin/laporan');
        $res->assertOk();
        $res->assertSee('Laporan Kos');
    }

    public function test_admin_cannot_see_other_kos_on_page(): void
    {
        [$ownerA, $kosA] = $this->createOwnerWithKos('KOS-ADMIN-ONLY-A');
        [, $kosB] = $this->createOwnerWithKos('KOS-ADMIN-ONLY-B');
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignedKos()->attach($kosA->id);
        $res = $this->actingAs($admin)->get('/admin/laporan?kos_id='.$kosB->id);
        $res->assertForbidden();
    }

    public function test_owner_can_view_laporan_page(): void
    {
        [$owner, $kos] = $this->createOwnerWithKos('KOS-OWNER-TEST');
        $res = $this->actingAs($owner)->get('/owner/laporan');
        $res->assertOk();
        $res->assertSee('Laporan Kos');
    }

    public function test_tenant_cannot_access_laporan(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($tenant)->get('/admin/laporan')->assertForbidden();
        $this->actingAs($tenant)->get('/super-admin/laporan')->assertForbidden();
    }

    public function test_laporan_page_contains_sections(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        Kos::factory()->create();
        $res = $this->actingAs($admin)->get('/super-admin/laporan');
        $res->assertOk();
        $res->assertSee('Ringkasan Kos');
        $res->assertSee('Laporan Pendapatan');
        $res->assertSee('Laporan Penghuni');
        $res->assertSee('Laporan Booking');
        $res->assertSee('Laporan Kamar');
        $res->assertSee('Laporan Tagihan');
    }

    public function test_laporan_page_export_links_present(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        Kos::factory()->create();
        $res = $this->actingAs($admin)->get('/super-admin/laporan');
        $res->assertOk();
        $res->assertSee('Export PDF');
        $res->assertSee('Export Excel');
    }
}
