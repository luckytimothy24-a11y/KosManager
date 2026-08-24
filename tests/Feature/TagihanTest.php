<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagihanTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    private Penghuni $penghuni;

    private Kontrak $kontrak;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $this->penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $this->kontrak = Kontrak::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'rental_type' => 'monthly',
            'rental_price' => 1500000,
            'status' => 'active',
        ]);
    }

    public function test_owner_can_create_tagihan(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $this->kontrak->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => now()->startOfMonth()->format('Y-m-d'),
            'period_end' => now()->endOfMonth()->format('Y-m-d'),
            'subtotal' => 1500000,
            'discount' => 0,
            'penalty' => 0,
            'due_date' => now()->endOfMonth()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('owner.tagihan.index'));
        $this->assertDatabaseHas('tagihans', [
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'bill_type' => 'Sewa Bulanan',
            'total' => 1500000,
            'status' => 'unpaid',
        ]);
    }

    public function test_owner_can_view_tagihan_list(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.tagihan.index'));
        $response->assertStatus(200);
    }

    public function test_tenant_can_view_own_tagihan(): void
    {
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kamar->id,
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.index'));
        $response->assertStatus(200);
    }

    public function test_owner_can_view_tagihan_detail(): void
    {
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kamar->id,
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.tagihan.show', $tagihan));
        $response->assertStatus(200);
    }

    public function test_tagihan_total_is_calculated_correctly(): void
    {
        // Client mengirim subtotal 2000000, tapi server wajib memakai harga kontrak (1500000 x 1 bulan).
        $response = $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $this->kontrak->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => now()->startOfMonth()->format('Y-m-d'),
            'period_end' => now()->endOfMonth()->format('Y-m-d'),
            'subtotal' => 2000000,
            'discount' => 100000,
            'penalty' => 50000,
            'due_date' => now()->endOfMonth()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('tagihans', [
            'penghuni_id' => $this->penghuni->id,
            'subtotal' => 1500000,
            'discount' => 100000,
            'penalty' => 50000,
            'total' => 1450000,
        ]);
    }

    public function test_owner_can_view_tagihan_create_form(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.tagihan.create'));
        $response->assertStatus(200);
    }

    public function test_tenant_cannot_create_tagihan(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $this->kontrak->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => now()->startOfMonth()->format('Y-m-d'),
            'period_end' => now()->endOfMonth()->format('Y-m-d'),
            'subtotal' => 1500000,
            'due_date' => now()->endOfMonth()->format('Y-m-d'),
        ]);

        $response->assertStatus(403);
    }
}
