<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CheckIn;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KamarDeletionProtectionTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Kos $kos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
    }

    private function createOccupiedKamar(array $kamarAttributes = []): array
    {
        $kamar = Kamar::factory()->create(array_merge([
            'kos_id' => $this->kos->id,
            'status' => 'occupied',
        ], $kamarAttributes));

        $tenant = User::factory()->create(['role' => 'tenant']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        return [$kamar, $penghuni];
    }

    public function test_kamar_with_paid_tagihan_history_cannot_be_deleted(): void
    {
        [$kamar, $penghuni] = $this->createOccupiedKamar();

        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => Kontrak::factory()->create([
                'penghuni_id' => $penghuni->id,
                'kos_id' => $this->kos->id,
                'kamar_id' => $kamar->id,
                'status' => 'expired',
            ])->id,
            'kamar_id' => $kamar->id,
            'status' => 'paid',
        ]);

        $penghuni->update(['status' => 'inactive']);
        $kamar->update(['status' => 'available']);

        $response = $this->actingAs($this->owner)->delete(route('owner.kamar.destroy', $kamar));

        $response->assertRedirect(route('owner.kamar.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id]);
    }

    public function test_kamar_with_checkin_history_cannot_be_deleted(): void
    {
        [$kamar, $penghuni] = $this->createOccupiedKamar();

        CheckIn::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'check_in_date' => now(),
            'check_in_time' => now()->format('H:i'),
            'officer_id' => $this->owner->id,
        ]);

        $penghuni->update(['status' => 'inactive']);
        $kamar->update(['status' => 'available']);

        $response = $this->actingAs($this->owner)->delete(route('owner.kamar.destroy', $kamar));

        $response->assertRedirect(route('owner.kamar.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id]);
    }

    public function test_kamar_with_cancelled_booking_history_cannot_be_deleted(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $tenant = User::factory()->create(['role' => 'tenant']);

        Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'cancelled',
        ]);

        $response = $this->actingAs($this->owner)->delete(route('owner.kamar.destroy', $kamar));

        $response->assertRedirect(route('owner.kamar.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id]);
    }

    public function test_cannot_set_occupied_kamar_to_available_while_penghuni_active(): void
    {
        [$kamar] = $this->createOccupiedKamar();

        $response = $this->actingAs($this->owner)->put(route('owner.kamar.update', $kamar), [
            'room_number' => $kamar->room_number,
            'room_name' => $kamar->room_name,
            'room_type' => $kamar->room_type,
            'daily_price' => 50000,
            'monthly_price' => 1000000,
            'status' => 'available',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('status');

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'occupied']);
    }

    public function test_maintenance_kamar_without_penghuni_can_be_set_available(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'maintenance']);

        $response = $this->actingAs($this->owner)->put(route('owner.kamar.update', $kamar), [
            'room_number' => $kamar->room_number,
            'room_name' => $kamar->room_name,
            'room_type' => $kamar->room_type,
            'daily_price' => 50000,
            'monthly_price' => 1000000,
            'status' => 'available',
        ]);

        $response->assertRedirect(route('owner.kamar.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'available']);
    }

    public function test_super_admin_can_manage_kamar(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $index = $this->actingAs($superAdmin)->get(route('owner.kamar.index'));
        $index->assertOk();

        $store = $this->actingAs($superAdmin)->post(route('owner.kamar.store'), [
            'kos_id' => $this->kos->id,
            'room_number' => '999',
            'room_name' => 'Kamar Super',
            'room_type' => 'standard',
            'daily_price' => 50000,
            'monthly_price' => 1000000,
        ]);
        $store->assertRedirect(route('owner.kamar.index'));
        $store->assertSessionHas('success');

        $kamar = Kamar::where('kos_id', $this->kos->id)->where('room_number', '999')->firstOrFail();

        $update = $this->actingAs($superAdmin)->put(route('owner.kamar.update', $kamar), [
            'room_number' => '999',
            'room_name' => 'Kamar Super Updated',
            'room_type' => 'standard',
            'daily_price' => 50000,
            'monthly_price' => 1200000,
        ]);
        $update->assertRedirect(route('owner.kamar.index'));

        $destroy = $this->actingAs($superAdmin)->delete(route('owner.kamar.destroy', $kamar));
        $destroy->assertRedirect(route('owner.kamar.index'));

        $this->assertSoftDeleted('kamar', ['id' => $kamar->id]);
    }

    public function test_super_admin_cannot_create_kamar_in_nonexistent_kos(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $store = $this->actingAs($superAdmin)->post(route('owner.kamar.store'), [
            'kos_id' => 99999,
            'room_number' => '999',
            'room_name' => 'Kamar Ghost',
            'room_type' => 'standard',
            'daily_price' => 50000,
            'monthly_price' => 1000000,
        ]);

        $store->assertRedirect();
        $store->assertSessionHasErrors('kos_id');
        $this->assertDatabaseMissing('kamar', ['room_number' => '999']);
    }
}
