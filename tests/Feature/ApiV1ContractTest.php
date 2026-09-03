<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1ContractTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private User $otherTenant;

    private User $owner;

    private Kos $kos;

    private Kamar $kamar;

    private Penghuni $penghuni;

    private Kontrak $kontrak;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $this->otherTenant = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);

        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied']);

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
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addMonths(4),
            'status' => 'active',
            'notes' => 'Kontrak bulanan',
        ]);
    }

    private function makeOtherKontrak(): array
    {
        $kosB = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $kamarB = Kamar::factory()->create(['kos_id' => $kosB->id, 'status' => 'occupied']);
        $penghuniB = Penghuni::factory()->create([
            'user_id' => $this->otherTenant->id,
            'kos_id' => $kosB->id,
            'kamar_id' => $kamarB->id,
            'status' => 'active',
        ]);
        $kontrakB = Kontrak::factory()->create([
            'penghuni_id' => $penghuniB->id,
            'kos_id' => $kosB->id,
            'kamar_id' => $kamarB->id,
            'rental_type' => 'daily',
            'rental_price' => 100000,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'status' => 'active',
        ]);

        return ['kontrak' => $kontrakB, 'penghuni' => $penghuniB];
    }

    private function makeTagihanFor(Kontrak $kontrak, int $total = 1500000): Tagihan
    {
        return Tagihan::factory()->create([
            'penghuni_id' => $kontrak->penghuni_id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $kontrak->kamar_id,
            'status' => 'unpaid',
            'total' => $total,
            'due_date' => now()->addDays(7),
        ]);
    }

    // ---------------------------------------------------------------- index

    public function test_index_returns_own_kontrak_contract_shape(): void
    {
        $response = $this->withToken($this->token($this->tenant))->getJson('/api/v1/kontrak');

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'contract_number',
                    'kos' => ['id', 'name', 'address', 'city', 'photo'],
                    'kamar' => ['id', 'name', 'type', 'photo'],
                    'rental_type',
                    'rental_price',
                    'start_date',
                    'end_date',
                    'status',
                    'notes',
                ]],
                'meta' => ['current_page', 'per_page', 'last_page', 'total'],
            ]);

        $item = $response->json('data.0');
        $this->assertSame($this->kontrak->id, $item['id']);
        $this->assertSame($this->kos->id, $item['kos']['id']);
        $this->assertSame($this->kamar->id, $item['kamar']['id']);
        $this->assertSame('active', $item['status']);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/kontrak')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_index_requires_tenant_role(): void
    {
        $this->withToken($this->token($this->owner))->getJson('/api/v1/kontrak')
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_index_returns_only_own_kontrak(): void
    {
        $other = $this->makeOtherKontrak();

        $response = $this->withToken($this->token($this->tenant))->getJson('/api/v1/kontrak');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($this->kontrak->id, $ids);
        $this->assertNotContains($other['kontrak']->id, $ids);
    }

    public function test_index_returns_empty_collection_with_200(): void
    {
        $fresh = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('x')]);

        $response = $this->withToken($this->token($fresh))->getJson('/api/v1/kontrak');

        $response->assertOk()
            ->assertJson(['data' => [], 'meta' => ['total' => 0]]);
    }

    public function test_index_defaults_and_caps_per_page(): void
    {
        $default = $this->withToken($this->token($this->tenant))->getJson('/api/v1/kontrak');
        $this->assertSame(15, $default->json('meta.per_page'));

        $capped = $this->withToken($this->token($this->tenant))->getJson('/api/v1/kontrak?per_page=200');
        $this->assertSame(50, $capped->json('meta.per_page'));
    }

    public function test_index_does_not_expose_internal_or_sensitive_fields(): void
    {
        $item = $this->withToken($this->token($this->tenant))->getJson('/api/v1/kontrak')->json('data.0');

        $this->assertArrayNotHasKey('penghuni_id', $item);
        $this->assertArrayNotHasKey('kos_id', $item);
        $this->assertArrayNotHasKey('kamar_id', $item);
        $this->assertArrayNotHasKey('penghuni', $item);
        $this->assertArrayNotHasKey('created_at', $item);
        $this->assertArrayNotHasKey('updated_at', $item);
        $this->assertArrayNotHasKey('deleted_at', $item);
    }

    // ---------------------------------------------------------------- show

    public function test_show_returns_kontrak_detail(): void
    {
        $response = $this->withToken($this->token($this->tenant))
            ->getJson("/api/v1/kontrak/{$this->kontrak->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'contract_number',
                'kos',
                'kamar',
                'rental_type',
                'rental_price',
                'start_date',
                'end_date',
                'status',
                'notes',
            ]]);
        $this->assertSame($this->kontrak->contract_number, $response->json('data.contract_number'));
    }

    public function test_show_denies_other_tenant_kontrak(): void
    {
        $other = $this->makeOtherKontrak();

        $this->withToken($this->token($this->tenant))->getJson("/api/v1/kontrak/{$other['kontrak']->id}")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_show_requires_authentication(): void
    {
        $this->getJson("/api/v1/kontrak/{$this->kontrak->id}")
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_show_requires_tenant_role(): void
    {
        $this->withToken($this->token($this->owner))->getJson("/api/v1/kontrak/{$this->kontrak->id}")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_show_returns_404_for_missing_kontrak(): void
    {
        $this->withToken($this->token($this->tenant))->getJson('/api/v1/kontrak/999999')
            ->assertStatus(404)
            ->assertJson(['message' => 'Resource not found.']);
    }

    // ------------------------------------------------------- kontrak tagihan

    public function test_tagihan_returns_related_bills(): void
    {
        $bill = $this->makeTagihanFor($this->kontrak);

        $response = $this->withToken($this->token($this->tenant))
            ->getJson("/api/v1/kontrak/{$this->kontrak->id}/tagihan");

        $response->assertOk()
            ->assertJsonStructure(['data' => [[
                'id',
                'bill_number',
                'bill_type',
                'total',
                'status',
            ]]])
            ->assertJson([
                'data' => [[
                    'id' => $bill->id,
                    'total' => 1500000,
                ]],
            ]);
    }

    public function test_tagihan_returns_empty_when_no_bills(): void
    {
        $response = $this->withToken($this->token($this->tenant))
            ->getJson("/api/v1/kontrak/{$this->kontrak->id}/tagihan");

        $response->assertOk()
            ->assertJson(['data' => []]);
    }

    public function test_tagihan_denies_other_tenant_kontrak(): void
    {
        $other = $this->makeOtherKontrak();

        $this->withToken($this->token($this->tenant))
            ->getJson("/api/v1/kontrak/{$other['kontrak']->id}/tagihan")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_tagihan_does_not_leak_other_kontrak_bills(): void
    {
        $other = $this->makeOtherKontrak();
        $otherBill = $this->makeTagihanFor($other['kontrak'], 999000);

        $response = $this->withToken($this->token($this->tenant))
            ->getJson("/api/v1/kontrak/{$this->kontrak->id}/tagihan");

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertNotContains($otherBill->id, $ids);
    }

    public function test_tagihan_requires_authentication(): void
    {
        $this->getJson("/api/v1/kontrak/{$this->kontrak->id}/tagihan")
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_tagihan_requires_tenant_role(): void
    {
        $this->withToken($this->token($this->owner))->getJson("/api/v1/kontrak/{$this->kontrak->id}/tagihan")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_tagihan_returns_404_for_missing_kontrak(): void
    {
        $this->withToken($this->token($this->tenant))->getJson('/api/v1/kontrak/999999/tagihan')
            ->assertStatus(404)
            ->assertJson(['message' => 'Resource not found.']);
    }
}
