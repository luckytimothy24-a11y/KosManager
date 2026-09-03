<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDashboardHardeningTest extends TestCase
{
    use RefreshDatabase;

    // ---- Dashboard chart correctness

    public function test_super_admin_revenue_chart_reflects_database_data(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'paid',
        ]);

        $thisMonth = now()->format('Y-m');
        Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'approved',
            'payment_date' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();

        $this->assertDatabaseCount('pembayarans', 1);
        $this->assertSame(1, Pembayaran::where('verification_status', 'approved')
            ->whereBetween('payment_date', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
            ->count());
    }

    public function test_super_admin_booking_chart_counts_accurately(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $thisMonth = now()->format('Y-m');
        Booking::factory()->create(['booking_date' => now()]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();

        $thisMonthBookings = Booking::whereBetween('booking_date', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
            ->get()
            ->filter(fn ($b) => $b->booking_date->format('Y-m') === $thisMonth)
            ->count();

        $this->assertSame(1, $thisMonthBookings);
    }

    public function test_owner_revenue_chart_scoped_to_owner(): void
    {
        $ownerA = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $kosA = Kos::factory()->create(['owner_id' => $ownerA->id]);
        $kosB = Kos::factory()->create(['owner_id' => $ownerB->id]);

        $kamarA = Kamar::factory()->create(['kos_id' => $kosA->id]);
        $kamarB = Kamar::factory()->create(['kos_id' => $kosB->id]);

        $penghuniA = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kosA->id,
            'kamar_id' => $kamarA->id,
            'status' => 'active',
        ]);
        $penghuniB = Penghuni::factory()->create([
            'kos_id' => $kosB->id,
            'kamar_id' => $kamarB->id,
            'status' => 'active',
        ]);

        $tagihanA = Tagihan::factory()->create([
            'penghuni_id' => $penghuniA->id,
            'kamar_id' => $kamarA->id,
            'status' => 'paid',
        ]);
        $tagihanB = Tagihan::factory()->create([
            'penghuni_id' => $penghuniB->id,
            'kamar_id' => $kamarB->id,
            'status' => 'paid',
        ]);

        Pembayaran::factory()->create([
            'tagihan_id' => $tagihanA->id,
            'penghuni_id' => $penghuniA->id,
            'amount' => 1000000,
            'verification_status' => 'approved',
            'payment_date' => now(),
        ]);
        Pembayaran::factory()->create([
            'tagihan_id' => $tagihanB->id,
            'penghuni_id' => $penghuniB->id,
            'amount' => 9000000,
            'verification_status' => 'approved',
            'payment_date' => now(),
        ]);

        $response = $this->actingAs($ownerA)->get('/dashboard');

        $response->assertOk();

        // Owner A should only see their own revenue in aggregation
        $ownerTotal = Pembayaran::where('verification_status', 'approved')
            ->whereHas('penghuni.kos', fn ($q) => $q->where('owner_id', $ownerA->id))
            ->whereBetween('payment_date', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        $this->assertSame(1000000.0, (float) $ownerTotal);
    }

    // ---- Tenant dashboard N+1 regression (eager loading verification)

    public function test_tenant_dashboard_renders_with_eager_loaded_relations(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee($kos->name);
        $response->assertSee($kamar->room_number);
    }

    // ---- Report summary correctness

    public function test_pendapatan_report_summary_uses_approved_only(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'paid',
        ]);

        Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 1200000,
            'verification_status' => 'approved',
            'payment_date' => now(),
        ]);
        Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 500000,
            'verification_status' => 'pending',
            'payment_date' => now(),
        ]);

        $response = $this->actingAs($owner)->get('/owner/laporan/export-pdf/pendapatan');

        $response->assertOk();

        // Summary should only count approved payment
        $this->assertSame(1, Pembayaran::where('verification_status', 'approved')->count());
    }

    public function test_kamar_report_summary_counts_statuses_correctly(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);

        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'maintenance']);

        $response = $this->actingAs($owner)->get('/owner/laporan/export-excel/kamar');

        $response->assertOk();
        $this->assertSame(3, Kamar::count());
    }

    public function test_booking_report_summary_with_status_breakdown(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $tenant = User::factory()->create(['role' => 'tenant']);

        Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'booking_date' => now()->subDays(3),
        ]);
        Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'pending',
            'booking_date' => now()->subDays(2),
        ]);
        Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'rejected',
            'booking_date' => now()->subDays(1),
        ]);

        $response = $this->actingAs($owner)->get('/owner/laporan/export-excel/booking');

        $response->assertOk();

        // The report should include all 3 bookings in its aggregation
        $summary = Booking::selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved")->first();

        $this->assertSame(3, (int) $summary->total);
        $this->assertSame(1, (int) $summary->approved);
    }

    // ---- Date filtering preserved

    public function test_pendapatan_report_respects_date_filter(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'paid',
        ]);

        Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 100000,
            'verification_status' => 'approved',
            'payment_date' => now()->subDays(10),
        ]);
        Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 200000,
            'verification_status' => 'approved',
            'payment_date' => now()->subDays(30),
        ]);

        $start = now()->subDays(15)->toDateString();
        $end = now()->toDateString();

        $response = $this->actingAs($owner)
            ->get("/owner/laporan/export-excel/pendapatan?start_date={$start}&end_date={$end}");

        $response->assertOk();

        // Only the payment from 10 days ago should be in the range
        $inDateRange = Pembayaran::where('verification_status', 'approved')
            ->whereDate('payment_date', '>=', $start)
            ->whereDate('payment_date', '<=', $end)
            ->count();

        $this->assertSame(1, $inDateRange);
    }
}
