<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class KamarCapacityTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $otherOwner;

    private Kos $kos;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->otherOwner = User::factory()->create(['role' => 'owner']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'kos_id' => $this->kos->id,
            'room_number' => '101',
            'room_name' => 'Kamar Mawar',
            'room_type' => 'standard',
            'daily_price' => 50000,
            'weekly_price' => 300000,
            'monthly_price' => 1000000,
        ], $overrides);
    }

    private function addPenghuniPayload(User $user, array $overrides = []): array
    {
        return array_merge([
            'user_id' => $user->id,
            'rental_type' => 'monthly',
            'rental_price' => 1000000,
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'check_in_date' => now()->toDateString(),
        ], $overrides);
    }

    public function test_kamar_can_be_created_with_capacity(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), $this->validPayload(['capacity' => 3]));

        $response->assertRedirect(route('owner.kamar.index'));
        $this->assertDatabaseHas('kamar', ['kos_id' => $this->kos->id, 'room_number' => '101', 'capacity' => 3]);
    }

    public function test_kamar_defaults_capacity_to_one(): void
    {
        $this->actingAs($this->owner)->post(route('owner.kamar.store'), $this->validPayload());

        $this->assertDatabaseHas('kamar', ['kos_id' => $this->kos->id, 'room_number' => '101', 'capacity' => 1]);
    }

    public function test_kamar_capacity_must_be_at_least_one(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), $this->validPayload(['capacity' => 0]));

        $response->assertSessionHasErrors('capacity');
    }

    public function test_kamar_capacity_must_not_exceed_maximum(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), $this->validPayload(['capacity' => 21]));

        $response->assertSessionHasErrors('capacity');
    }

    public function test_kamar_can_be_updated_with_capacity(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'capacity' => 1]);

        $response = $this->actingAs($this->owner)->put(route('owner.kamar.update', $kamar), $this->validPayload([
            'room_number' => '202',
            'capacity' => 4,
        ]));

        $response->assertRedirect(route('owner.kamar.index'));
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'capacity' => 4]);
    }

    public function test_owner_can_add_penghuni_until_capacity_full(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'capacity' => 2, 'status' => 'available']);
        $first = User::factory()->create(['role' => 'tenant']);
        $second = User::factory()->create(['role' => 'tenant']);
        $third = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), $this->addPenghuniPayload($first))
            ->assertRedirect(route('owner.kamar.show', $kamar));
        $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), $this->addPenghuniPayload($second))
            ->assertRedirect(route('owner.kamar.show', $kamar));

        $response = $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), $this->addPenghuniPayload($third));

        $response->assertStatus(400);
        $this->assertDatabaseCount('penghunis', 2);
    }

    public function test_add_penghuni_creates_penghuni_and_active_kontrak(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'capacity' => 1, 'status' => 'available']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), $this->addPenghuniPayload($tenant));

        $this->assertDatabaseHas('penghunis', [
            'user_id' => $tenant->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        $penghuni = Penghuni::where('user_id', $tenant->id)->first();
        $this->assertDatabaseHas('kontraks', [
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'rental_type' => 'monthly',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'occupied']);
    }

    public function test_cannot_add_same_tenant_twice(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'capacity' => 2]);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), $this->addPenghuniPayload($tenant))
            ->assertRedirect(route('owner.kamar.show', $kamar));

        $response = $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), $this->addPenghuniPayload($tenant));

        $response->assertStatus(422);
    }

    public function test_add_penghuni_requires_tenant_role(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), $this->addPenghuniPayload($admin));

        $response->assertStatus(422);
        $this->assertDatabaseCount('penghunis', 0);
    }

    public function test_non_authorized_owner_cannot_add_penghuni(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($this->otherOwner)->post(route('owner.kamar.add-penghuni', $kamar), $this->addPenghuniPayload($tenant));

        $response->assertForbidden();
    }

    public function test_checkin_rejected_when_kamar_at_full_capacity(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'capacity' => 1, 'status' => 'booked']);
        $bookingTenant = User::factory()->create(['role' => 'tenant']);
        $existing = User::factory()->create(['role' => 'tenant']);
        Penghuni::factory()->create([
            'user_id' => $existing->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $booking = Booking::factory()->create([
            'user_id' => $bookingTenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
            'identity_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.checkin.process', $booking), [
            'notes' => 'Check-in lancar',
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseCount('penghunis', 1);
    }

    public function test_owner_can_view_capacity_on_kamar_show(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'capacity' => 3]);

        $response = $this->actingAs($this->owner)->get(route('owner.kamar.show', $kamar));

        $response->assertOk();
        $response->assertSee('Kapasitas');
        $response->assertSee('0 / 3');
    }
}
