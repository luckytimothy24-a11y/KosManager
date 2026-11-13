<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase34CapacityErrorTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'pencari']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
    }

    private function personalKamar(): Kamar
    {
        return Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'capacity' => 1,
            'billing_mode' => 'personal',
            'monthly_price' => 1000000,
            'daily_price' => 50000,
            'weekly_price' => 300000,
        ]);
    }

    private function bookPayload(Kamar $kamar, int $requested, string $billing = 'personal'): array
    {
        return [
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => $billing,
            'requested_occupants' => $requested,
        ];
    }

    public function test_personal_multi_rejected_human_message(): void
    {
        $kamar = $this->personalKamar();
        $svc = app(BookingService::class);
        $r = $svc->create($this->bookPayload($kamar, 2), $this->tenant->id);
        $this->assertFalse($r['ok']);
        $this->assertSame('personal_multi_rejected', $r['error']);

        // Web: request divalidasi sebelum service, pesan ramah di field requested_occupants.
        $resp = $this->actingAs($this->tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
            'requested_occupants' => 2,
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl Test No 1',
        ]);
        $resp->assertSessionHasErrors('requested_occupants');

        // API juga menolak dengan pesan validasi yang sama.
        $token = $this->tenant->createToken('test')->plainTextToken;
        $api = $this->withToken($token)->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
            'requested_occupants' => 2,
        ]);
        $api->assertStatus(422);
        $api->assertJsonValidationErrors('requested_occupants');
    }

    public function test_shared_billing_mode_rejected(): void
    {
        $kamar = $this->personalKamar();
        $svc = app(BookingService::class);
        $r = $svc->create($this->bookPayload($kamar, 1, 'shared'), $this->tenant->id);
        $this->assertFalse($r['ok']);
        $this->assertSame('billing_mode_not_allowed', $r['error']);

        $token = $this->tenant->createToken('shared')->plainTextToken;
        $api = $this->withToken($token)->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'requested_occupants' => 1,
        ]);
        $api->assertStatus(422);
        $api->assertJsonValidationErrors('billing_mode');
    }

    public function test_occupied_room_is_unavailable(): void
    {
        $kamar = $this->personalKamar();
        $penghuni = Penghuni::factory()->create([
            'user_id' => User::factory()->create(['role' => 'pencari'])->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $kamar->update(['status' => 'occupied']);

        $svc = app(BookingService::class);
        $r = $svc->create($this->bookPayload($kamar, 1), $this->tenant->id);

        $this->assertFalse($r['ok']);
        $this->assertSame('kamar_tidak_tersedia', $r['error']);
    }

    public function test_personal_multi_regression(): void
    {
        $kamar = $this->personalKamar();
        $svc = app(BookingService::class);
        $r = $svc->create($this->bookPayload($kamar, 2), $this->tenant->id);
        $this->assertFalse($r['ok']);
        $this->assertSame('personal_multi_rejected', $r['error']);
    }

    public function test_identifier_stability_and_no_leak(): void
    {
        $kamar = $this->personalKamar();
        $svc = app(BookingService::class);
        $r = $svc->create($this->bookPayload($kamar, 2), $this->tenant->id);
        $this->assertSame('personal_multi_rejected', $r['error']);

        $token = $this->tenant->createToken('t5')->plainTextToken;
        $api = $this->withToken($token)->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
            'requested_occupants' => 2,
        ]);
        $apiMsg = $api->json('errors.requested_occupants.0') ?? '';
        $this->assertStringNotContainsString('personal_multi_rejected', $apiMsg);
        $this->assertStringNotContainsString($this->owner->id.'', $apiMsg);
        // Only aggregate numbers, not emails
        $this->assertStringNotContainsString('@', $apiMsg);
        $this->assertStringNotContainsString('booking_id', strtolower($apiMsg));
    }
}
