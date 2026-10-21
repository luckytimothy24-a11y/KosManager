<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingRejectedWhenKamarFullTest extends TestCase
{
    use RefreshDatabase;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_booking_rejected_when_kamar_full(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create([
            'kos_id' => $kos->id,
            'status' => 'available',
            'capacity' => 1,
            'billing_mode' => 'personal',
            'monthly_price' => 1500000,
            'daily_price' => 100000,
        ]);

        $occupantUser = User::factory()->create(['role' => 'tenant']);
        Penghuni::factory()->create([
            'user_id' => $occupantUser->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $kamar->update(['status' => 'occupied']);
        $kamar->refresh();

        $this->assertSame(1, (int) $kamar->capacity);
        $this->assertSame('personal', $kamar->billing_mode);
        $this->assertSame('occupied', $kamar->status);
        $this->assertTrue($kamar->isFull());
        $this->assertSame(1, Penghuni::where('kamar_id', $kamar->id)->where('status', 'active')->count());

        $response = $this->withToken($this->token($tenant))->postJson('/api/v1/bookings', [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors' => ['kamar_id']]);

        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'occupied']);
        $this->assertDatabaseHas('penghunis', ['kamar_id' => $kamar->id, 'status' => 'active']);
        $this->assertDatabaseCount('pembayarans', 0);
        $this->assertDatabaseCount('kontraks', 0);
    }
}
