<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\DamageReport;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 2 API Authorization Hardening — regression/security tests.
 *
 * Mengunci:
 * - tenant boundary (own vs milik tenant lain)
 * - owner boundary (kos milik sendiri vs milik owner lain)
 * - admin assigned-kos scope (damage report di luar assignment ditolak)
 * - booking store API tanpa identity fields (GAP-1 fix)
 */
class ApiAuthorizationHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    private function makeKos(User $owner): Kos
    {
        return Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
    }

    private function makeKamar(Kos $kos): Kamar
    {
        return Kamar::factory()->create([
            'kos_id' => $kos->id,
            'status' => 'available',
            'monthly_price' => 1000000,
            'daily_price' => 100000,
        ]);
    }

    public function test_tenant_cannot_view_other_tenant_booking(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = $this->makeKos($owner);
        $kamar = $this->makeKamar($kos);
        $tenantA = User::factory()->create(['role' => 'tenant']);
        $tenantB = User::factory()->create(['role' => 'tenant']);

        $booking = Booking::factory()->create([
            'user_id' => $tenantA->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
        ]);

        $this->withToken($this->token($tenantB))
            ->getJson("/api/v1/bookings/{$booking->id}")
            ->assertStatus(403);
    }

    public function test_tenant_cannot_cancel_other_tenant_booking(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = $this->makeKos($owner);
        $kamar = $this->makeKamar($kos);
        $tenantA = User::factory()->create(['role' => 'tenant']);
        $tenantB = User::factory()->create(['role' => 'tenant']);

        $booking = Booking::factory()->create([
            'user_id' => $tenantA->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
        ]);

        $this->withToken($this->token($tenantB))
            ->postJson("/api/v1/bookings/{$booking->id}/cancel")
            ->assertStatus(403);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'approved']);
    }

    public function test_owner_cannot_view_other_owner_kos_or_booking(): void
    {
        $ownerA = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);
        $kosB = $this->makeKos($ownerB);
        $kamarB = $this->makeKamar($kosB);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $bookingB = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kosB->id,
            'kamar_id' => $kamarB->id,
            'status' => 'approved',
        ]);

        $this->withToken($this->token($ownerA))
            ->getJson("/api/v1/owner/kos/{$kosB->id}")
            ->assertStatus(403);

        $this->withToken($this->token($ownerA))
            ->getJson("/api/v1/owner/bookings/{$bookingB->id}")
            ->assertStatus(403);
    }

    public function test_admin_cannot_view_damage_report_outside_assignment(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kosAssigned = $this->makeKos($owner);
        $kosOther = $this->makeKos($owner);
        $kamarOther = $this->makeKamar($kosOther);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignedKos()->attach($kosAssigned->id);

        $report = DamageReport::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kosOther->id,
            'kamar_id' => $kamarOther->id,
            'status' => 'pending',
        ]);

        $this->withToken($this->token($admin))
            ->getJson("/api/v1/admin/damage-reports/{$report->id}")
            ->assertStatus(403);
    }

    public function test_booking_store_without_identity_fields_returns_201(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = $this->makeKos($owner);
        $kamar = $this->makeKamar($kos);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $response = $this->withToken($this->token($tenant))->postJson('/api/v1/bookings', [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ]);

        $response->assertStatus(201);

        // Booking SELALU pending — tenant tidak bisa meng-set confirmed/approved dari request
        $this->assertDatabaseHas('bookings', [
            'user_id' => $tenant->id,
            'kamar_id' => $kamar->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('penghunis', [
            'user_id' => $tenant->id,
            'status' => 'active',
        ]);
    }

    public function test_owner_cannot_verify_other_owner_payment(): void
    {
        $ownerA = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);
        $kosB = $this->makeKos($ownerB);
        $kamarB = $this->makeKamar($kosB);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kosB->id,
            'kamar_id' => $kamarB->id,
            'status' => 'active',
        ]);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamarB->id,
            'status' => 'unpaid',
        ]);

        $pembayaran = Pembayaran::factory()->create([
            'penghuni_id' => $penghuni->id,
            'tagihan_id' => $tagihan->id,
            'verification_status' => 'pending',
        ]);

        $this->withToken($this->token($ownerA))
            ->postJson("/api/v1/owner/pembayaran/{$pembayaran->id}/verify")
            ->assertStatus(403);
    }
}
