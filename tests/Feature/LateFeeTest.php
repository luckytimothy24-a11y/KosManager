<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LateFeeTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $otherOwner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    private Penghuni $penghuni;

    private Kontrak $kontrak;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->otherOwner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'billing_mode' => 'personal',
            'monthly_price' => 1000000,
            'daily_price' => 50000,
            'weekly_price' => 300000,
        ]);
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
            'rental_price' => 1000000,
            'billing_mode' => 'personal',
            'status' => 'active',
        ]);
    }

    private function makeTagihan(array $overrides = []): Tagihan
    {
        return Tagihan::factory()->create(array_merge([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kamar->id,
            'bill_type' => 'rent',
            'period_start' => today()->subMonth()->toDateString(),
            'period_end' => today()->toDateString(),
            'subtotal' => 1000000,
            'discount' => 0,
            'penalty' => 0,
            'total' => 1000000,
            'due_date' => today()->toDateString(),
            'status' => 'unpaid',
        ], $overrides));
    }

    public function test_due_today_remains_unpaid_without_penalty(): void
    {
        $tagihan = $this->makeTagihan(['due_date' => today()->toDateString(), 'status' => 'unpaid', 'penalty' => 0]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'unpaid', 'penalty' => 0]);
    }

    public function test_overdue_yesterday_becomes_overdue_with_fixed_penalty(): void
    {
        $tagihan = $this->makeTagihan(['due_date' => today()->subDay()->toDateString(), 'status' => 'unpaid', 'subtotal' => 1000000, 'discount' => 0, 'penalty' => 0, 'total' => 1000000]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $tagihan->refresh();
        $this->assertEquals('overdue', $tagihan->status);
        $this->assertEquals(50000.0, (float) $tagihan->penalty);
        $this->assertEquals(1050000.0, (float) $tagihan->total);
    }

    public function test_overdue_one_time_idempotent(): void
    {
        $tagihan = $this->makeTagihan(['due_date' => today()->subDay()->toDateString(), 'status' => 'unpaid', 'penalty' => 0, 'total' => 1000000]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();
        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $tagihan->refresh();
        $this->assertEquals(50000.0, (float) $tagihan->penalty);
        $this->assertEquals(1050000.0, (float) $tagihan->total);
        $this->assertEquals('overdue', $tagihan->status);
    }

    public function test_paid_not_touched(): void
    {
        $tagihan = $this->makeTagihan(['due_date' => today()->subDay()->toDateString(), 'status' => 'paid', 'penalty' => 0, 'total' => 1000000]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $tagihan->refresh();
        $this->assertEquals('paid', $tagihan->status);
        $this->assertEquals(0.0, (float) $tagihan->penalty);
    }

    public function test_pending_verification_not_touched(): void
    {
        $tagihan = $this->makeTagihan(['due_date' => today()->subDay()->toDateString(), 'status' => 'pending_verification', 'penalty' => 0]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $tagihan->refresh();
        $this->assertEquals('pending_verification', $tagihan->status);
        $this->assertEquals(0.0, (float) $tagihan->penalty);
    }

    public function test_manual_penalty_not_overwritten(): void
    {
        $tagihan = $this->makeTagihan(['due_date' => today()->subDay()->toDateString(), 'status' => 'unpaid', 'subtotal' => 1000000, 'discount' => 0, 'penalty' => 30000, 'total' => 1030000]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $tagihan->refresh();
        $this->assertEquals(30000.0, (float) $tagihan->penalty);
        $this->assertEquals(1030000.0, (float) $tagihan->total);
        $this->assertEquals('overdue', $tagihan->status);
    }

    public function test_shared_billing_penalty_per_tagihan(): void
    {
        // Shared kamar with 3 penghuni
        $sharedKamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'billing_mode' => 'shared',
            'capacity' => 5,
            'monthly_price' => 1200000,
        ]);

        $tagihans = collect();
        $kontraks = collect();

        for ($i = 0; $i < 3; $i++) {
            $user = User::factory()->create(['role' => 'tenant']);
            $penghuni = Penghuni::factory()->create([
                'user_id' => $user->id,
                'kos_id' => $this->kos->id,
                'kamar_id' => $sharedKamar->id,
                'status' => 'active',
            ]);
            $kontrak = Kontrak::factory()->create([
                'penghuni_id' => $penghuni->id,
                'kos_id' => $this->kos->id,
                'kamar_id' => $sharedKamar->id,
                'rental_type' => 'monthly',
                'rental_price' => 1200000,
                'billing_mode' => 'shared',
                'status' => 'active',
            ]);
            $kontraks->push($kontrak);

            $tagihan = Tagihan::factory()->create([
                'penghuni_id' => $penghuni->id,
                'kontrak_id' => $kontrak->id,
                'kamar_id' => $sharedKamar->id,
                'period_start' => today()->subMonth()->toDateString(),
                'period_end' => today()->toDateString(),
                'subtotal' => 400000,
                'discount' => 0,
                'penalty' => 0,
                'total' => 400000,
                'total_room_bill' => 1200000,
                'due_date' => today()->subDay()->toDateString(),
                'status' => 'unpaid',
            ]);
            $tagihans->push($tagihan);
        }

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        foreach ($tagihans as $t) {
            $t->refresh();
            $this->assertEquals('overdue', $t->status);
            $this->assertEquals(50000.0, (float) $t->penalty);
            $this->assertEquals(450000.0, (float) $t->total);
            $this->assertEquals(1200000.0, (float) $t->total_room_bill);
            $this->assertEquals(400000.0, (float) $t->subtotal);
        }

        // Invariant shared: SUM(subtotal) == total_room_bill still holds
        $sumSubtotal = Tagihan::whereIn('id', $tagihans->pluck('id'))->sum('subtotal');
        $this->assertEquals(1200000.0, (float) $sumSubtotal);
    }

    public function test_discount_with_penalty_calculation(): void
    {
        $tagihan = $this->makeTagihan(['subtotal' => 1000000, 'discount' => 100000, 'penalty' => 0, 'total' => 900000, 'due_date' => today()->subDay()->toDateString(), 'status' => 'unpaid']);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $tagihan->refresh();
        $this->assertEquals(50000.0, (float) $tagihan->penalty);
        $this->assertEquals(950000.0, (float) $tagihan->total);
    }

    public function test_negative_penalty_rejected_via_validation(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $this->kontrak->id,
            'bill_type' => 'rent',
            'period_start' => today()->startOfMonth()->toDateString(),
            'period_end' => today()->endOfMonth()->toDateString(),
            'due_date' => today()->addDays(5)->toDateString(),
            'discount' => 0,
            'penalty' => -1,
        ]);

        $response->assertSessionHasErrors('penalty');
    }

    public function test_penalty_overflow_rejected_when_exceeds_subtotal(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $this->kontrak->id,
            'bill_type' => 'rent',
            'period_start' => today()->startOfMonth()->toDateString(),
            'period_end' => today()->endOfMonth()->toDateString(),
            'due_date' => today()->addDays(5)->toDateString(),
            'discount' => 0,
            'penalty' => 1500000,
        ]);

        $response->assertSessionHasErrors('penalty');
        $this->assertDatabaseCount('tagihans', 0);
    }

    public function test_authorization_owner_cannot_penalty_other_owner_tagihan(): void
    {
        $otherKos = Kos::factory()->create(['owner_id' => $this->otherOwner->id]);
        $otherKamar = Kamar::factory()->create(['kos_id' => $otherKos->id]);
        $otherTenant = User::factory()->create(['role' => 'tenant']);
        $otherPenghuni = Penghuni::factory()->create([
            'user_id' => $otherTenant->id,
            'kos_id' => $otherKos->id,
            'kamar_id' => $otherKamar->id,
            'status' => 'active',
        ]);
        $otherKontrak = Kontrak::factory()->create([
            'penghuni_id' => $otherPenghuni->id,
            'kos_id' => $otherKos->id,
            'kamar_id' => $otherKamar->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $otherKontrak->id,
            'bill_type' => 'rent',
            'period_start' => today()->startOfMonth()->toDateString(),
            'period_end' => today()->endOfMonth()->toDateString(),
            'due_date' => today()->addDays(5)->toDateString(),
            'penalty' => 50000,
        ]);

        $response->assertForbidden();
    }

    public function test_tenant_cannot_modify_penalty(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $this->kontrak->id,
            'bill_type' => 'rent',
            'period_start' => today()->startOfMonth()->toDateString(),
            'period_end' => today()->endOfMonth()->toDateString(),
            'due_date' => today()->addDays(5)->toDateString(),
            'penalty' => 50000,
        ]);

        $response->assertForbidden();
    }

    public function test_concurrent_overdue_idempotent_via_lock(): void
    {
        $tagihan = $this->makeTagihan(['due_date' => today()->subDay()->toDateString(), 'status' => 'unpaid', 'penalty' => 0, 'total' => 1000000]);

        // Simulate two concurrent sweeps by running command twice rapidly
        $this->artisan('tagihan:mark-overdue')->assertSuccessful();
        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $tagihan->refresh();
        $this->assertEquals(50000.0, (float) $tagihan->penalty);
        $this->assertEquals(1050000.0, (float) $tagihan->total);
    }

    public function test_overdue_does_not_touch_cancelled(): void
    {
        $tagihan = $this->makeTagihan(['due_date' => today()->subDay()->toDateString(), 'status' => 'cancelled', 'penalty' => 0]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $tagihan->refresh();
        $this->assertEquals('cancelled', $tagihan->status);
        $this->assertEquals(0.0, (float) $tagihan->penalty);
    }
}
