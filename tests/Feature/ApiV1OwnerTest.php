<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1OwnerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $otherOwner;

    private User $tenant;

    private User $admin;

    private User $superAdmin;

    private Kos $kos;

    private Kos $otherKos;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->otherOwner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->otherKos = Kos::factory()->create(['owner_id' => $this->otherOwner->id]);
    }

    // ------------------------------------------------------------ authentication

    public function test_owner_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/owner/kos')->assertStatus(401);
        $this->getJson('/api/v1/owner/kos/1')->assertStatus(401);
        $this->getJson('/api/v1/owner/kos/1/kamar')->assertStatus(401);
        $this->getJson('/api/v1/owner/dashboard')->assertStatus(401);
    }

    public function test_owner_routes_reject_non_owner_roles(): void
    {
        $roles = [
            'tenant' => $this->tenant,
            'admin' => $this->admin,
            'super_admin' => $this->superAdmin,
        ];

        foreach ($roles as $label => $user) {
            $this->withToken($this->token($user))->getJson('/api/v1/owner/kos')->assertStatus(403);
            $this->withToken($this->token($user))->getJson('/api/v1/owner/dashboard')->assertStatus(403);
        }
    }

    // ---------------------------------------------------------------- owner kos index

    public function test_index_returns_only_own_kos(): void
    {
        $response = $this->withToken($this->token($this->owner))->getJson('/api/v1/owner/kos');

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure(['data' => [['id', 'name', 'address', 'status', 'kamar_count', 'penghuni_count']]])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $this->kos->id])
            ->assertJsonMissing(['id' => $this->otherKos->id]);
    }

    public function test_index_returns_empty_when_owner_has_no_kos(): void
    {
        $emptyOwner = User::factory()->create(['role' => 'owner']);

        $response = $this->withToken($this->token($emptyOwner))->getJson('/api/v1/owner/kos');

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_index_uses_default_and_capped_per_page(): void
    {
        $response = $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/owner/kos?per_page=1');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    // ------------------------------------------------------------------- owner kos show

    public function test_show_returns_own_kos_detail(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);

        $response = $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kos/{$this->kos->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'address', 'status', 'kamar_count', 'penghuni_count', 'bookings_count',
                ],
            ])
            ->assertJsonPath('data.id', $this->kos->id)
            ->assertJsonPath('data.kamar_count', 1);
    }

    public function test_show_rejects_other_owner_kos(): void
    {
        $this->withToken($this->token($this->otherOwner))
            ->getJson("/api/v1/owner/kos/{$this->kos->id}")
            ->assertStatus(403);
    }

    public function test_show_returns_404_for_missing_kos(): void
    {
        $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/owner/kos/99999')
            ->assertStatus(404);
    }

    // ---------------------------------------------------------------- owner kos kamar

    public function test_kamar_returns_own_kos_rooms(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);

        $response = $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kos/{$this->kos->id}/kamar");

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'kos_id', 'room_name', 'status', 'monthly_price']]])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['kos_id' => $this->kos->id, 'id' => $kamar->id]);
    }

    public function test_kamar_returns_empty_for_kos_without_rooms(): void
    {
        $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kos/{$this->kos->id}/kamar")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_kamar_rejects_other_owner_kos(): void
    {
        Kamar::factory()->create(['kos_id' => $this->kos->id]);

        $this->withToken($this->token($this->otherOwner))
            ->getJson("/api/v1/owner/kos/{$this->kos->id}/kamar")
            ->assertStatus(403);
    }

    public function test_kamar_returns_only_parent_kos_rooms(): void
    {
        $this->kosKamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $this->otherKosKamar = Kamar::factory()->create(['kos_id' => $this->otherKos->id]);

        $response = $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kos/{$this->kos->id}/kamar");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $this->kosKamar->id])
            ->assertJsonMissing(['id' => $this->otherKosKamar->id]);
    }

    public function test_kamar_uses_pagination(): void
    {
        Kamar::factory()->count(3)->create(['kos_id' => $this->kos->id]);

        $response = $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/kos/{$this->kos->id}/kamar?per_page=2");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2);
    }

    // ---------------------------------------------------------------- owner dashboard

    public function test_dashboard_returns_owner_scoped_statistics(): void
    {
        $ownKos = $this->kos;
        $ownRoom = Kamar::factory()->create(['kos_id' => $ownKos->id, 'status' => 'occupied']);

        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $ownKos->id,
            'kamar_id' => $ownRoom->id,
            'status' => 'active',
        ]);

        Pembayaran::factory()->create([
            'penghuni_id' => $penghuni->id,
            'verification_status' => 'approved',
            'amount' => 1500000,
        ]);

        $response = $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/owner/dashboard');

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [
                    'kos' => ['total'],
                    'kamar' => ['total', 'available', 'occupied', 'maintenance'],
                    'penghuni' => ['active'],
                    'booking' => ['needs_checkin'],
                    'billing' => ['pending_payments', 'tagihan_outstanding', 'tagihan_overdue'],
                    'finance' => ['total_revenue'],
                ],
            ])
            ->assertJsonPath('data.kos.total', 1)
            ->assertJsonPath('data.kamar.total', 1)
            ->assertJsonPath('data.kamar.occupied', 1)
            ->assertJsonPath('data.kamar.available', 0)
            ->assertJsonPath('data.penghuni.active', 1)
            ->assertJsonPath('data.finance.total_revenue', 1500000);
    }

    public function test_dashboard_excludes_other_owner_data(): void
    {
        $foreignKos = $this->otherKos;
        $foreignRoom = Kamar::factory()->create(['kos_id' => $foreignKos->id, 'status' => 'occupied']);
        $foreignTenant = User::factory()->create(['role' => 'tenant']);

        Penghuni::factory()->create([
            'user_id' => $foreignTenant->id,
            'kos_id' => $foreignKos->id,
            'kamar_id' => $foreignRoom->id,
            'status' => 'active',
        ]);

        $response = $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/owner/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.kos.total', 1)
            ->assertJsonPath('data.kamar.total', 0)
            ->assertJsonPath('data.penghuni.active', 0);
    }

    public function test_dashboard_returns_valid_empty_state(): void
    {
        $emptyOwner = User::factory()->create(['role' => 'owner']);

        $response = $this->withToken($this->token($emptyOwner))
            ->getJson('/api/v1/owner/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.kos.total', 0)
            ->assertJsonPath('data.kamar.total', 0)
            ->assertJsonPath('data.kamar.available', 0)
            ->assertJsonPath('data.kamar.occupied', 0)
            ->assertJsonPath('data.penghuni.active', 0)
            ->assertJsonPath('data.finance.total_revenue', 0);
    }

    public function test_dashboard_counts_outstanding_and_overdue_tagihan(): void
    {
        $ownKos = $this->kos;
        $ownRoom = Kamar::factory()->create(['kos_id' => $ownKos->id, 'status' => 'occupied']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $ownKos->id,
            'kamar_id' => $ownRoom->id,
            'status' => 'active',
        ]);

        Tagihan::factory()->create(['penghuni_id' => $penghuni->id, 'kamar_id' => $ownRoom->id, 'status' => 'unpaid']);
        Tagihan::factory()->create(['penghuni_id' => $penghuni->id, 'kamar_id' => $ownRoom->id, 'status' => 'paid']);

        $response = $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/owner/dashboard');

        // payable() = unpaid + overdue → hanya 'unpaid' yang outstanding; 'paid' tidak dihitung.
        $response->assertOk()
            ->assertJsonPath('data.billing.tagihan_outstanding', 1)
            ->assertJsonPath('data.billing.tagihan_overdue', 0);
    }

    // ------------------------------------------------------------------ security

    public function test_owner_a_cannot_access_owner_b_property_listing(): void
    {
        $response = $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/owner/kos');

        $response->assertOk()->assertJsonMissing(['id' => $this->otherKos->id]);
    }

    public function test_owner_b_cannot_view_owner_a_kos(): void
    {
        $this->withToken($this->token($this->otherOwner))
            ->getJson("/api/v1/owner/kos/{$this->kos->id}")
            ->assertStatus(403);
    }
}
