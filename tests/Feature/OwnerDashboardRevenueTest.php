<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Web-layer hardening for Owner Dashboard revenue.
 *
 * Revenue definition (untouchable):
 *   Revenue = SUM(amount) of Pembayaran with verification_status 'approved'
 *   scoped to Pembayaran->penghuni->kos owned by the logged-in Owner.
 */
class OwnerDashboardRevenueTest extends TestCase
{
    use RefreshDatabase;

    private function formatRevenue(int $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    /**
     * Build the owner -> kos -> kamar -> penghuni -> tagihan chain required for
     * the dashboard revenue query (Pembayaran->penghuni->kos).
     */
    private function buildTenantInKos(User $owner, Kos $kos): Penghuni
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);

        return Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
    }

    private function createPayment(Penghuni $penghuni, int $amount, string $status): Pembayaran
    {
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $penghuni->kos_id,
            'kamar_id' => $penghuni->kamar_id,
            'status' => 'active',
        ]);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $penghuni->kamar_id,
            'status' => 'unpaid',
            'total' => $amount,
        ]);

        return Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => $amount,
            'verification_status' => $status,
            'payment_date' => now()->toDateString(),
        ]);
    }

    public function test_approved_payment_is_counted_as_revenue(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $penghuni = $this->buildTenantInKos($owner, $kos);

        $this->createPayment($penghuni, 1500000, 'approved');

        $response = $this->actingAs($owner)->get('/dashboard');

        $response->assertOk();
        $response->assertSee($this->formatRevenue(1500000), false);
    }

    public function test_pending_payment_is_excluded_from_revenue(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $penghuni = $this->buildTenantInKos($owner, $kos);

        $this->createPayment($penghuni, 1500000, 'approved');
        $this->createPayment($penghuni, 500000, 'pending');

        $response = $this->actingAs($owner)->get('/dashboard');

        $response->assertOk();
        $response->assertSee($this->formatRevenue(1500000), false);
        $response->assertDontSee($this->formatRevenue(2000000), false);
    }

    public function test_rejected_payment_is_excluded_from_revenue(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $penghuni = $this->buildTenantInKos($owner, $kos);

        $this->createPayment($penghuni, 1500000, 'approved');
        $this->createPayment($penghuni, 750000, 'rejected');

        $response = $this->actingAs($owner)->get('/dashboard');

        $response->assertOk();
        $response->assertSee($this->formatRevenue(1500000), false);
        $response->assertDontSee($this->formatRevenue(2250000), false);
    }

    public function test_owner_only_sees_own_revenue(): void
    {
        $ownerA = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);

        $kosA = Kos::factory()->create(['owner_id' => $ownerA->id]);
        $kosB = Kos::factory()->create(['owner_id' => $ownerB->id]);

        $this->createPayment($this->buildTenantInKos($ownerA, $kosA), 1000000, 'approved');
        $this->createPayment($this->buildTenantInKos($ownerB, $kosB), 2000000, 'approved');

        $this->actingAs($ownerA)->get('/dashboard')
            ->assertOk()
            ->assertSee($this->formatRevenue(1000000), false)
            ->assertDontSee($this->formatRevenue(3000000), false)
            ->assertDontSee($this->formatRevenue(2000000), false);

        $this->actingAs($ownerB)->get('/dashboard')
            ->assertOk()
            ->assertSee($this->formatRevenue(2000000), false)
            ->assertDontSee($this->formatRevenue(3000000), false)
            ->assertDontSee($this->formatRevenue(1000000), false);
    }

    public function test_revenue_sums_across_multiple_kos_of_same_owner(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $kosA = Kos::factory()->create(['owner_id' => $owner->id]);
        $kosB = Kos::factory()->create(['owner_id' => $owner->id]);

        $this->createPayment($this->buildTenantInKos($owner, $kosA), 1000000, 'approved');
        $this->createPayment($this->buildTenantInKos($owner, $kosB), 2500000, 'approved');

        $response = $this->actingAs($owner)->get('/dashboard');

        $response->assertOk();
        $response->assertSee($this->formatRevenue(3500000), false);
    }

    public function test_revenue_is_zero_when_owner_has_no_payments(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)->get('/dashboard');

        $response->assertOk();
        $response->assertSee($this->formatRevenue(0), false);

        $response->assertViewHas('revenueChart', function (array $chart): bool {
            return $chart['values'] === array_fill(0, 6, 0);
        });
    }

    public function test_rejected_then_approved_repayment_counts_only_approved(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $penghuni = $this->buildTenantInKos($owner, $kos);
        $kamar = $penghuni->kamar;

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => Kontrak::factory()->create([
                'penghuni_id' => $penghuni->id,
                'kos_id' => $kos->id,
                'kamar_id' => $kamar->id,
                'status' => 'active',
            ])->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
            'total' => 1500000,
        ]);

        Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 750000,
            'verification_status' => 'rejected',
            'payment_date' => now()->toDateString(),
        ]);

        Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'approved',
            'payment_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($owner)->get('/dashboard');

        $response->assertOk();
        $response->assertSee($this->formatRevenue(1500000), false);
        $response->assertDontSee($this->formatRevenue(2250000), false);
    }

    public function test_monthly_revenue_chart_is_owner_scoped_and_status_filtered(): void
    {
        $ownerA = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);

        $kosA = Kos::factory()->create(['owner_id' => $ownerA->id]);
        $kosB = Kos::factory()->create(['owner_id' => $ownerB->id]);

        $penghuniA = $this->buildTenantInKos($ownerA, $kosA);

        // Valid approved payment in the current month (should appear in chart).
        $this->createPayment($penghuniA, 1500000, 'approved');

        // Pending & rejected from the same owner must NOT appear.
        $this->createPayment($penghuniA, 500000, 'pending');
        $this->createPayment($penghuniA, 750000, 'rejected');

        // Another owner's approved payment must NOT appear.
        $this->createPayment($this->buildTenantInKos($ownerB, $kosB), 2000000, 'approved');

        $response = $this->actingAs($ownerA)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('revenueChart', function (array $chart): bool {
            $values = $chart['values'];

            return count($values) === 6
                && $values[5] === 1500000
                && array_sum($values) === 1500000;
        });
    }
}
