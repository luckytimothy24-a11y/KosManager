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

class ApiV1OwnerContractTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $otherOwner;

    private User $tenantA;

    private User $tenantB;

    private User $admin;

    private User $superAdmin;

    private Kos $kos;

    private Kos $otherKos;

    private Kamar $room;

    private Kamar $otherRoom;

    private Penghuni $penghuniA;

    private Penghuni $penghuniB;

    private Kontrak $kontrak;

    private Kontrak $otherKontrak;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->otherOwner = User::factory()->create(['role' => 'owner']);
        $this->tenantA = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $this->tenantB = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->otherKos = Kos::factory()->create(['owner_id' => $this->otherOwner->id]);
        $this->room = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $this->otherRoom = Kamar::factory()->create(['kos_id' => $this->otherKos->id]);

        $this->penghuniA = Penghuni::factory()->create([
            'user_id' => $this->tenantA->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->room->id,
            'status' => 'active',
        ]);
        $this->penghuniB = Penghuni::factory()->create([
            'user_id' => $this->tenantB->id,
            'kos_id' => $this->otherKos->id,
            'kamar_id' => $this->otherRoom->id,
            'status' => 'active',
        ]);

        $this->kontrak = Kontrak::factory()->create([
            'penghuni_id' => $this->penghuniA->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->room->id,
            'status' => 'active',
        ]);
        $this->otherKontrak = Kontrak::factory()->create([
            'penghuni_id' => $this->penghuniB->id,
            'kos_id' => $this->otherKos->id,
            'kamar_id' => $this->otherRoom->id,
            'status' => 'active',
        ]);
    }

    private function bill(Kontrak $kontrak, array $overrides = []): Tagihan
    {
        return Tagihan::factory()->create(array_merge([
            'penghuni_id' => $kontrak->penghuni_id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $kontrak->kamar_id,
            'status' => 'unpaid',
        ], $overrides));
    }

    // ------------------------------------------------------------ authentication

    public function test_owner_contract_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/owner/kontrak')->assertStatus(401);
        $this->getJson('/api/v1/owner/kontrak/1')->assertStatus(401);
        $this->getJson('/api/v1/owner/kontrak/1/tagihan')->assertStatus(401);
    }

    public function test_owner_contract_routes_reject_non_owner_roles(): void
    {
        foreach ([
            'tenant' => $this->tenantA,
            'admin' => $this->admin,
            'super_admin' => $this->superAdmin,
        ] as $label => $user) {
            $this->withToken($this->token($user))->getJson('/api/v1/owner/kontrak')->assertStatus(403);
            $this->withToken($this->token($user))->getJson('/api/v1/owner/kontrak/1')->assertStatus(403);
            $this->withToken($this->token($user))->getJson('/api/v1/owner/kontrak/1/tagihan')->assertStatus(403);
        }
    }

    // ---------------------------------------------------------------- index

    public function test_index_returns_only_own_contracts(): void
    {
        $response = $this->withToken($this->token($this->owner))->getJson('/api/v1/owner/kontrak');

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id', 'contract_number', 'penghuni', 'kos', 'kamar', 'status',
                ]],
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $this->kontrak->id])
            ->assertJsonMissing(['id' => $this->otherKontrak->id]);
    }

    public function test_index_returns_relationship_data(): void
    {
        $response = $this->withToken($this->token($this->owner))->getJson('/api/v1/owner/kontrak');

        $response->assertOk()
            ->assertJsonPath('data.0.kos.id', $this->kos->id)
            ->assertJsonPath('data.0.kos.name', $this->kos->name)
            ->assertJsonPath('data.0.kamar.id', $this->room->id)
            ->assertJsonPath('data.0.penghuni.id', $this->tenantA->id)
            ->assertJsonPath('data.0.penghuni.name', $this->tenantA->name);
    }

    public function test_index_returns_empty_when_owner_has_no_contracts(): void
    {
        $emptyOwner = User::factory()->create(['role' => 'owner']);

        $response = $this->withToken($this->token($emptyOwner))->getJson('/api/v1/owner/kontrak');

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_index_uses_pagination_and_deterministic_ordering(): void
    {
        \DB::table('kontraks')->where('id', $this->kontrak->id)->update(['created_at' => '2026-02-01 10:00:00']);

        Kontrak::factory()->create([
            'penghuni_id' => $this->penghuniA->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->room->id,
            'contract_number' => 'KT-FIRST',
            'created_at' => '2026-01-01 10:00:00',
        ]);
        Kontrak::factory()->create([
            'penghuni_id' => $this->penghuniA->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->room->id,
            'contract_number' => 'KT-LAST',
            'created_at' => '2026-03-01 10:00:00',
        ]);

        $response = $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/owner/kontrak?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('data.0.contract_number', 'KT-LAST')
            ->assertJsonPath('data.1.contract_number', $this->kontrak->contract_number);
    }

    // ------------------------------------------------------------------- show

    public function test_show_returns_own_contract_detail(): void
    {
        $response = $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kontrak/{$this->kontrak->id}");

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [
                    'id', 'contract_number', 'penghuni', 'kos', 'kamar',
                    'rental_type', 'rental_price', 'start_date', 'end_date', 'status', 'notes',
                ],
            ])
            ->assertJsonPath('data.id', $this->kontrak->id);
    }

    public function test_show_rejects_other_owner_contract(): void
    {
        $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kontrak/{$this->otherKontrak->id}")
            ->assertStatus(403);
    }

    public function test_show_returns_404_for_missing_contract(): void
    {
        $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/owner/kontrak/99999')
            ->assertStatus(404);
    }

    // -------------------------------------------------------------------- tagihan

    public function test_tagihan_returns_bills_of_own_contract(): void
    {
        $bill = $this->bill($this->kontrak);

        $response = $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kontrak/{$this->kontrak->id}/tagihan");

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id', 'bill_number', 'kontrak_id', 'penghuni', 'total', 'status',
                ]],
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kontrak_id', $this->kontrak->id)
            ->assertJsonFragment(['id' => $bill->id]);
    }

    public function test_tagihan_returns_empty_when_contract_has_no_bills(): void
    {
        $response = $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kontrak/{$this->kontrak->id}/tagihan");

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_tagihan_rejects_other_owner_contract(): void
    {
        $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kontrak/{$this->otherKontrak->id}/tagihan")
            ->assertStatus(403);
    }

    public function test_tagihan_uses_pagination(): void
    {
        $this->bill($this->kontrak);
        $this->bill($this->kontrak);
        $this->bill($this->kontrak);

        $response = $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kontrak/{$this->kontrak->id}/tagihan?per_page=2");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2);
    }

    // ---------------------------------------------------------- cross-owner security

    public function test_owner_a_cannot_access_owner_b_contract_or_bills(): void
    {
        $this->bill($this->otherKontrak);

        $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kontrak/{$this->otherKontrak->id}")
            ->assertStatus(403);

        $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kontrak/{$this->otherKontrak->id}/tagihan")
            ->assertStatus(403);
    }

    public function test_owner_b_cannot_access_owner_a_contract_or_bills(): void
    {
        $this->bill($this->kontrak);

        $this->withToken($this->token($this->otherOwner))
            ->getJson("/api/v1/owner/kontrak/{$this->kontrak->id}")
            ->assertStatus(403);

        $this->withToken($this->token($this->otherOwner))
            ->getJson("/api/v1/owner/kontrak/{$this->kontrak->id}/tagihan")
            ->assertStatus(403);
    }

    public function test_nested_bills_do_not_leak_other_owner_bills(): void
    {
        $ownBill = $this->bill($this->kontrak, ['bill_number' => 'BL-A']);
        $foreignBill = $this->bill($this->otherKontrak, ['bill_number' => 'BL-B']);

        $response = $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kontrak/{$this->kontrak->id}/tagihan");

        $response->assertOk()
            ->assertJsonFragment(['id' => $ownBill->id])
            ->assertJsonMissing(['id' => $foreignBill->id])
            ->assertJsonMissing(['bill_number' => 'BL-B']);
    }

    public function test_response_does_not_include_sensitive_payload(): void
    {
        $this->bill($this->kontrak);

        $response = $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/owner/kontrak');

        $response->assertOk()
            ->assertJsonMissing(['id' => $this->otherKontrak->id])
            ->assertJsonMissingPath('data.0.penghuni.identity_number');
    }
}
