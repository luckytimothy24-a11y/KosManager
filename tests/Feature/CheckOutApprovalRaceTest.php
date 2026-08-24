<?php

namespace Tests\Feature;

use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckOutApprovalRaceTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kamar $kamar;

    private Penghuni $penghuni;

    private CheckOut $checkOut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);
        $this->penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $this->checkOut = CheckOut::create([
            'penghuni_id' => $this->penghuni->id,
            'kamar_id' => $this->kamar->id,
            'request_date' => now(),
            'status' => 'pending',
        ]);
    }

    public function test_double_approve_does_not_corrupt_state(): void
    {
        Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'paid',
        ]);

        $first = $this->actingAs($this->owner)->post("/owner/check-out/{$this->checkOut->id}/approve");
        $first->assertRedirect();
        $first->assertSessionHas('success');

        $second = $this->actingAs($this->owner)->post("/owner/check-out/{$this->checkOut->id}/approve");

        $second->assertStatus(400);

        $this->assertDatabaseHas('check_outs', ['id' => $this->checkOut->id, 'status' => 'approved']);
        $this->assertDatabaseHas('penghunis', ['id' => $this->penghuni->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'available']);
    }

    public function test_reject_after_approval_is_blocked(): void
    {
        Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'paid',
        ]);

        $this->actingAs($this->owner)->post("/owner/check-out/{$this->checkOut->id}/approve")->assertSessionHas('success');

        $reject = $this->actingAs($this->owner)->post("/owner/check-out/{$this->checkOut->id}/reject");

        $reject->assertStatus(400);

        $this->assertDatabaseHas('check_outs', ['id' => $this->checkOut->id, 'status' => 'approved']);
        $this->assertDatabaseHas('penghunis', ['id' => $this->penghuni->id, 'status' => 'inactive']);
    }

    public function test_unpaid_bill_check_runs_inside_transaction_and_blocks_cleanly(): void
    {
        Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kamar_id' => $this->kamar->id,
            'due_date' => today()->subDay(),
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->owner)->post("/owner/check-out/{$this->checkOut->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('check_outs', [
            'id' => $this->checkOut->id,
            'status' => 'pending',
            'verified_by' => null,
            'check_out_date' => null,
        ]);
        $this->assertDatabaseHas('penghunis', ['id' => $this->penghuni->id, 'status' => 'active']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'occupied']);

        Tagihan::query()->update(['status' => 'paid']);

        $retry = $this->actingAs($this->owner)->post("/owner/check-out/{$this->checkOut->id}/approve");
        $retry->assertRedirect();
        $retry->assertSessionHas('success');

        $this->assertDatabaseHas('check_outs', ['id' => $this->checkOut->id, 'status' => 'approved']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'available']);
    }
}
