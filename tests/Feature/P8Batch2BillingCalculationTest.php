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

/**
 * P8 Batch 2 — Billing Correctness (deterministic ceiling months).
 *
 * Subtotal = harga kontrak x jumlah bulan ceiling (setiap bulan kalender yang
 * tersentuh dihitung satu bulan penuh, tanpa pro-rata) untuk sewa bulanan.
 */
class P8Batch2BillingCalculationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Kontrak $kontrak;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => User::factory()->create(['role' => 'tenant'])->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $this->kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'rental_type' => 'monthly',
            'rental_price' => 1000000,
            'status' => 'active',
        ]);
    }

    private function store(string $start, string $end): mixed
    {
        return $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $this->kontrak->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => $start,
            'period_end' => $end,
            'discount' => 0,
            'penalty' => 0,
            'due_date' => $end,
        ]);
    }

    private function assertBilled(string $start, string $end, int $expectedMonths): void
    {
        $this->store($start, $end)->assertRedirect(route('owner.tagihan.index'));

        $tagihan = Tagihan::where('kontrak_id', $this->kontrak->id)
            ->whereDate('period_start', $start)
            ->whereDate('period_end', $end)
            ->firstOrFail();

        $this->assertSame(1000000 * $expectedMonths, (int) $tagihan->subtotal);
        $this->assertSame(1000000 * $expectedMonths, (int) $tagihan->total);
    }

    public function test_exact_full_month_is_one_month(): void
    {
        $this->assertBilled('2024-01-01', '2024-01-31', 1);
    }

    public function test_exact_full_month_leap_year_february(): void
    {
        $this->assertBilled('2024-02-01', '2024-02-29', 1);
        $this->assertBilled('2023-02-01', '2023-02-28', 1);
    }

    public function test_partial_month_within_single_calendar_month_is_one_month(): void
    {
        // Parsial dalam satu bulan -> ceiling = 1 bulan penuh.
        $this->assertBilled('2024-01-15', '2024-01-25', 1);
    }

    public function test_crossing_calendar_boundary_counts_both_months(): void
    {
        // Menyentuh Jan (parsial) + Feb (parsial) -> 2 bulan.
        $this->assertBilled('2024-01-15', '2024-02-14', 2);
        $this->assertBilled('2024-01-15', '2024-02-15', 2);
    }

    public function test_month_end_start_is_deterministic(): void
    {
        // Mulai dari tanggal akhir bulan tidak boleh menghasilkan angka yang
        // bergantung pada clamping/overflow (bug lamanya).
        $this->assertBilled('2024-01-31', '2024-02-29', 2);
        $this->assertBilled('2024-03-31', '2024-04-30', 2);
        $this->assertBilled('2024-01-31', '2024-03-31', 3);
    }

    public function test_multi_month_period(): void
    {
        $this->assertBilled('2024-01-01', '2024-03-31', 3);
        $this->assertBilled('2024-01-10', '2024-04-09', 4);
    }

    public function test_cross_year_period(): void
    {
        $this->assertBilled('2024-12-15', '2025-02-14', 3);
    }

    public function test_one_day_monthly_period_counts_one_month(): void
    {
        // Rentang 1 hari (mulai esok) pada kontrak bulanan -> 1 bulan penuh (ceiling).
        $this->assertBilled('2024-01-15', '2024-01-16', 1);
    }

    public function test_same_start_end_date_is_rejected_by_validation(): void
    {
        // Periode wajib berakhir setelah dimulai; start == end ditolak.
        $this->store('2024-01-15', '2024-01-15')
            ->assertSessionHasErrors('period_end');

        $this->assertDatabaseCount('tagihans', 0);
    }

    public function test_daily_one_day_unit(): void
    {
        $this->kontrak->update(['rental_type' => 'daily', 'rental_price' => 100000]);

        $this->store('2024-01-15', '2024-01-16')->assertRedirect(route('owner.tagihan.index'));

        $this->assertDatabaseHas('tagihans', [
            'kontrak_id' => $this->kontrak->id,
            'subtotal' => 200000, // 2 hari (inklusif)
        ]);
    }
}
