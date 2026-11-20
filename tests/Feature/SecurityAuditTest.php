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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // 1. Cross-tenant file access: Tenant B cannot access Tenant A's proof
    // ---------------------------------------------------------------

    public function test_tenant_b_cannot_download_tenant_a_payment_proof(): void
    {
        Storage::fake('private');

        $tenantA = User::factory()->create(['role' => 'pencari']);
        $tenantB = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenantA->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
        ]);
        $proofPath = UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf')->store('bukti-pembayaran', 'private');
        $pembayaran = Pembayaran::factory()->create([
            'penghuni_id' => $penghuni->id,
            'tagihan_id' => $tagihan->id,
            'proof_file' => $proofPath,
            'verification_status' => 'pending',
        ]);

        $this->actingAs($tenantB)
            ->get(route('pembayaran.proof', $pembayaran))
            ->assertForbidden();
    }

    // ---------------------------------------------------------------
    // 2. Owner of different kos cannot access payment proof
    // ---------------------------------------------------------------

    public function test_owner_of_different_kos_cannot_download_payment_proof(): void
    {
        Storage::fake('private');

        $ownerA = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create(['owner_id' => $ownerA->id]);
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
            'status' => 'unpaid',
        ]);
        $proofPath = UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf')->store('bukti-pembayaran', 'private');
        $pembayaran = Pembayaran::factory()->create([
            'penghuni_id' => $penghuni->id,
            'tagihan_id' => $tagihan->id,
            'proof_file' => $proofPath,
            'verification_status' => 'pending',
        ]);

        $this->actingAs($ownerB)
            ->get(route('pembayaran.proof', $pembayaran))
            ->assertForbidden();
    }

    // ---------------------------------------------------------------
    // 3. Admin outside scope cannot access payment proof
    // ---------------------------------------------------------------

    public function test_admin_outside_scope_cannot_download_payment_proof(): void
    {
        Storage::fake('private');

        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'pencari']);
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
            'status' => 'unpaid',
        ]);
        $proofPath = UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf')->store('bukti-pembayaran', 'private');
        $pembayaran = Pembayaran::factory()->create([
            'penghuni_id' => $penghuni->id,
            'tagihan_id' => $tagihan->id,
            'proof_file' => $proofPath,
            'verification_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('pembayaran.proof', $pembayaran))
            ->assertForbidden();
    }

    // ---------------------------------------------------------------
    // 4. Super admin can access payment proof
    // ---------------------------------------------------------------

    public function test_super_admin_can_download_payment_proof(): void
    {
        Storage::fake('private');

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create();
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
            'status' => 'unpaid',
        ]);
        $proofPath = UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf')->store('bukti-pembayaran', 'private');
        $pembayaran = Pembayaran::factory()->create([
            'penghuni_id' => $penghuni->id,
            'tagihan_id' => $tagihan->id,
            'proof_file' => $proofPath,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($superAdmin)
            ->get(route('pembayaran.proof', $pembayaran));

        $response->assertOk();
    }

    // ---------------------------------------------------------------
    // 5. Forbidden file type rejected
    // ---------------------------------------------------------------

    public function test_forbidden_file_type_rejected_on_payment_upload(): void
    {
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'pending',
        ]);

        $fakeFile = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($tenant)->post(route('tenant.booking.payment.store', $booking), [
            'payment_method' => 'transfer_bank',
            'proof_file' => $fakeFile,
        ]);

        $response->assertSessionHasErrors('proof_file');
    }

    // ---------------------------------------------------------------
    // 6. Mass assignment: role and status ignored on register
    // ---------------------------------------------------------------

    public function test_register_ignores_role_and_status_from_client(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'super_admin',
            'is_active' => false,
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('pencari', $user->role);
        $this->assertTrue($user->is_active);
    }

    // ---------------------------------------------------------------
    // 7. Mass assignment: role and status ignored on profile update
    // ---------------------------------------------------------------

    public function test_profile_update_rejects_role_and_status(): void
    {
        $tenant = User::factory()->create(['role' => 'pencari']);

        $response = $this->actingAs($tenant)->patch('/profile', [
            'name' => 'Updated Name',
            'email' => $tenant->email,
            'role' => 'super_admin',
            'is_active' => false,
        ]);

        $tenant->refresh();
        $this->assertEquals('pencari', $tenant->role);
        $this->assertTrue($tenant->is_active);
    }

    // ---------------------------------------------------------------
    // 8. Mass assignment: status ignored on booking create
    // ---------------------------------------------------------------

    public function test_booking_create_ignores_status_from_client(): void
    {
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->post('/tenant/booking', [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'rental_type' => 'monthly',
            'status' => 'approved',
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test No 1',
        ]);

        $booking = Booking::where('user_id', $tenant->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals('pending', $booking->status);
    }

    // ---------------------------------------------------------------
    // 9. Throttle returns 429 on rapid requests
    // ---------------------------------------------------------------

    public function test_throttle_returns_429_on_rapid_booking_creation(): void
    {
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        // Make 11 requests (limit is 10 per 60 min)
        for ($i = 0; $i < 11; $i++) {
            $kamarNew = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
            $response = $this->actingAs($tenant)->post('/tenant/booking', [
                'kos_id' => $kos->id,
                'kamar_id' => $kamarNew->id,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'rental_type' => 'monthly',
                'identity_number' => '3201234567890001',
                'phone' => '08123456789',
                'address' => 'Jl. Test No 1',
            ]);
        }

        // The 11th request should be throttled
        $response->assertStatus(429);
    }
}
