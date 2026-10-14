<?php

namespace Tests\Feature;

use App\Models\Booking;
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
use App\Services\SettlementReportService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SettlementFixedCommissionTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Kos $kosA;

    private Kos $kosB;

    private PropertyOwner $poA;

    private PropertyOwner $poB;

    private Tagihan $tagihanA;

    private Penghuni $penghuniA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $this->poA = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $this->poA->id]);
        $this->kosA = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $this->poA->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $this->kosA->id]);
        $this->penghuniA = Penghuni::factory()->create(['user_id' => $tenant->id, 'kos_id' => $this->kosA->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $kontrak = Kontrak::factory()->create(['penghuni_id' => $this->penghuniA->id, 'kos_id' => $this->kosA->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $this->tagihanA = Tagihan::factory()->create(['penghuni_id' => $this->penghuniA->id, 'kontrak_id' => $kontrak->id, 'kamar_id' => $kamar->id]);

        $this->poB = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $this->poB->id]);
        $this->kosB = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $this->poB->id]);
    }

    private function fixedAgreement(Kos $kos, PropertyOwner $po, int $value, string $from, ?string $to = null): ManagementAgreement
    {
        return ManagementAgreement::factory()->create([
            'kos_id' => $kos->id, 'property_owner_id' => $po->id,
            'commission_type' => 'fixed', 'commission_value' => $value,
            'effective_from' => $from, 'effective_to' => $to,
        ]);
    }

    private function percentAgreement(Kos $kos, PropertyOwner $po, int $value, string $from, ?string $to = null): ManagementAgreement
    {
        return ManagementAgreement::factory()->create([
            'kos_id' => $kos->id, 'property_owner_id' => $po->id,
            'commission_type' => 'percent', 'commission_value' => $value,
            'effective_from' => $from, 'effective_to' => $to,
        ]);
    }

    private function pay(Kos $kos, Penghuni $penghuni, Tagihan $tagihan, int $rent, string $date): Pembayaran
    {
        return Pembayaran::factory()->create([
            'tagihan_id' => null, 'penghuni_id' => $penghuni->id,
            'amount' => $rent, 'rent_amount' => $rent, 'penalty_amount' => 0,
            'payment_date' => $date, 'verification_status' => 'approved',
            'active_payment_key' => null,
        ]);
    }

    private function payout(Kos $kos, string $start, string $end): TestResponse
    {
        return $this->actingAs($this->owner)->post(route('owner.payouts.store'), [
            'kos_id' => $kos->id, 'period_start' => $start, 'period_end' => $end,
        ]);
    }

    private function assertInvariant(Payout $payout): void
    {
        $sumCommission = (float) PayoutItem::where('payout_id', $payout->id)->sum('commission_amount');
        $sumNet = (float) PayoutItem::where('payout_id', $payout->id)->sum('net_amount');
        $sumGross = (float) PayoutItem::where('payout_id', $payout->id)->sum('gross_rent');
        $this->assertEqualsWithDelta((float) $payout->commission_total, $sumCommission, 0.005, 'SUM commission_amount must equal payout.commission_total');
        $this->assertEqualsWithDelta((float) $payout->amount, $sumNet, 0.005, 'SUM net_amount must equal payout.amount');
        $this->assertEqualsWithDelta((float) $payout->gross_amount, $sumGross, 0.005, 'SUM gross_rent must equal payout.gross_amount');
        $this->assertEqualsWithDelta((float) $payout->amount, (float) $payout->gross_amount - (float) $payout->commission_total, 0.005, 'amount must equal gross - commission');
    }

    public function test_fixed_single_payment(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(100000.0, (float) $payout->commission_total);
        $this->assertSame(900000.0, (float) $payout->amount);
        $this->assertInvariant($payout);
    }

    public function test_fixed_two_payments_same_payout(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1500000, '2026-09-20');
        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(100000.0, (float) $payout->commission_total);
        $this->assertSame(2500000.0, (float) $payout->gross_amount);
        $this->assertSame(2400000.0, (float) $payout->amount);
        $items = PayoutItem::orderBy('id')->get();
        $this->assertCount(2, $items);
        $this->assertSame(2500000.0, $items->sum(fn ($i) => (float) $i->gross_rent));
        $this->assertEqualsWithDelta(100000.0, $items->sum(fn ($i) => (float) $i->commission_amount), 0.005);
        $this->assertInvariant($payout);
    }

    public function test_fixed_five_payments_same_payout(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01');
        foreach (['2026-09-02', '2026-09-08', '2026-09-14', '2026-09-20', '2026-09-28'] as $d) {
            $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 500000, $d);
        }
        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(100000.0, (float) $payout->commission_total);
        $this->assertSame(2400000.0, (float) $payout->amount);
        $this->assertInvariant($payout);
    }

    public function test_fixed_two_kos_isolated(): void
    {
        $tenantB = User::factory()->create(['role' => 'tenant']);
        $kamarB = Kamar::factory()->create(['kos_id' => $this->kosB->id]);
        $penghuniB = Penghuni::factory()->create(['user_id' => $tenantB->id, 'kos_id' => $this->kosB->id, 'kamar_id' => $kamarB->id, 'status' => 'active']);
        $kontrakB = Kontrak::factory()->create(['penghuni_id' => $penghuniB->id, 'kos_id' => $this->kosB->id, 'kamar_id' => $kamarB->id, 'status' => 'active']);
        $tagihanB = Tagihan::factory()->create(['penghuni_id' => $penghuniB->id, 'kontrak_id' => $kontrakB->id, 'kamar_id' => $kamarB->id]);

        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01');
        $this->fixedAgreement($this->kosB, $this->poB, 100000, '2026-01-01');

        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1500000, '2026-09-12');
        $this->pay($this->kosB, $penghuniB, $tagihanB, 800000, '2026-09-11');
        $this->pay($this->kosB, $penghuniB, $tagihanB, 700000, '2026-09-13');

        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $this->payout($this->kosB, '2026-09-01', '2026-09-30')->assertRedirect();

        $pa = Payout::where('kos_id', $this->kosA->id)->firstOrFail();
        $pb = Payout::where('kos_id', $this->kosB->id)->firstOrFail();
        $this->assertSame(100000.0, (float) $pa->commission_total);
        $this->assertSame(100000.0, (float) $pb->commission_total);
        $this->assertInvariant($pa);
        $this->assertInvariant($pb);
    }

    public function test_fixed_two_periods(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-20');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-10-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-10-20');

        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $second = $this->payout($this->kosA, '2026-10-01', '2026-10-31');
        $second->assertRedirect();
        if ($second->isRedirect() && session('error')) {
            $this->fail('Second payout failed: '.session('error'));
        }

        $payouts = Payout::where('kos_id', $this->kosA->id)->orderBy('period_start')->get();
        $this->assertCount(2, $payouts, 'Expected 2 payouts, got '.Payout::count().' errors: '.json_encode(session()->all()));
        $p1 = $payouts[0];
        $p2 = $payouts[1];
        $this->assertSame(100000.0, (float) $p1->commission_total);
        $this->assertSame(100000.0, (float) $p2->commission_total);
        $this->assertInvariant($p1);
        $this->assertInvariant($p2);
    }

    public function test_fixed_multiple_agreements_picks_latest(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01', '2026-09-14');
        $this->fixedAgreement($this->kosA, $this->poA, 200000, '2026-09-15', null);
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1500000, '2026-09-20');

        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(200000.0, (float) $payout->commission_total);
        $this->assertInvariant($payout);
    }

    public function test_fixed_latest_not_eligible_not_picked(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01', '2026-09-30');
        $this->fixedAgreement($this->kosA, $this->poA, 200000, '2026-10-01', null);
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1500000, '2026-09-20');

        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(100000.0, (float) $payout->commission_total);
        $this->assertInvariant($payout);
    }

    public function test_fixed_ended_not_picked_after_to(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01', '2026-08-31');
        $this->percentAgreement($this->kosA, $this->poA, 10, '2026-09-01', null);
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-20');

        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(200000.0, (float) $payout->commission_total);
        $this->assertInvariant($payout);
    }

    public function test_mixed_percent_and_fixed(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01', '2026-09-14');
        $this->percentAgreement($this->kosA, $this->poA, 10, '2026-09-15', null);
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-20');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-21');

        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(300000.0, (float) $payout->commission_total);
        $this->assertSame(2700000.0, (float) $payout->amount);
        $this->assertInvariant($payout);
    }

    public function test_penalty_still_excluded(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01');
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihanA->id, 'penghuni_id' => $this->penghuniA->id,
            'amount' => 1050000, 'rent_amount' => 1000000, 'penalty_amount' => 50000,
            'payment_date' => '2026-09-15', 'verification_status' => 'approved',
        ]);
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihanA->id, 'penghuni_id' => $this->penghuniA->id,
            'amount' => 1000000, 'rent_amount' => 1000000, 'penalty_amount' => 0,
            'payment_date' => '2026-09-20', 'verification_status' => 'approved',
        ]);

        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(50000.0, (float) $payout->penalty_excluded_total);
        $this->assertSame(100000.0, (float) $payout->commission_total);
        $this->assertSame(1900000.0, (float) $payout->amount);
        $this->assertInvariant($payout);
    }

    public function test_snapshot_stable_after_agreement_change(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(100000.0, (float) $payout->commission_total);

        ManagementAgreement::where('kos_id', $this->kosA->id)->delete();
        $this->fixedAgreement($this->kosA, $this->poA, 300000, '2026-01-01');
        $payout->refresh();
        $this->assertSame(100000.0, (float) $payout->commission_total);
        $this->assertSame(100000.0, (float) PayoutItem::where('payout_id', $payout->id)->sum('commission_amount'));
    }

    public function test_retry_idempotent(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-12');
        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertSessionHas('error');
        $this->assertSame(1, Payout::count());
        $this->assertSame(100000.0, (float) Payout::firstOrFail()->commission_total);
    }

    public function test_fixed_zero(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 0, '2026-01-01');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-12');
        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(0.0, (float) $payout->commission_total);
        $this->assertSame(2000000.0, (float) $payout->amount);
        $this->assertInvariant($payout);
    }

    public function test_no_eligible_income_guard(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01');
        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertSessionHas('error');
        $this->assertSame(0, Payout::count());
    }

    public function test_effective_boundary_inclusive(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-09-09', '2026-09-21');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-20');
        $resp = $this->payout($this->kosA, '2026-09-10', '2026-09-21');
        $resp->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(100000.0, (float) $payout->commission_total);
        $this->assertInvariant($payout);
        $this->assertSame(2, PayoutItem::where('payout_id', $payout->id)->count());
    }

    public function test_tie_breaker_same_effective_from(): void
    {
        $a1 = $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-09-15', null);
        $this->assertSame('2026-09-15', $a1->effective_from->format('Y-m-d'));
        $this->expectException(QueryException::class);
        ManagementAgreement::factory()->create([
            'kos_id' => $this->kosA->id, 'property_owner_id' => $this->poA->id,
            'commission_type' => 'fixed', 'commission_value' => 200000,
            'effective_from' => '2026-09-15', 'effective_to' => null,
        ]);
    }

    public function test_percent_multiple_unchanged(): void
    {
        $this->percentAgreement($this->kosA, $this->poA, 10, '2026-01-01');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 2000000, '2026-09-12');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1500000, '2026-09-15');
        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(450000.0, (float) $payout->commission_total);
        $this->assertSame(4050000.0, (float) $payout->amount);
        $this->assertInvariant($payout);
    }

    public function test_booking_payment_scope(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01');
        $kamar = Kamar::factory()->create(['kos_id' => $this->kosA->id]);
        $booking = Booking::factory()->create([
            'kos_id' => $this->kosA->id, 'kamar_id' => $kamar->id,
            'user_id' => User::factory()->create(['role' => 'tenant'])->id,
            'status' => 'pending',
        ]);
        Pembayaran::factory()->create([
            'booking_id' => $booking->id, 'tagihan_id' => null, 'penghuni_id' => null,
            'amount' => 1200000, 'rent_amount' => 1200000, 'penalty_amount' => 0,
            'payment_date' => '2026-09-15', 'verification_status' => 'approved',
        ]);
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-16');
        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::firstOrFail();
        $this->assertSame(100000.0, (float) $payout->commission_total);
        $this->assertSame(2200000.0, (float) $payout->gross_amount);
        $this->assertSame(2100000.0, (float) $payout->amount);
        $this->assertInvariant($payout);
    }

    public function test_adjustment_does_not_recalc_fixed(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01');
        $pay = $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-12');
        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::firstOrFail();
        $before = (float) $payout->commission_total;
        $super = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($super)->post(route('owner.adjustments.store'), [
            'pembayaran_id' => $pay->id, 'payout_id' => $payout->id,
            'type' => 'refund', 'amount' => 50000, 'reason' => 'koreksi test',
        ])->assertRedirect();
        $payout->refresh();
        $this->assertSame($before, (float) $payout->commission_total);
        $this->assertInvariant($payout);
    }

    public function test_settlement_report_consistent(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1500000, '2026-09-12');
        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $report = app(SettlementReportService::class)->forOwner($this->owner);
        $this->assertSame(2500000.0, (float) $report['received']);
        $this->assertSame(100000.0, (float) $report['commission']);
        $this->assertSame(2400000.0, (float) $report['net_obligation']);
    }

    public function test_kos_isolation_fixed(): void
    {
        $this->fixedAgreement($this->kosA, $this->poA, 100000, '2026-01-01');
        $pay = $this->pay($this->kosA, $this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $poOther = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $poOther->id]);
        $kosOther = Kos::factory()->create(['owner_id' => $otherOwner->id, 'property_owner_id' => $poOther->id, 'status' => 'active']);
        $kamarOther = Kamar::factory()->create(['kos_id' => $kosOther->id]);
        $tenantOther = User::factory()->create(['role' => 'tenant']);
        $penghuniOther = Penghuni::factory()->create(['user_id' => $tenantOther->id, 'kos_id' => $kosOther->id, 'kamar_id' => $kamarOther->id, 'status' => 'active']);
        $kontrakOther = Kontrak::factory()->create(['penghuni_id' => $penghuniOther->id, 'kos_id' => $kosOther->id, 'kamar_id' => $kamarOther->id, 'status' => 'active']);
        $tagihanOther = Tagihan::factory()->create(['penghuni_id' => $penghuniOther->id, 'kontrak_id' => $kontrakOther->id, 'kamar_id' => $kamarOther->id]);
        ManagementAgreement::factory()->create(['kos_id' => $kosOther->id, 'property_owner_id' => $poOther->id, 'commission_type' => 'fixed', 'commission_value' => 999999, 'effective_from' => '2026-01-01']);
        Pembayaran::factory()->create(['tagihan_id' => $tagihanOther->id, 'penghuni_id' => $penghuniOther->id, 'amount' => 5000000, 'rent_amount' => 5000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-11', 'verification_status' => 'approved']);
        $this->payout($this->kosA, '2026-09-01', '2026-09-30')->assertRedirect();
        $payout = Payout::where('kos_id', $this->kosA->id)->firstOrFail();
        $this->assertSame(100000.0, (float) $payout->commission_total);
        $this->assertSame(1, PayoutItem::where('payout_id', $payout->id)->count());
        $this->assertDatabaseMissing('payout_items', ['payout_id' => $payout->id, 'pembayaran_id' => $tagihanOther->id]);
    }
}
