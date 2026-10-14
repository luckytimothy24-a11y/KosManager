<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PropertyOwnerPayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdjustmentHttpTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $superAdmin;
    private Kos $kos;
    private Pembayaran $pembayaran;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $po = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $po->id]);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $po->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $penghuni = Penghuni::factory()->create(['user_id' => $tenant->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $kontrak = Kontrak::factory()->create(['penghuni_id' => $penghuni->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $tagihan = Tagihan::factory()->create(['penghuni_id' => $penghuni->id, 'kontrak_id' => $kontrak->id, 'kamar_id' => $kamar->id, 'subtotal' => 2000000, 'penalty' => 0, 'total' => 2000000]);
        ManagementAgreement::factory()->create(['kos_id' => $this->kos->id, 'property_owner_id' => $po->id, 'commission_type' => 'percent', 'commission_value' => 10, 'effective_from' => '2026-01-01']);
        $this->pembayaran = Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 2000000, 'rent_amount' => 2000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-10', 'verification_status' => 'approved']);
        $svc = app(PropertyOwnerPayoutService::class);
        $svc->createForKos($this->owner, ['kos_id' => $this->kos->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
    }

    public function test_super_admin_can_create_adjustment_via_http(): void
    {
        $this->actingAs($this->superAdmin)->post(route('owner.adjustments.store'), [
            'pembayaran_id' => $this->pembayaran->id,
            'type' => 'refund',
            'amount' => 500000,
            'reason' => 'refund via http test',
        ])->assertRedirect();
        $this->assertDatabaseHas('payout_adjustments', ['pembayaran_id' => $this->pembayaran->id, 'amount' => 500000]);
    }

    public function test_refund_exceeds_gross_via_http(): void
    {
        $this->actingAs($this->superAdmin)->post(route('owner.adjustments.store'), [
            'pembayaran_id' => $this->pembayaran->id,
            'type' => 'refund',
            'amount' => 2500000,
            'reason' => 'refund melebihi gross',
        ])->assertSessionHas('error');
    }

    public function test_owner_linked_payout_forbidden_via_http(): void
    {
        $payout = \App\Models\Payout::firstOrFail();
        $this->actingAs($this->owner)->post(route('owner.adjustments.store'), [
            'pembayaran_id' => $this->pembayaran->id,
            'payout_id' => $payout->id,
            'type' => 'refund',
            'amount' => 100000,
            'reason' => 'owner coba via http',
        ])->assertSessionHas('error');
    }

    public function test_reason_too_short_validation(): void
    {
        $this->actingAs($this->superAdmin)->post(route('owner.adjustments.store'), [
            'pembayaran_id' => $this->pembayaran->id,
            'type' => 'refund',
            'amount' => 100000,
            'reason' => 'ab',
        ])->assertSessionHasErrors('reason');
    }
}
