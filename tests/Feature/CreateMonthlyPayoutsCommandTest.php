<?php

namespace Tests\Feature;

use App\Console\Commands\CreateMonthlyPayouts;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\Payout;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test CreateMonthlyPayouts Scheduler Command
 */
class CreateMonthlyPayoutsCommandTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private PropertyOwner $propertyOwner;

    private Kos $kos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->propertyOwner = PropertyOwner::factory()->create(['name' => 'Pemilik Hukum']);
        PropertyOwnerBankAccount::factory()->create([
            'property_owner_id' => $this->propertyOwner->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder_name' => 'Pemilik Hukum',
        ]);

        $this->kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'active',
            'name' => 'Kos Test',
        ]);

        ManagementAgreement::factory()->create([
            'kos_id' => $this->kos->id,
            'property_owner_id' => $this->propertyOwner->id,
            'commission_type' => 'percent',
            'commission_value' => 10,
            'effective_from' => '2026-01-01',
        ]);
    }

    // ── Basic Creation ───────────────────────────────────

    public function test_command_creates_monthly_payout_for_kos_with_approved_payments(): void
    {
        $this->seedApprovedPaymentsForLastMonth();

        $this->artisan('payout:create-monthly')
            ->assertExitCode(0);

        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();
        $this->assertSame(Payout::STATUS_PENDING, $payout->status);
        $this->assertStringStartsWith('PO-', $payout->payout_number);
    }

    public function test_command_skips_kos_without_property_owner(): void
    {
        $kosNoPo = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'property_owner_id' => null,
            'status' => 'active',
        ]);

        $this->artisan('payout:create-monthly')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('payouts', ['kos_id' => $kosNoPo->id]);
    }

    public function test_command_skips_kos_without_management_agreement(): void
    {
        // Kos WITH property owner + bank account, BUT NO management agreement
        $poNoAgreement = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create([
            'property_owner_id' => $poNoAgreement->id,
        ]);

        $kosNoAgreement = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'property_owner_id' => $poNoAgreement->id,
            'status' => 'active',
            'name' => 'Kos Tanpa Agreement',
        ]);

        // Seed approved payments
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kamar = Kamar::factory()->create(['kos_id' => $kosNoAgreement->id, 'monthly_price' => 1500000]);
        $penghuni = Penghuni::factory()->create(['user_id' => $tenant, 'kos_id' => $kosNoAgreement->id, 'kamar_id' => $kamar->id]);
        $kontrak = Kontrak::factory()->create(['penghuni_id' => $penghuni->id, 'kos_id' => $kosNoAgreement->id, 'kamar_id' => $kamar->id]);
        $tagihan = Tagihan::factory()->create(['penghuni_id' => $penghuni->id, 'kontrak_id' => $kontrak->id, 'kamar_id' => $kamar->id, 'total' => 1500000]);
        Pembayaran::factory()->create([
            'payment_number' => 'PY-NO-AGREEMENT-001',
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 1500000,
            'payment_date' => now()->subMonth()->toDateString(),
            'verification_status' => 'approved',
        ]);

        // Run command — should skip (needs_agreement) with exit code 0
        $this->artisan('payout:create-monthly')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('payouts', ['kos_id' => $kosNoAgreement->id]);
    }

    public function test_command_skips_kos_without_approved_payments(): void
    {
        $this->artisan('payout:create-monthly')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('payouts', ['kos_id' => $this->kos->id]);
    }

    // ── Duplicate Prevention ─────────────────────────────

    public function test_command_skips_duplicate_payout_same_period(): void
    {
        $this->seedApprovedPaymentsForLastMonth();

        // Create first payout manually
        $payout1 = Payout::factory()->create([
            'kos_id' => $this->kos->id,
            'period_start' => now()->subMonth()->startOfMonth()->toDateString(),
            'period_end' => now()->subMonth()->endOfMonth()->toDateString(),
            'status' => Payout::STATUS_PENDING,
        ]);

        // Run command
        $this->artisan('payout:create-monthly')
            ->assertExitCode(0);

        // Should still be only 1 payout
        $this->assertSame(1, Payout::where('kos_id', $this->kos->id)->count());
    }

    // ── Dry Run Mode ─────────────────────────────────────

    public function test_command_with_dry_run_does_not_create_payouts(): void
    {
        $this->seedApprovedPaymentsForLastMonth();

        $this->artisan('payout:create-monthly --dry-run')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('payouts', ['kos_id' => $this->kos->id]);
    }

    public function test_command_with_dry_run_displays_what_would_be_created(): void
    {
        $this->seedApprovedPaymentsForLastMonth();

        $this->artisan('payout:create-monthly --dry-run')
            ->expectsOutput('DRY')
            ->assertExitCode(0);
    }

    // ── Filtering by Kos ID ──────────────────────────────

    public function test_command_with_specific_kos_id_only_processes_that_kos(): void
    {
        $this->seedApprovedPaymentsForLastMonth();

        $this->artisan('payout:create-monthly', ['--kos-id' => $this->kos->id])
            ->assertExitCode(0);

        // Payout should exist for kos1
        $this->assertSame(1, Payout::where('kos_id', $this->kos->id)->count());
    }

    // ── Summary Output ───────────────────────────────────

    public function test_command_displays_creation_summary(): void
    {
        $this->seedApprovedPaymentsForLastMonth();

        $this->artisan('payout:create-monthly')
            ->assertExitCode(0);
    }

    // ── Helpers ───────────────────────────────────────────

    private function seedApprovedPaymentsForLastMonth(): void
    {
        $lastMonth = now()->subMonth();
        $periodStart = $lastMonth->startOfMonth()->toDateString();
        $periodEnd = $lastMonth->endOfMonth()->toDateString();

        $tenant = User::factory()->create(['role' => 'tenant']);
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos,
            'monthly_price' => 1500000,
        ]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant,
            'kos_id' => $this->kos,
            'kamar_id' => $kamar,
        ]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni,
            'kos_id' => $this->kos,
            'kamar_id' => $kamar,
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni,
            'kontrak_id' => $kontrak,
            'kamar_id' => $kamar,
            'total' => 1500000,
        ]);

        // Approved payment in period
        Pembayaran::factory()->create([
            'payment_number' => 'PY-CMD-TEST-001',
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 1500000,
            'payment_date' => $lastMonth->toDateString(),
            'verification_status' => 'approved',
        ]);
    }
}
