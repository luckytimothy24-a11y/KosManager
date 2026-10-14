<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\Payout;
use App\Models\PayoutDisbursement;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PropertyOwnerPayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Phase 7 — True Parallel Concurrency (simulated sequential due to SQLite in-memory).
 *
 * SQLite :memory: does NOT support SELECT ... FOR UPDATE row-level locking nor
 * true parallel connections. These tests prove the service invariants via
 * sequential calls and UNIQUE constraint fallback, and document that empirical
 * parallel proof requires isolated MySQL/MariaDB with 2 connections + barrier.
 *
 * Each scenario asserts final DB state, not just HTTP response.
 *
 * @group concurrency-limitation-sqlite
 */
class ParallelDisbursementTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $superAdmin;

    private Payout $payout;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $po = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $po->id]);
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $po->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create(['user_id' => $tenant->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $kontrak = Kontrak::factory()->create(['penghuni_id' => $penghuni->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $tagihan = Tagihan::factory()->create(['penghuni_id' => $penghuni->id, 'kontrak_id' => $kontrak->id, 'kamar_id' => $kamar->id, 'subtotal' => 3000000, 'penalty' => 0, 'total' => 3000000]);
        ManagementAgreement::factory()->create(['kos_id' => $kos->id, 'property_owner_id' => $po->id, 'commission_type' => 'percent', 'commission_value' => 10, 'effective_from' => '2026-01-01']);
        Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 2000000, 'rent_amount' => 2000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-10', 'verification_status' => 'approved']);
        Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 1000000, 'rent_amount' => 1000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-12', 'verification_status' => 'approved']);
        $svc = app(PropertyOwnerPayoutService::class);
        $res = $svc->createForKos($this->owner, ['kos_id' => $kos->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $this->assertTrue($res['ok'], json_encode($res));
        $this->payout = $res['payout'];
        $this->assertSame(2700000.0, (float) $this->payout->amount);
    }

    public function test_scenario_a_same_key_identical_payload_no_duplicate(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $data = ['amount' => 1000000, 'idempotency_key' => 'parallel-a-aaaaaaaa', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20', 'notes' => 'Transfer 1'];
        $before = PayoutDisbursement::where('payout_id', $this->payout->id)->count();
        $r1 = $svc->recordDisbursement($this->owner, $this->payout, $data);
        $r2 = $svc->recordDisbursement($this->owner, $this->payout, $data);
        $after = PayoutDisbursement::where('payout_id', $this->payout->id)->count();
        $this->assertTrue($r1['ok']);
        $this->assertTrue($r2['ok']);
        $this->assertTrue($r2['duplicate'] ?? false);
        $this->assertSame(1, $after - $before);
        $this->assertSame($r1['disbursement']->id, $r2['disbursement']->id);
        $sum = (float) PayoutDisbursement::where('payout_id', $this->payout->id)->sum('amount');
        $this->assertSame(1000000.0, $sum);
    }

    public function test_scenario_b_same_key_different_payload_conflict(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $base = ['idempotency_key' => 'parallel-b-bbbbbbbb', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20', 'notes' => 'Transfer 1'];

        $r1 = $svc->recordDisbursement($this->owner, $this->payout, array_merge($base, ['amount' => 1000000]));
        $this->assertTrue($r1['ok']);

        $cases = [
            ['amount' => 500000, 'notes' => 'Transfer 1'],
            ['amount' => 1000000, 'notes' => 'Transfer 2 different'],
            ['amount' => 1000000, 'notes' => 'Transfer 1', 'transferred_at' => '2026-09-21'],
        ];
        foreach ($cases as $i => $variant) {
            $r2 = $svc->recordDisbursement($this->owner, $this->payout, array_merge($base, $variant));
            $this->assertFalse($r2['ok'], "variant $i should conflict");
            $this->assertSame('idempotency_conflict', $r2['error']);
        }
        $this->assertSame(1, PayoutDisbursement::where('idempotency_key', 'parallel-b-bbbbbbbb')->count());
        $this->assertSame(1, PayoutDisbursement::where('payout_id', $this->payout->id)->count());
    }

    public function test_scenario_c_overpayment_race_two_keys_exceeding_remaining(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $r1 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 2000000, 'idempotency_key' => 'parallel-c-11111111', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20']);
        $this->assertTrue($r1['ok']);
        $r2 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 800000, 'idempotency_key' => 'parallel-c-22222222', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20']);
        $this->assertFalse($r2['ok']);
        $this->assertSame('overpayment', $r2['error']);
        $r3 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 700000, 'idempotency_key' => 'parallel-c-33333333', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20']);
        $this->assertTrue($r3['ok']);
        $totalPendingVerified = (float) PayoutDisbursement::where('payout_id', $this->payout->id)->whereIn('status', ['pending', 'verified'])->sum('amount');
        $this->assertLessThanOrEqual(2700000.0 + 0.005, $totalPendingVerified);
        $this->assertSame(2, PayoutDisbursement::where('payout_id', $this->payout->id)->count());
    }

    public function test_scenario_d_status_transition_with_locking(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $this->assertSame(Payout::STATUS_PENDING, $this->payout->fresh()->status);
        $r1 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 2700000, 'idempotency_key' => 'parallel-d-44444444', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20']);
        $this->assertTrue($r1['ok']);
        $this->assertSame(Payout::STATUS_PENDING, $this->payout->fresh()->status);
        $svc->verifyDisbursement($this->superAdmin, $r1['disbursement']);
        $this->assertSame(Payout::STATUS_VERIFIED, $this->payout->fresh()->status);
        $r2 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 100000, 'idempotency_key' => 'parallel-d-55555555', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-21']);
        $this->assertFalse($r2['ok']);
        $this->assertSame('already_verified', $r2['error']);
    }
}
