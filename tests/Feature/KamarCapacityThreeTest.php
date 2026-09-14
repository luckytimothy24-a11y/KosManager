<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\SharedBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KamarCapacityThreeTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Kos $kos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
    }

    private function sharedKamar(int $capacity = 3, int $price = 1200000): Kamar
    {
        return Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'capacity' => $capacity,
            'billing_mode' => 'shared',
            'monthly_price' => $price,
            'daily_price' => 50000,
            'weekly_price' => 350000,
        ]);
    }

    private function addPenghuni(Kamar $kamar, User $user, ?int $customShare = null): void
    {
        $payload = [
            'user_id' => $user->id,
            'rental_type' => 'monthly',
            'rental_price' => 1200000,
            'billing_mode' => 'shared',
            'check_in_date' => now()->toDateString(),
        ];
        if ($customShare !== null) {
            $payload['custom_share_amount'] = $customShare;
        }
        $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), $payload)->assertRedirect();
    }

    // ── N.2 — Capacity 3 states ─────────────────────────────────

    public function test_empty_three_slots(): void
    {
        $kamar = $this->sharedKamar(3);
        $this->assertSame(3, $kamar->remainingSlots());
        $this->assertTrue($kamar->hasAvailableSlot());
        $this->assertTrue($kamar->isAvailableForBooking());
        $this->assertSame(0, $kamar->occupancy());
    }

    public function test_one_of_three(): void
    {
        $kamar = $this->sharedKamar(3);
        $u1 = User::factory()->create(['role' => 'tenant']);
        $this->addPenghuni($kamar, $u1);
        $kamar->refresh();
        $this->assertSame(1, $kamar->occupancy());
        $this->assertSame(2, $kamar->remainingSlots());
        $this->assertTrue($kamar->hasAvailableSlot());
        $this->assertTrue($kamar->isAvailableForBooking());
    }

    public function test_two_of_three(): void
    {
        $kamar = $this->sharedKamar(3);
        $u1 = User::factory()->create(['role' => 'tenant']);
        $u2 = User::factory()->create(['role' => 'tenant']);
        $this->addPenghuni($kamar, $u1);
        $this->addPenghuni($kamar, $u2);
        $kamar->refresh();
        $this->assertSame(2, $kamar->occupancy());
        $this->assertSame(1, $kamar->remainingSlots());
        $this->assertTrue($kamar->hasAvailableSlot());
        $this->assertTrue($kamar->isAvailableForBooking());
    }

    public function test_full_three_of_three(): void
    {
        $kamar = $this->sharedKamar(3);
        foreach (range(1, 3) as $i) {
            $u = User::factory()->create(['role' => 'tenant']);
            $this->addPenghuni($kamar, $u);
        }
        $kamar->refresh();
        $this->assertSame(3, $kamar->occupancy());
        $this->assertSame(0, $kamar->remainingSlots());
        $this->assertFalse($kamar->hasAvailableSlot());
        $this->assertFalse($kamar->isAvailableForBooking());
        $this->assertTrue($kamar->isFull());
    }

    public function test_fourth_tenant_rejected_for_capacity_three(): void
    {
        $kamar = $this->sharedKamar(3);
        foreach (range(1, 3) as $i) {
            $u = User::factory()->create(['role' => 'tenant']);
            $this->addPenghuni($kamar, $u);
        }
        $fourth = User::factory()->create(['role' => 'tenant']);
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), [
            'user_id' => $fourth->id,
            'rental_type' => 'monthly',
            'rental_price' => 1200000,
            'billing_mode' => 'shared',
            'check_in_date' => now()->toDateString(),
        ]);
        $response->assertStatus(400);
        $kamar->refresh();
        $this->assertSame(3, $kamar->occupancy());
        $this->assertDatabaseCount('penghunis', 3);
    }

    public function test_checkout_one_from_three_keeps_occupied_and_bookable(): void
    {
        $kamar = $this->sharedKamar(3);
        $users = [];
        foreach (range(1, 3) as $i) {
            $u = User::factory()->create(['role' => 'tenant']);
            $users[] = $u;
            $this->addPenghuni($kamar, $u);
        }
        $kamar->refresh();
        $this->assertSame(3, $kamar->occupancy());
        // Checkout one via CheckOut flow (request + approve)
        $penghuni = Penghuni::where('user_id', $users[0]->id)->first();
        $this->actingAs($users[0])->post(route('tenant.checkout.request', $penghuni))->assertRedirect();
        $checkOut = \App\Models\CheckOut::where('penghuni_id', $penghuni->id)->first();
        $this->actingAs($this->owner)->post(route('owner.checkout.approve', $checkOut))->assertRedirect();
        $kamar->refresh();
        $this->assertSame(2, $kamar->occupancy());
        $this->assertSame(1, $kamar->remainingSlots());
        $this->assertSame('occupied', $kamar->status);
        $this->assertTrue($kamar->isAvailableForBooking());
    }

    public function test_replacement_tenant_after_checkout(): void
    {
        $kamar = $this->sharedKamar(3);
        $users = [];
        foreach (range(1, 3) as $i) {
            $u = User::factory()->create(['role' => 'tenant']);
            $users[] = $u;
            $this->addPenghuni($kamar, $u);
        }
        // Checkout first
        $penghuni = Penghuni::where('user_id', $users[0]->id)->first();
        $this->actingAs($users[0])->post(route('tenant.checkout.request', $penghuni));
        $checkOut = \App\Models\CheckOut::where('penghuni_id', $penghuni->id)->first();
        $this->actingAs($this->owner)->post(route('owner.checkout.approve', $checkOut));
        // Add replacement
        $replacement = User::factory()->create(['role' => 'tenant']);
        $this->addPenghuni($kamar, $replacement);
        $kamar->refresh();
        $this->assertSame(3, $kamar->occupancy());
        $this->assertSame(0, $kamar->remainingSlots());
        $this->assertFalse($kamar->isAvailableForBooking());
        $this->assertDatabaseCount('penghunis', 4); // 3 active + 1 inactive
        $this->assertSame(3, Penghuni::where('kamar_id', $kamar->id)->where('status', 'active')->count());
    }

    // ── N.3/N.4 — Shared billing 3 tenants ──────────────────────

    public function test_shared_billing_three_equal_split(): void
    {
        $kamar = $this->sharedKamar(3, 1200000);
        $kontraks = [];
        foreach (range(1, 3) as $i) {
            $u = User::factory()->create(['role' => 'tenant']);
            $p = Penghuni::factory()->create(['user_id' => $u->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
            $kontraks[] = Kontrak::factory()->create([
                'penghuni_id' => $p->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id,
                'rental_type' => 'monthly', 'rental_price' => 1200000, 'billing_mode' => 'shared', 'status' => 'active',
            ]);
        }
        $kamar->update(['status' => 'occupied']);
        $svc = new SharedBillingService;
        $tagihans = $svc->createSharedTagihans($kamar, 'Sewa Bulanan', '2026-09-01', '2026-09-30', '2026-10-05');
        $this->assertCount(3, $tagihans);
        foreach ($tagihans as $t) {
            $this->assertEquals(400000.0, (float) $t->subtotal);
            $this->assertEquals(1200000.0, (float) $t->total_room_bill);
        }
        $this->assertEqualsWithDelta(1200000.0, (float) Tagihan::sum('subtotal'), 0.01);
    }

    public function test_shared_billing_remainder_distribution(): void
    {
        $kamar = $this->sharedKamar(3, 1000000);
        foreach (range(1, 3) as $i) {
            $u = User::factory()->create(['role' => 'tenant']);
            $p = Penghuni::factory()->create(['user_id' => $u->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
            Kontrak::factory()->create([
                'penghuni_id' => $p->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id,
                'rental_type' => 'monthly', 'rental_price' => 1000000, 'billing_mode' => 'shared', 'status' => 'active',
            ]);
        }
        $kamar->update(['status' => 'occupied']);
        $svc = new SharedBillingService;
        $tagihans = $svc->createSharedTagihans($kamar, 'Sewa Bulanan', '2026-09-01', '2026-09-30', '2026-10-05');
        $this->assertCount(3, $tagihans);
        $subtotals = collect($tagihans)->pluck('subtotal')->map(fn ($v) => (float) $v)->sort()->values()->toArray();
        // 1_000_000 /3 = remainder 1 cent -> 333333.34 + 333333.33 + 333333.33 (integer-cent)
        $this->assertEqualsWithDelta(1000000.0, array_sum($subtotals), 0.01);
        $this->assertContains(333333.34, $subtotals);
        $this->assertContains(333333.33, $subtotals);
    }

    public function test_custom_share_three_equal_pass(): void
    {
        $kamar = $this->sharedKamar(3, 1200000);
        $kontraks = [];
        foreach ([400000, 400000, 400000] as $share) {
            $u = User::factory()->create(['role' => 'tenant']);
            $p = Penghuni::factory()->create(['user_id' => $u->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
            $kontraks[] = Kontrak::factory()->create([
                'penghuni_id' => $p->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id,
                'rental_type' => 'monthly', 'billing_mode' => 'shared', 'custom_share_amount' => $share, 'status' => 'active',
            ]);
        }
        $kamar->update(['status' => 'occupied']);
        $svc = new SharedBillingService;
        $tagihans = $svc->createSharedTagihans($kamar, 'Sewa Bulanan', '2026-09-01', '2026-09-30', '2026-10-05');
        $this->assertCount(3, $tagihans);
        $this->assertEqualsWithDelta(1200000.0, (float) Tagihan::sum('subtotal'), 0.01);
    }

    public function test_custom_share_three_under_rejected_when_full(): void
    {
        $kamar = $this->sharedKamar(3, 1200000);
        // Pre-create 2 with custom 400k each
        foreach ([400000, 400000] as $share) {
            $u = User::factory()->create(['role' => 'tenant']);
            $p = Penghuni::factory()->create(['user_id' => $u->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
            Kontrak::factory()->create([
                'penghuni_id' => $p->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id,
                'rental_type' => 'monthly', 'billing_mode' => 'shared', 'custom_share_amount' => $share, 'status' => 'active',
            ]);
        }
        $kamar->update(['status' => 'occupied']);
        // Try to add third with 300k => total 1.1M <1.2M, should be rejected when room becomes full with all custom
        $third = User::factory()->create(['role' => 'tenant']);
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), [
            'user_id' => $third->id,
            'rental_type' => 'monthly',
            'rental_price' => 1200000,
            'billing_mode' => 'shared',
            'custom_share_amount' => 300000,
            'check_in_date' => now()->toDateString(),
        ]);
        $response->assertStatus(422);
        $kamar->refresh();
        $this->assertSame(2, $kamar->occupancy());
    }

    public function test_custom_share_three_valid_mixed(): void
    {
        $kamar = $this->sharedKamar(3, 1200000);
        foreach ([500000, 400000, 300000] as $share) {
            $u = User::factory()->create(['role' => 'tenant']);
            $p = Penghuni::factory()->create(['user_id' => $u->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
            Kontrak::factory()->create([
                'penghuni_id' => $p->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id,
                'rental_type' => 'monthly', 'billing_mode' => 'shared', 'custom_share_amount' => $share, 'status' => 'active',
            ]);
        }
        $kamar->update(['status' => 'occupied']);
        $svc = new SharedBillingService;
        $tagihans = $svc->createSharedTagihans($kamar, 'Sewa Bulanan', '2026-09-01', '2026-09-30', '2026-10-05');
        $this->assertCount(3, $tagihans);
        $subs = collect($tagihans)->pluck('subtotal')->map(fn ($v) => (float) $v)->sort()->values()->toArray();
        $this->assertEquals([300000.0, 400000.0, 500000.0], $subs);
    }

    // ── N.6 — Booking capacity 3 via BookingService ─────────────

    public function test_booking_shared_capacity_three_allows_until_full(): void
    {
        $kamar = $this->sharedKamar(3, 1200000);
        // Pre-occupy 2
        foreach (range(1, 2) as $i) {
            $u = User::factory()->create(['role' => 'tenant']);
            $p = Penghuni::factory()->create(['user_id' => $u->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
            Kontrak::factory()->create([
                'penghuni_id' => $p->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id,
                'rental_type' => 'monthly', 'billing_mode' => 'shared', 'status' => 'active',
            ]);
        }
        $kamar->update(['status' => 'occupied']);
        $kamar->refresh();
        $this->assertSame(2, $kamar->occupancy());
        // Third booking should succeed
        $tenant = User::factory()->create(['role' => 'tenant']);
        $svc = app(\App\Services\BookingService::class);
        $result = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
        ], $tenant->id);
        $this->assertTrue($result['ok']);
    }

    public function test_booking_shared_capacity_three_rejects_when_full(): void
    {
        $kamar = $this->sharedKamar(3, 1200000);
        foreach (range(1, 3) as $i) {
            $u = User::factory()->create(['role' => 'tenant']);
            $p = Penghuni::factory()->create(['user_id' => $u->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
            Kontrak::factory()->create([
                'penghuni_id' => $p->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id,
                'rental_type' => 'monthly', 'billing_mode' => 'shared', 'status' => 'active',
            ]);
        }
        $kamar->update(['status' => 'occupied']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $svc = app(\App\Services\BookingService::class);
        $result = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
        ], $tenant->id);
        $this->assertFalse($result['ok']);
        $this->assertSame('kamar_penuh', $result['error']);
    }
}
