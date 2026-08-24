<?php

namespace Tests\Feature;

use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckOutBillCheckTest extends TestCase
{
    use RefreshDatabase;

    private function createPendingCheckout(): array
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $checkOut = CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'request_date' => now(),
            'status' => 'pending',
        ]);

        return [$owner, $penghuni, $checkOut, $kamar];
    }

    public function test_owner_cannot_approve_checkout_with_unpaid_bills(): void
    {
        [$owner, $penghuni, $checkOut, $kamar] = $this->createPendingCheckout();

        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($owner)->post("/owner/check-out/{$checkOut->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('check_outs', ['id' => $checkOut->id, 'status' => 'pending']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'occupied']);
    }

    public function test_overdue_bill_also_blocks_checkout(): void
    {
        [$owner, $penghuni, $checkOut] = $this->createPendingCheckout();

        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'status' => 'overdue',
        ]);

        $response = $this->actingAs($owner)->post("/owner/check-out/{$checkOut->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('check_outs', ['id' => $checkOut->id, 'status' => 'pending']);
    }

    public function test_checkout_blocked_while_payment_pending_verification(): void
    {
        [$owner, $penghuni, $checkOut] = $this->createPendingCheckout();

        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'status' => 'pending_verification',
        ]);

        $response = $this->actingAs($owner)->post("/owner/check-out/{$checkOut->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('check_outs', ['id' => $checkOut->id, 'status' => 'pending']);
    }

    public function test_checkout_request_persists_condition_and_notes(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($tenant)->post("/tenant/check-out/{$penghuni->id}/request", [
            'condition' => 'Kamar bersih, AC normal',
            'notes' => 'Pindah tugas ke luar kota',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('check_outs', [
            'penghuni_id' => $penghuni->id,
            'room_condition' => 'Kamar bersih, AC normal',
            'notes' => 'Pindah tugas ke luar kota',
            'status' => 'pending',
        ]);
    }

    public function test_owner_can_approve_checkout_when_all_bills_paid(): void
    {
        [$owner, $penghuni, $checkOut, $kamar] = $this->createPendingCheckout();

        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($owner)->post("/owner/check-out/{$checkOut->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('check_outs', ['id' => $checkOut->id, 'status' => 'approved']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'available']);
    }

    public function test_checkout_before_contract_end_terminates_kontrak(): void
    {
        [$owner, $penghuni, $checkOut, $kamar] = $this->createPendingCheckout();

        Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $penghuni->kos_id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
            'end_date' => now()->addMonths(3),
        ]);

        $this->actingAs($owner)->post("/owner/check-out/{$checkOut->id}/approve")->assertSessionHas('success');

        $this->assertDatabaseHas('kontraks', [
            'penghuni_id' => $penghuni->id,
            'status' => 'terminated',
        ]);
    }

    public function test_checkout_at_or_after_contract_end_expires_kontrak(): void
    {
        [$owner, $penghuni, $checkOut, $kamar] = $this->createPendingCheckout();

        Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $penghuni->kos_id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
            'end_date' => now()->subDay(),
        ]);

        $this->actingAs($owner)->post("/owner/check-out/{$checkOut->id}/approve")->assertSessionHas('success');

        $this->assertDatabaseHas('kontraks', [
            'penghuni_id' => $penghuni->id,
            'status' => 'expired',
        ]);
    }
}
