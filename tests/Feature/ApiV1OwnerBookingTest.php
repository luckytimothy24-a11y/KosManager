<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1OwnerBookingTest extends TestCase
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
    }

    private function booking(User $tenant, Kamar $room, array $overrides = []): Booking
    {
        return Booking::factory()->create(array_merge([
            'user_id' => $tenant->id,
            'kos_id' => $room->kos_id,
            'kamar_id' => $room->id,
            'status' => 'approved',
        ], $overrides));
    }

    // ------------------------------------------------------------ authentication

    public function test_owner_booking_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/owner/bookings')->assertStatus(401);
        $this->getJson('/api/v1/owner/bookings/1')->assertStatus(401);
    }

    public function test_owner_booking_routes_reject_non_owner_roles(): void
    {
        $existing = $this->booking($this->tenantA, $this->room);

        foreach ([
            'tenant' => $this->tenantA,
            'admin' => $this->admin,
            'super_admin' => $this->superAdmin,
        ] as $label => $user) {
            $this->withToken($this->token($user))->getJson('/api/v1/owner/bookings')->assertStatus(403);
            $this->withToken($this->token($user))->getJson("/api/v1/owner/bookings/{$existing->id}")->assertStatus(403);
        }
    }

    // ---------------------------------------------------------------- index

    public function test_index_returns_only_own_property_bookings(): void
    {
        $ownBooking = $this->booking($this->tenantA, $this->room);
        $foreignBooking = $this->booking($this->tenantB, $this->otherRoom);

        $response = $this->withToken($this->token($this->owner))->getJson('/api/v1/owner/bookings');

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id', 'booking_code', 'tenant', 'kos', 'kamar', 'status',
                ]],
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $ownBooking->id])
            ->assertJsonMissing(['id' => $foreignBooking->id]);
    }

    public function test_index_returns_relationship_data(): void
    {
        $this->booking($this->tenantA, $this->room);

        $response = $this->withToken($this->token($this->owner))->getJson('/api/v1/owner/bookings');

        $response->assertOk()
            ->assertJsonPath('data.0.kos.id', $this->kos->id)
            ->assertJsonPath('data.0.kos.name', $this->kos->name)
            ->assertJsonPath('data.0.kamar.id', $this->room->id)
            ->assertJsonPath('data.0.tenant.id', $this->tenantA->id)
            ->assertJsonPath('data.0.tenant.name', $this->tenantA->name);
    }

    public function test_index_returns_empty_when_owner_has_no_bookings(): void
    {
        $emptyOwner = User::factory()->create(['role' => 'owner']);

        $response = $this->withToken($this->token($emptyOwner))->getJson('/api/v1/owner/bookings');

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_index_uses_pagination(): void
    {
        Booking::factory()->count(3)->create([
            'user_id' => $this->tenantA->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->room->id,
        ]);

        $response = $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/owner/bookings?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_index_is_deterministic_latest_ordering(): void
    {
        $this->booking($this->tenantA, $this->room, ['booking_code' => 'BK-FIRST', 'created_at' => '2026-01-01 10:00:00']);
        $this->booking($this->tenantA, $this->room, ['booking_code' => 'BK-SECOND', 'created_at' => '2026-02-01 10:00:00']);

        $response = $this->withToken($this->token($this->owner))->getJson('/api/v1/owner/bookings');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.booking_code', 'BK-SECOND')
            ->assertJsonPath('data.1.booking_code', 'BK-FIRST');
    }

    // ------------------------------------------------------------------- show

    public function test_show_returns_own_property_booking(): void
    {
        $ownBooking = $this->booking($this->tenantA, $this->room);

        $response = $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/bookings/{$ownBooking->id}");

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [
                    'id', 'booking_code', 'tenant', 'kos', 'kamar',
                    'booking_date', 'start_date', 'end_date', 'rental_type', 'price', 'status', 'notes',
                ],
            ])
            ->assertJsonPath('data.id', $ownBooking->id);
    }

    public function test_show_rejects_other_owner_booking(): void
    {
        $foreignBooking = $this->booking($this->tenantB, $this->otherRoom);

        $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/bookings/{$foreignBooking->id}")
            ->assertStatus(403);
    }

    public function test_show_returns_404_for_missing_booking(): void
    {
        $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/owner/bookings/99999')
            ->assertStatus(404);
    }

    // ---------------------------------------------------------- cross-owner security

    public function test_owner_a_can_access_own_but_not_owner_b_in_single_authenticated_request(): void
    {
        $bookingOwnerA = $this->booking($this->tenantA, $this->room);
        $bookingOwnerB = $this->booking($this->tenantB, $this->otherRoom);

        $index = $this->withToken($this->token($this->owner))->getJson('/api/v1/owner/bookings');
        $index->assertOk()
            ->assertJsonFragment(['id' => $bookingOwnerA->id])
            ->assertJsonMissing(['id' => $bookingOwnerB->id]);

        $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/bookings/{$bookingOwnerA->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $bookingOwnerA->id);

        $this->withToken($this->token($this->owner))
            ->getJson("/api/v1/owner/bookings/{$bookingOwnerB->id}")
            ->assertStatus(403);
    }

    public function test_owner_b_cannot_access_owner_a_booking_but_can_access_own(): void
    {
        $bookingOwnerA = $this->booking($this->tenantA, $this->room);
        $bookingOwnerB = $this->booking($this->tenantB, $this->otherRoom);

        $index = $this->withToken($this->token($this->otherOwner))->getJson('/api/v1/owner/bookings');
        $index->assertOk()
            ->assertJsonFragment(['id' => $bookingOwnerB->id])
            ->assertJsonMissing(['id' => $bookingOwnerA->id]);

        $this->withToken($this->token($this->otherOwner))
            ->getJson("/api/v1/owner/bookings/{$bookingOwnerA->id}")
            ->assertStatus(403);

        $this->withToken($this->token($this->otherOwner))
            ->getJson("/api/v1/owner/bookings/{$bookingOwnerB->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $bookingOwnerB->id);
    }

    public function test_response_does_not_leak_foreign_booking_or_sensitive_data(): void
    {
        $foreignBooking = $this->booking($this->tenantB, $this->otherRoom, ['notes' => 'internal-owner-B']);

        $response = $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/owner/bookings');

        $response->assertOk()
            ->assertJsonMissing(['id' => $foreignBooking->id])
            ->assertJsonMissing(['notes' => 'internal-owner-B']);
    }
}
