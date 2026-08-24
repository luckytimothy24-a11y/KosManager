<?php

namespace Tests\Feature;

use App\Mail\KosManagerMail;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class KontrakAutoCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveStay(): array
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
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
            'end_date' => today()->subDay(),
        ]);

        return [$owner, $tenant, $kamar, $penghuni, $kontrak];
    }

    public function test_expired_kontrak_auto_checks_out_penghuni_without_debt(): void
    {
        Mail::fake();

        [$owner, $tenant, $kamar, $penghuni, $kontrak] = $this->createActiveStay();

        $this->artisan('kontrak:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('kontraks', ['id' => $kontrak->id, 'status' => 'expired']);
        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'available']);

        $this->assertDatabaseHas('check_outs', [
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
        ]);

        Mail::assertQueued(KosManagerMail::class, function ($mail) use ($tenant) {
            return $mail->mailSubject === 'Check-Out Disetujui'
                && $mail->to[0]['address'] === $tenant->email;
        });
    }

    public function test_expired_kontrak_with_unpaid_bill_keeps_penghuni_until_bills_settled(): void
    {
        Mail::fake();

        [$owner, $tenant, $kamar, $penghuni, $kontrak] = $this->createActiveStay();

        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
        ]);

        $this->artisan('kontrak:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('kontraks', ['id' => $kontrak->id, 'status' => 'expired']);
        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id, 'status' => 'active']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'occupied']);
        $this->assertDatabaseCount('check_outs', 0);

        Tagihan::query()->update(['status' => 'paid']);

        $this->artisan('kontrak:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'available']);
        $this->assertDatabaseCount('check_outs', 1);
    }

    public function test_auto_checkout_is_not_duplicated_on_subsequent_runs(): void
    {
        Mail::fake();

        [$owner, $tenant, $kamar, $penghuni, $kontrak] = $this->createActiveStay();

        $this->artisan('kontrak:expire-old')->assertSuccessful();
        $this->artisan('kontrak:expire-old')->assertSuccessful();

        $this->assertDatabaseCount('check_outs', 1);
    }

    public function test_manual_checkout_flow_still_works_after_auto_checkout_feature(): void
    {
        Mail::fake();

        [$owner, $tenant, $kamar, $penghuni, $kontrak] = $this->createActiveStay();

        $checkOut = CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($owner)->post("/owner/check-out/{$checkOut->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kontraks', ['id' => $kontrak->id, 'status' => 'expired']);
        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'available']);
    }
}
