<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\Payout;
use App\Models\PayoutItem;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $superAdmin;

    private Kos $kos;

    private PropertyOwner $po;

    private Tagihan $tagihan;

    private Penghuni $penghuni;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $this->po = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $this->po->id]);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $this->po->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $this->penghuni = Penghuni::factory()->create(['user_id' => $tenant->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $kontrak = Kontrak::factory()->create(['penghuni_id' => $this->penghuni->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $this->tagihan = Tagihan::factory()->create(['penghuni_id' => $this->penghuni->id, 'kontrak_id' => $kontrak->id, 'kamar_id' => $kamar->id, 'subtotal' => 2000000, 'penalty' => 50000, 'total' => 2050000, 'discount' => 0]);
    }

    private function agreement(array $over = []): ManagementAgreement
    {
        return ManagementAgreement::factory()->create(array_merge([
            'kos_id' => $this->kos->id, 'property_owner_id' => $this->po->id,
            'commission_type' => 'percent', 'commission_value' => 10,
            'effective_from' => '2026-01-01', 'effective_to' => null,
        ], $over));
    }

    public function test_penalty_excluded_from_commission_basis(): void
    {
        $this->agreement();
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id, 'penghuni_id' => $this->penghuni->id,
            'amount' => 2050000, 'rent_amount' => 2000000, 'penalty_amount' => 50000,
            'payment_date' => '2026-09-15', 'verification_status' => 'approved',
        ]);
        $this->actingAs($this->owner)->post(route('owner.payouts.store'), [
            'kos_id' => $this->kos->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30',
        ])->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(2000000.0, (float) $payout->gross_amount);
        $this->assertSame(50000.0, (float) $payout->penalty_excluded_total);
        $this->assertSame(200000.0, (float) $payout->commission_total);
        $this->assertSame(1800000.0, (float) $payout->amount);
        $item = PayoutItem::firstOrFail();
        $this->assertSame(50000.0, (float) $item->penalty_excluded);
    }

    public function test_needs_agreement_blocks_payout(): void
    {
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id, 'penghuni_id' => $this->penghuni->id,
            'amount' => 2000000, 'payment_date' => '2026-09-15', 'verification_status' => 'approved',
        ]);
        $this->actingAs($this->owner)->post(route('owner.payouts.store'), [
            'kos_id' => $this->kos->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30',
        ])->assertSessionHas('error');
        $this->assertSame(0, Payout::count());
    }

    public function test_rate_change_not_retroactive(): void
    {
        $this->agreement(['commission_value' => 10, 'effective_from' => '2026-01-01', 'effective_to' => '2026-09-14']);
        ManagementAgreement::factory()->create([
            'kos_id' => $this->kos->id, 'property_owner_id' => $this->po->id,
            'commission_type' => 'percent', 'commission_value' => 20,
            'effective_from' => '2026-09-15', 'effective_to' => null,
        ]);
        Pembayaran::factory()->create(['tagihan_id' => $this->tagihan->id, 'penghuni_id' => $this->penghuni->id, 'amount' => 1000000, 'rent_amount' => 1000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-10', 'verification_status' => 'approved']);
        Pembayaran::factory()->create(['tagihan_id' => $this->tagihan->id, 'penghuni_id' => $this->penghuni->id, 'amount' => 1000000, 'rent_amount' => 1000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-20', 'verification_status' => 'approved']);
        $this->actingAs($this->owner)->post(route('owner.payouts.store'), [
            'kos_id' => $this->kos->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30',
        ])->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(300000.0, (float) $payout->commission_total);
        $items = PayoutItem::orderBy('id')->get();
        $this->assertSame(100000.0, (float) $items[0]->commission_amount);
        $this->assertSame(200000.0, (float) $items[1]->commission_amount);
    }

    public function test_unique_pembayaran_prevents_double_payout(): void
    {
        $this->agreement();
        $pay = Pembayaran::factory()->create(['tagihan_id' => $this->tagihan->id, 'penghuni_id' => $this->penghuni->id, 'amount' => 1000000, 'payment_date' => '2026-09-10', 'verification_status' => 'approved']);
        $this->actingAs($this->owner)->post(route('owner.payouts.store'), ['kos_id' => $this->kos->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-15'])->assertRedirect();
        $this->actingAs($this->owner)->post(route('owner.payouts.store'), ['kos_id' => $this->kos->id, 'period_start' => '2026-09-10', 'period_end' => '2026-09-20'])->assertSessionHas('error');
        $this->assertSame(1, PayoutItem::where('pembayaran_id', $pay->id)->count());
    }

    public function test_fixed_commission_type(): void
    {
        $this->agreement(['commission_type' => 'fixed', 'commission_value' => 150000]);
        Pembayaran::factory()->create(['tagihan_id' => $this->tagihan->id, 'penghuni_id' => $this->penghuni->id, 'amount' => 2000000, 'rent_amount' => 2000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-15', 'verification_status' => 'approved']);
        $this->actingAs($this->owner)->post(route('owner.payouts.store'), ['kos_id' => $this->kos->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30'])->assertRedirect();
        $this->assertSame(150000.0, (float) Payout::firstOrFail()->commission_total);
        $this->assertSame(1850000.0, (float) Payout::firstOrFail()->amount);
    }

    public function test_zero_percent_explicit_allowed(): void
    {
        $this->agreement(['commission_value' => 0]);
        Pembayaran::factory()->create(['tagihan_id' => $this->tagihan->id, 'penghuni_id' => $this->penghuni->id, 'amount' => 1000000, 'payment_date' => '2026-09-15', 'verification_status' => 'approved']);
        $this->actingAs($this->owner)->post(route('owner.payouts.store'), ['kos_id' => $this->kos->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30'])->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(0.0, (float) $payout->commission_total);
        $this->assertSame(1000000.0, (float) $payout->amount);
    }
}
