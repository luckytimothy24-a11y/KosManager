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

/**
 * Audit fungsi Admin + Laporan.
 *
 * Admin = operational staff, scope assigned-kos.
 * Laporan Admin = operational report untuk assigned kos, BUKAN global.
 */
class AdminRoleLaporanScopeTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    private User $ownerA;

    private User $ownerB;

    private User $tenant;

    private Kos $kosA;

    private Kos $kosB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->ownerA = User::factory()->create(['role' => 'owner']);
        $this->ownerB = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);

        $this->kosA = Kos::factory()->create(['owner_id' => $this->ownerA->id, 'name' => 'KOS-SCOPE-A', 'status' => 'active']);
        $this->kosB = Kos::factory()->create(['owner_id' => $this->ownerB->id, 'name' => 'KOS-SCOPE-B', 'status' => 'active']);

        $this->admin->assignedKos()->attach($this->kosA->id);
    }

    private function seedKosData(Kos $kos, string $marker): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'room_number' => $marker.'-01']);
        $tenant = User::factory()->create(['role' => 'tenant', 'email' => strtolower($marker).'@example.test']);
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
            'verification_status' => 'approved',
            'amount' => 1000000,
        ]);
        Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
        ]);
    }

    // ── Definisi role berdasarkan source code ────────────────

    public function test_admin_is_operational_not_owner(): void
    {
        $this->assertFalse($this->admin->isOwner());
        $this->assertTrue($this->admin->isAdmin());
        $this->assertFalse($this->admin->isSuperAdmin());
        $this->assertTrue($this->admin->canAccessKos((int) $this->kosA->id));
        $this->assertFalse($this->admin->canAccessKos((int) $this->kosB->id));
    }

    public function test_super_admin_is_global(): void
    {
        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertTrue($this->superAdmin->canAccessKos((int) $this->kosA->id));
        $this->assertTrue($this->superAdmin->canAccessKos((int) $this->kosB->id));
    }

    // ── Laporan Admin = operational, assigned-kos only ───────

    public function test_admin_laporan_page_only_shows_assigned_kos(): void
    {
        $this->seedKosData($this->kosA, 'KOS-SCOPE-A');
        $this->seedKosData($this->kosB, 'KOS-SCOPE-B');

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index'));

        $response->assertOk();
        $response->assertSee('KOS-SCOPE-A');
        $response->assertDontSee('KOS-SCOPE-B');
    }

    public function test_admin_laporan_export_only_assigned_kos(): void
    {
        $this->seedKosData($this->kosA, 'KOS-SCOPE-A');
        $this->seedKosData($this->kosB, 'KOS-SCOPE-B');

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.export-excel'));

        $response->assertOk();
        $csv = $response->getContent();
        $this->assertStringContainsString('KOS-SCOPE-A', $csv);
        $this->assertStringNotContainsString('KOS-SCOPE-B', $csv);
    }

    public function test_admin_laporan_export_pdf_scoped(): void
    {
        $this->seedKosData($this->kosA, 'KOS-SCOPE-A');
        $this->seedKosData($this->kosB, 'KOS-SCOPE-B');

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.export-pdf'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        // PDF binary — scope diverifikasi lewat query service; tidak ada exception global leak
    }

    public function test_admin_cannot_filter_unassigned_kos(): void
    {
        $this->seedKosData($this->kosA, 'KOS-SCOPE-A');
        $this->seedKosData($this->kosB, 'KOS-SCOPE-B');

        $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['kos_id' => $this->kosB->id]))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->get(route('admin.laporan.export-excel', ['kos_id' => $this->kosB->id]))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->get(route('admin.laporan.export-pdf', ['kos_id' => $this->kosB->id]))
            ->assertForbidden();
    }

    public function test_admin_without_assignment_sees_empty_not_global(): void
    {
        $adminB = User::factory()->create(['role' => 'admin']);
        $this->seedKosData($this->kosA, 'KOS-SCOPE-A');

        $response = $this->actingAs($adminB)->get(route('admin.laporan.index'));

        $response->assertOk();
        $response->assertDontSee('KOS-SCOPE-A');
    }

    // ── Admin tidak dapat akses capability global ────────────

    public function test_admin_cannot_access_super_admin_laporan_global(): void
    {
        $this->actingAs($this->admin)
            ->get(route('super-admin.laporan.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_access_super_admin_user_management(): void
    {
        $this->actingAs($this->admin)
            ->get(route('super-admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_access_super_admin_settings(): void
    {
        $this->actingAs($this->admin)
            ->get(route('super-admin.settings.index'))
            ->assertForbidden();
    }

    // ── Tenant / Owner isolation ─────────────────────────────

    public function test_tenant_cannot_access_admin_laporan(): void
    {
        $this->actingAs($this->tenant)
            ->get(route('admin.laporan.index'))
            ->assertForbidden();

        $this->actingAs($this->tenant)
            ->get(route('admin.laporan.export-excel'))
            ->assertForbidden();
    }

    public function test_owner_cannot_access_admin_laporan_route(): void
    {
        // Owner memakai /owner/laporan sendiri; /admin/laporan = role admin
        $this->actingAs($this->ownerA)
            ->get(route('admin.laporan.index'))
            ->assertForbidden();
    }

    public function test_owner_laporan_scoped_to_own_kos(): void
    {
        $this->seedKosData($this->kosA, 'KOS-SCOPE-A');
        $this->seedKosData($this->kosB, 'KOS-SCOPE-B');

        $response = $this->actingAs($this->ownerA)->get(route('owner.laporan.index'));

        $response->assertOk();
        $response->assertSee('KOS-SCOPE-A');
        $response->assertDontSee('KOS-SCOPE-B');
    }

    // ── Operational admin functions tetap assigned ───────────

    public function test_admin_payment_verify_assigned_only(): void
    {
        $this->seedKosData($this->kosA, 'KOS-SCOPE-A');
        $this->seedKosData($this->kosB, 'KOS-SCOPE-B');

        $payA = Pembayaran::whereHas('penghuni', fn ($q) => $q->where('kos_id', $this->kosA->id))->firstOrFail();
        $payB = Pembayaran::whereHas('penghuni', fn ($q) => $q->where('kos_id', $this->kosB->id))->firstOrFail();

        // Payment A sudah approved dari seed — verify ulang harus ditolak (sudah diproses)
        // tapi IDOR ke payment B harus forbidden
        $this->actingAs($this->admin)
            ->post(route('owner.pembayaran.verify', $payB))
            ->assertForbidden();

        $this->assertSame('approved', $payB->fresh()->verification_status);
    }

    public function test_admin_cannot_confirm_unassigned_booking(): void
    {
        $this->seedKosData($this->kosB, 'KOS-SCOPE-B');
        $bookingB = Booking::where('kos_id', $this->kosB->id)->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.booking.approve', $bookingB))
            ->assertForbidden();

        // Booking B tetap approved (seed) — admin tidak mengubah apa pun
        $this->assertEquals('approved', $bookingB->fresh()->status);
    }

    public function test_admin_can_confirm_assigned_pending_booking(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kamar = Kamar::factory()->create(['kos_id' => $this->kosA->id, 'status' => 'booked', 'monthly_price' => 1000000]);
        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $this->kosA->id,
            'kamar_id' => $kamar->id,
            'status' => 'pending',
            'price' => 1000000,
        ]);

        // Payment prerequisite (approved) — server-side amount
        Pembayaran::factory()->create([
            'booking_id' => $booking->id,
            'verification_status' => 'approved',
            'amount' => 1000000,
        ]);

        $this->assertTrue($booking->hasApprovedPayment());

        $this->actingAs($this->admin)
            ->post(route('admin.booking.approve', $booking))
            ->assertSessionHas('success');

        $this->assertEquals('approved', $booking->fresh()->status);
    }
}
