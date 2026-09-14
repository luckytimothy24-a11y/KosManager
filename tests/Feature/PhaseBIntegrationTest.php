<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\BookingService;
use App\Services\SharedBillingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseBIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenantA;

    private User $tenantB;

    private User $tenantC;

    private Kos $kos;

    private SharedBillingService $shared;

    protected function setUp(): void
    {
        parent::setUp();
        $this->shared = new SharedBillingService;
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenantA = User::factory()->create(['role' => 'tenant']);
        $this->tenantB = User::factory()->create(['role' => 'tenant']);
        $this->tenantC = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
    }

    private function kamar(array $overrides = []): Kamar
    {
        return Kamar::factory()->create(array_merge([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'billing_mode' => 'personal',
            'capacity' => 5,
            'monthly_price' => 1200000,
            'daily_price' => 50000,
            'weekly_price' => 350000,
        ], $overrides));
    }

    // ---------------------------------------------------------------- Booking

    public function test_booking_personal_room_personal_booking_pass(): void
    {
        $kamar = $this->kamar(['billing_mode' => 'personal']);
        $svc = app(BookingService::class);
        $result = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
        ], $this->tenantA->id);
        $this->assertTrue($result['ok']);
        $this->assertEquals('personal', $result['booking']->billing_mode);
    }

    public function test_booking_personal_room_shared_rejected(): void
    {
        $kamar = $this->kamar(['billing_mode' => 'personal']);
        $svc = app(BookingService::class);
        $result = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
        ], $this->tenantA->id);
        $this->assertFalse($result['ok']);
        $this->assertEquals('billing_mode_not_allowed', $result['error']);
    }

    public function test_booking_shared_room_shared_pass(): void
    {
        $kamar = $this->kamar(['billing_mode' => 'shared']);
        $svc = app(BookingService::class);
        $result = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
        ], $this->tenantA->id);
        $this->assertTrue($result['ok']);
        $this->assertEquals('shared', $result['booking']->billing_mode);
    }

    public function test_booking_shared_room_personal_pass(): void
    {
        $kamar = $this->kamar(['billing_mode' => 'shared']);
        $svc = app(BookingService::class);
        $result = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
        ], $this->tenantA->id);
        $this->assertTrue($result['ok']);
        $this->assertEquals('personal', $result['booking']->billing_mode);
    }

    public function test_booking_invalid_billing_mode_rejected_via_validation(): void
    {
        $kamar = $this->kamar(['billing_mode' => 'shared']);
        $response = $this->actingAs($this->tenantA)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'invalid',
        ]);
        $response->assertSessionHasErrors('billing_mode');
    }

    public function test_booking_api_shared_on_personal_rejected(): void
    {
        $kamar = $this->kamar(['billing_mode' => 'personal']);
        $token = $this->tenantA->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors('billing_mode');
    }

    public function test_booking_default_is_personal(): void
    {
        $kamar = $this->kamar(['billing_mode' => 'shared']);
        $svc = app(BookingService::class);
        $result = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ], $this->tenantA->id);
        $this->assertTrue($result['ok']);
        $this->assertEquals('personal', $result['booking']->billing_mode);
    }

    // ---------------------------------------------------------------- Contract snapshot

    public function test_contract_snapshot_personal(): void
    {
        $kamar = $this->kamar(['billing_mode' => 'personal', 'status' => 'booked', 'capacity' => 5]);
        $booking = Booking::factory()->create([
            'user_id' => $this->tenantA->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
            'price' => 1200000,
            'status' => 'approved',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addMonths(2),
            'identity_confirmed_at' => now(),
            'identity_data_submitted_at' => now(),
            'identity_number' => '1234567890',
            'phone' => '08123456789',
            'address' => 'Jl Test',
        ]);
        $response = $this->actingAs($this->owner)->post(route('owner.checkin.process', $booking), ['notes' => 'ok']);
        $response->assertRedirect();
        $kontrak = Kontrak::where('kamar_id', $kamar->id)->latest()->first();
        $this->assertNotNull($kontrak);
        $this->assertEquals('personal', $kontrak->billing_mode);
    }

    public function test_contract_snapshot_shared(): void
    {
        $kamar = $this->kamar(['billing_mode' => 'shared', 'status' => 'booked', 'capacity' => 5]);
        $booking = Booking::factory()->create([
            'user_id' => $this->tenantA->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'price' => 1200000,
            'status' => 'approved',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addMonths(2),
            'identity_confirmed_at' => now(),
            'identity_data_submitted_at' => now(),
            'identity_number' => '1234567890',
            'phone' => '08123456789',
            'address' => 'Jl Test',
        ]);
        $this->actingAs($this->owner)->post(route('owner.checkin.process', $booking), ['notes' => 'ok'])->assertRedirect();
        $kontrak = Kontrak::where('kamar_id', $kamar->id)->latest()->first();
        $this->assertEquals('shared', $kontrak->billing_mode);
    }

    public function test_contract_snapshot_shared_capable_personal_choice(): void
    {
        $kamar = $this->kamar(['billing_mode' => 'shared', 'status' => 'booked', 'capacity' => 5]);
        $booking = Booking::factory()->create([
            'user_id' => $this->tenantA->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
            'price' => 1200000,
            'status' => 'approved',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addMonths(2),
            'identity_confirmed_at' => now(),
            'identity_data_submitted_at' => now(),
            'identity_number' => '1234567890',
            'phone' => '08123456789',
            'address' => 'Jl Test',
        ]);
        $this->actingAs($this->owner)->post(route('owner.checkin.process', $booking), ['notes' => 'ok'])->assertRedirect();
        $kontrak = Kontrak::where('kamar_id', $kamar->id)->latest()->first();
        $this->assertEquals('personal', $kontrak->billing_mode);
    }

    public function test_contract_snapshot_stable_after_kamar_change(): void
    {
        $kamar = $this->kamar(['billing_mode' => 'shared', 'status' => 'booked', 'capacity' => 5]);
        $booking = Booking::factory()->create([
            'user_id' => $this->tenantA->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'price' => 1200000,
            'status' => 'approved',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addMonths(2),
            'identity_confirmed_at' => now(),
            'identity_data_submitted_at' => now(),
            'identity_number' => '1234567890',
            'phone' => '08123456789',
            'address' => 'Jl Test',
        ]);
        $this->actingAs($this->owner)->post(route('owner.checkin.process', $booking), ['notes' => 'ok']);
        $kontrak = Kontrak::where('kamar_id', $kamar->id)->latest()->first();
        $kamar->update(['billing_mode' => 'personal']);
        $kontrak->refresh();
        $this->assertEquals('shared', $kontrak->billing_mode);
        $this->assertEquals('shared', $kontrak->effectiveBillingMode());
    }

    // ---------------------------------------------------------------- Helpers for Tagihan

    private function sharedKamarWithOccupants(int $count, float $price = 1200000, string $rentalType = 'monthly', string $billingMode = 'shared'): array
    {
        $kamar = $this->kamar([
            'billing_mode' => 'shared',
            'capacity' => 10,
            'monthly_price' => 1200000,
            'daily_price' => 50000,
            'weekly_price' => 350000,
            'status' => 'occupied',
        ]);
        $kontraks = [];
        foreach (range(1, $count) as $i) {
            $user = User::factory()->create(['role' => 'tenant']);
            $penghuni = Penghuni::factory()->create([
                'user_id' => $user->id,
                'kos_id' => $this->kos->id,
                'kamar_id' => $kamar->id,
                'status' => 'active',
            ]);
            $kontraks[] = Kontrak::factory()->create([
                'penghuni_id' => $penghuni->id,
                'kos_id' => $this->kos->id,
                'kamar_id' => $kamar->id,
                'rental_price' => $price,
                'rental_type' => $rentalType,
                'billing_mode' => $billingMode,
                'status' => 'active',
            ]);
        }

        return [$kamar, $kontraks];
    }

    // ---------------------------------------------------------------- Tagihan

    public function test_tagihan_personal_regression(): void
    {
        $kamar = $this->kamar(['billing_mode' => 'personal', 'monthly_price' => 1500000]);
        $penghuni = Penghuni::factory()->create(['user_id' => $this->tenantA->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id,
            'rental_type' => 'monthly', 'rental_price' => 1500000, 'billing_mode' => 'personal', 'status' => 'active',
        ]);
        $response = $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontrak->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => now()->startOfMonth()->format('Y-m-d'),
            'period_end' => now()->endOfMonth()->format('Y-m-d'),
            'discount' => 0,
            'penalty' => 0,
            'due_date' => now()->endOfMonth()->format('Y-m-d'),
        ]);
        $response->assertRedirect(route('owner.tagihan.index'));
        $this->assertDatabaseCount('tagihans', 1);
        $this->assertDatabaseHas('tagihans', ['kontrak_id' => $kontrak->id, 'total' => 1500000]);
    }

    public function test_tagihan_shared_generates_for_all(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(3);
        $response = $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'discount' => 0,
            'penalty' => 0,
            'due_date' => '2026-10-05',
        ]);
        $response->assertRedirect(route('owner.tagihan.index'));
        $this->assertDatabaseCount('tagihans', 3);
        $tags = Tagihan::all();
        foreach ($tags as $t) {
            $this->assertEquals(400000.0, (float) $t->subtotal);
            $this->assertEquals(1200000.0, (float) $t->total_room_bill);
        }
    }

    public function test_tagihan_shared_each_occupant_own_share(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(3);
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'due_date' => '2026-10-05',
        ]);
        foreach ($kontraks as $k) {
            $tag = Tagihan::where('kontrak_id', $k->id)->first();
            $this->assertNotNull($tag);
            $this->assertEquals($k->penghuni_id, $tag->penghuni_id);
        }
    }

    public function test_tagihan_shared_sum_equals_total(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(3);
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'due_date' => '2026-10-05',
        ]);
        $sum = Tagihan::sum('subtotal');
        $this->assertEqualsWithDelta(1200000.0, (float) $sum, 0.01);
        $first = Tagihan::first();
        $this->assertEqualsWithDelta((float) $first->total_room_bill, (float) $sum, 0.01);
    }

    public function test_tagihan_shared_equal_split(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(2);
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'due_date' => '2026-10-05',
        ]);
        $subs = Tagihan::orderBy('kontrak_id')->pluck('subtotal')->map(fn ($v) => (float) $v)->toArray();
        $this->assertEquals([600000.0, 600000.0], $subs);
    }

    public function test_tagihan_rounding_100000_div_3(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(3, 100000);
        $kamar->update(['monthly_price' => 100000]);
        // kontrak rental_price not used for shared total, kamar price is source
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'due_date' => '2026-10-05',
        ]);
        $sum = (float) Tagihan::sum('subtotal');
        $this->assertEqualsWithDelta(100000.0, $sum, 0.01);
    }

    public function test_tagihan_custom_shares_valid(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(3);
        $kontraks[0]->update(['custom_share_amount' => 500000]);
        $kontraks[1]->update(['custom_share_amount' => 400000]);
        $kontraks[2]->update(['custom_share_amount' => 300000]);
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'due_date' => '2026-10-05',
        ])->assertRedirect(route('owner.tagihan.index'));
        $subs = Tagihan::orderBy('subtotal')->pluck('subtotal')->map(fn ($v) => (float) $v)->toArray();
        $this->assertEquals([300000.0, 400000.0, 500000.0], $subs);
    }

    public function test_tagihan_custom_shares_invalid_rejected(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(3);
        $kontraks[0]->update(['custom_share_amount' => 500000]);
        $kontraks[1]->update(['custom_share_amount' => 400000]);
        $kontraks[2]->update(['custom_share_amount' => 200000]); // sum 1.1m != 1.2m
        $response = $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'due_date' => '2026-10-05',
        ]);
        $response->assertSessionHasErrors('kontrak_id');
        $this->assertDatabaseCount('tagihans', 0);
    }

    public function test_tagihan_custom_shares_over_total_rejected(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(3);
        $kontraks[0]->update(['custom_share_amount' => 500000]);
        $kontraks[1]->update(['custom_share_amount' => 400000]);
        $kontraks[2]->update(['custom_share_amount' => 400000]); // sum 1.3m
        $response = $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'due_date' => '2026-10-05',
        ]);
        $response->assertSessionHasErrors('kontrak_id');
        $this->assertDatabaseCount('tagihans', 0);
    }

    public function test_tagihan_snapshot_preserved_after_price_change(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(3);
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'due_date' => '2026-10-05',
        ]);
        $kamar->update(['monthly_price' => 1500000]);
        $tags = Tagihan::all();
        foreach ($tags as $t) {
            $this->assertEquals(1200000.0, (float) $t->total_room_bill);
            $this->assertEquals(400000.0, (float) $t->subtotal);
        }
    }

    public function test_tagihan_duplicate_prevented(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(2);
        $payload = [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'due_date' => '2026-10-05',
        ];
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), $payload)->assertRedirect();
        $this->assertDatabaseCount('tagihans', 2);
        $response = $this->actingAs($this->owner)->post(route('owner.tagihan.store'), $payload);
        $response->assertSessionHasErrors('kontrak_id');
        $this->assertDatabaseCount('tagihans', 2);
    }

    public function test_historical_unchanged_after_departure(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(3);
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'due_date' => '2026-10-05',
        ]);
        $cycle1 = Tagihan::whereDate('period_start', '2026-09-01')->get();
        $cycle1Subtotals = $cycle1->pluck('subtotal')->map(fn ($v) => (float) $v)->sort()->values()->toArray();
        $this->assertEquals([400000.0, 400000.0, 400000.0], $cycle1Subtotals);
        // B keluar
        $kontraks[2]->update(['status' => 'terminated']);
        // Check old bills unchanged
        foreach ($cycle1 as $t) {
            $t->refresh();
            $this->assertEquals(400000.0, (float) $t->subtotal);
        }
        // Next cycle with 2 occupants
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'due_date' => '2026-11-05',
        ])->assertRedirect();
        $cycle2 = Tagihan::whereDate('period_start', '2026-10-01')->orderBy('kontrak_id')->get();
        $this->assertCount(2, $cycle2);
        foreach ($cycle2 as $t) {
            $this->assertEquals(600000.0, (float) $t->subtotal);
        }
    }

    public function test_new_occupant_no_retroactive(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(2);
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'due_date' => '2026-10-05',
        ]);
        $this->assertDatabaseCount('tagihans', 2);
        // Add new occupant after cycle 1
        $newUser = User::factory()->create(['role' => 'tenant']);
        $newPenghuni = Penghuni::factory()->create(['user_id' => $newUser->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $newKontrak = Kontrak::factory()->create([
            'penghuni_id' => $newPenghuni->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id,
            'rental_type' => 'monthly', 'rental_price' => 1200000, 'billing_mode' => 'shared', 'status' => 'active',
        ]);
        // Ensure no retroactive bill for new occupant in cycle 1
        $this->assertEquals(0, Tagihan::where('kontrak_id', $newKontrak->id)->whereDate('period_start', '2026-09-01')->count());
        // Next cycle includes new occupant
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $newKontrak->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'due_date' => '2026-11-05',
        ])->assertRedirect();
        $cycle2 = Tagihan::whereDate('period_start', '2026-10-01')->get();
        $this->assertCount(3, $cycle2);
        foreach ($cycle2 as $t) {
            $this->assertEquals(400000.0, (float) $t->subtotal);
        }
    }

    // ---------------------------------------------------------------- Rental type

    public function test_daily_shared_billing(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(3, 50000, 'daily');
        $kamar->update(['daily_price' => 50000, 'monthly_price' => 1200000]);
        Kontrak::where('kamar_id', $kamar->id)->update(['rental_type' => 'daily']);
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Harian',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-03',
            'due_date' => '2026-09-04',
        ])->assertRedirect();
        // total = 3 days * 50000 = 150000 /3 = 50000
        $tags = Tagihan::all();
        $this->assertCount(3, $tags);
        foreach ($tags as $t) {
            $this->assertEquals(150000.0, (float) $t->total_room_bill);
            $this->assertEquals(50000.0, (float) $t->subtotal);
        }
    }

    public function test_weekly_shared_billing(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(2, 350000, 'weekly');
        $kamar->update(['weekly_price' => 350000]);
        Kontrak::where('kamar_id', $kamar->id)->update(['rental_type' => 'weekly']);
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Mingguan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-10', // 10 days -> 2 weeks
            'due_date' => '2026-09-12',
        ])->assertRedirect();
        foreach (Tagihan::all() as $t) {
            $this->assertEquals(700000.0, (float) $t->total_room_bill);
            $this->assertEquals(350000.0, (float) $t->subtotal);
        }
    }

    public function test_monthly_shared_billing(): void
    {
        [$kamar, $kontraks] = $this->sharedKamarWithOccupants(3, 1200000, 'monthly');
        $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontraks[0]->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'due_date' => '2026-10-05',
        ])->assertRedirect();
        foreach (Tagihan::all() as $t) {
            $this->assertEquals(1200000.0, (float) $t->total_room_bill);
        }
    }

    public function test_weekly_units_compatibility(): void
    {
        $cases = [
            [1, 1], [6, 1], [7, 1], [8, 2], [13, 2], [14, 2], [15, 3], [30, 5],
        ];
        foreach ($cases as [$days, $expected]) {
            $start = Carbon::parse('2026-09-01');
            $end = $start->copy()->addDays($days - 1);
            $units = $this->shared->calculateUnits('weekly', $start, $end);
            $this->assertEquals($expected, $units, "Weekly {$days} days should be {$expected} weeks, got {$units}");
        }
    }

    // ---------------------------------------------------------------- Security

    public function test_tenant_cannot_force_shared_on_personal_via_http(): void
    {
        $kamar = $this->kamar(['billing_mode' => 'personal']);
        $response = $this->actingAs($this->tenantA)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
        ]);
        // Should be rejected with billing_mode error, not success
        $response->assertSessionHasErrors('billing_mode');
        $this->assertDatabaseMissing('bookings', ['kamar_id' => $kamar->id, 'billing_mode' => 'shared']);
    }

    public function test_owner_cannot_manipulate_other_tenant_billing_mode(): void
    {
        // Tenant B tries to book shared on personal room via API bypass attempt
        $kamar = $this->kamar(['billing_mode' => 'personal']);
        $token = $this->tenantB->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
        ]);
        $response->assertStatus(422);
        $this->assertDatabaseMissing('bookings', ['user_id' => $this->tenantB->id, 'billing_mode' => 'shared']);
    }
}
