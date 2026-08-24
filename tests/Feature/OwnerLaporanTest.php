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

class OwnerLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function createOwnerWithKos(string $kosName): array
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'name' => $kosName]);

        return [$owner, $kos];
    }

    public function test_owner_can_view_laporan_index(): void
    {
        [$owner, $kos] = $this->createOwnerWithKos('Kos Milik A');

        $response = $this->actingAs($owner)->get('/owner/laporan');

        $response->assertOk();
        $response->assertSee('Laporan Pendapatan');
        $response->assertSee('Kos Milik A');
    }

    public function test_owner_can_export_pdf(): void
    {
        [$owner, $kos] = $this->createOwnerWithKos('Kos Milik A');

        $response = $this->actingAs($owner)->get('/owner/laporan/export-pdf/kamar');

        $response->assertOk();
    }

    public function test_owner_export_excel_only_contains_own_kos(): void
    {
        [$ownerA, $kosA] = $this->createOwnerWithKos('KOSMILIK-A');
        [, $kosB] = $this->createOwnerWithKos('KOSMILIK-B');

        $tenant = User::factory()->create(['role' => 'tenant']);
        $kamarA = Kamar::factory()->create(['kos_id' => $kosA->id]);
        $kamarB = Kamar::factory()->create(['kos_id' => $kosB->id]);

        $penghuniA = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kosA->id,
            'kamar_id' => $kamarA->id,
        ]);
        $penghuniB = Penghuni::factory()->create([
            'kos_id' => $kosB->id,
            'kamar_id' => $kamarB->id,
        ]);

        $tagihanA = Tagihan::factory()->create([
            'penghuni_id' => $penghuniA->id,
            'kamar_id' => $kamarA->id,
            'status' => 'paid',
        ]);
        Tagihan::factory()->create([
            'penghuni_id' => $penghuniB->id,
            'kamar_id' => $kamarB->id,
            'status' => 'paid',
        ]);

        Pembayaran::factory()->create([
            'tagihan_id' => $tagihanA->id,
            'penghuni_id' => $penghuniA->id,
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($ownerA)->get('/owner/laporan/export-excel/pendapatan');

        $response->assertOk();
        $csv = $response->getContent();

        $this->assertStringContainsString('KOSMILIK-A', $csv);
        $this->assertStringNotContainsString('KOSMILIK-B', $csv);
    }

    public function test_owner_cannot_export_other_owner_kos_data(): void
    {
        [$ownerA] = $this->createOwnerWithKos('KOSMILIK-A');
        [, $kosB] = $this->createOwnerWithKos('KOSMILIK-B');

        $response = $this->actingAs($ownerA)
            ->get("/owner/laporan/export-excel/kamar?kos_id={$kosB->id}");

        $response->assertForbidden();
    }

    public function test_tenant_cannot_access_owner_laporan(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($tenant)->get('/owner/laporan')->assertForbidden();
    }
}
