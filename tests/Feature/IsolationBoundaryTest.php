<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IsolationBoundaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function makeStay(User $owner, ?User $tenant = null): array
    {
        $tenant ??= User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        return [$tenant, $kos, $kamar, $penghuni];
    }

    private function makeTagihan(Penghuni $penghuni, Kos $kos, Kamar $kamar): Tagihan
    {
        return Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => Kontrak::factory()->create([
                'penghuni_id' => $penghuni->id,
                'kos_id' => $kos->id,
                'kamar_id' => $kamar->id,
            ])->id,
            'kamar_id' => $kamar->id,
        ]);
    }

    private function makeBooking(User $tenant, Kos $kos, Kamar $kamar): Booking
    {
        return Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
        ]);
    }

    public function test_tenant_a_cannot_view_tenant_b_financial_data(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenantA, $kosA, $kamarA, $penghuniA] = $this->makeStay($owner);
        $tagihanA = $this->makeTagihan($penghuniA, $kosA, $kamarA);

        $tenantB = User::factory()->create(['role' => 'tenant']);

        $show = $this->actingAs($tenantB)->get(route('tenant.tagihan.show', $tagihanA));
        $show->assertForbidden();

        $index = $this->actingAs($tenantB)->get(route('tenant.tagihan.index'));
        $index->assertOk();
        $index->assertDontSee($tagihanA->bill_number);
    }

    public function test_tenant_a_cannot_cancel_tenant_b_booking(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenantA, $kosA, $kamarA] = $this->makeStay($owner);
        $bookingA = $this->makeBooking($tenantA, $kosA, $kamarA);
        $bookingA->update(['status' => 'approved']);

        $tenantB = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($tenantB)->post(route('tenant.booking.cancel', $bookingA));

        $response->assertForbidden();

        $this->assertDatabaseHas('bookings', ['id' => $bookingA->id, 'status' => 'approved']);
    }

    public function test_owner_a_cannot_access_kos_of_owner_b(): void
    {
        $ownerA = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);
        $kosB = Kos::factory()->create(['owner_id' => $ownerB->id]);

        $this->actingAs($ownerA)->get(route('owner.kos.show', $kosB))->assertForbidden();
        $this->actingAs($ownerA)->get(route('owner.kos.edit', $kosB))->assertForbidden();

        $update = $this->actingAs($ownerA)->put(route('owner.kos.update', $kosB), [
            'name' => 'Hacked',
            'address' => 'Jl. Hack',
            'phone' => '08111111111',
            'status' => 'active',
        ]);
        $update->assertForbidden();

        $this->actingAs($ownerA)->delete(route('owner.kos.destroy', $kosB))->assertForbidden();

        $this->assertDatabaseHas('kos', ['id' => $kosB->id, 'name' => $kosB->name]);
    }

    public function test_tenant_cannot_open_private_payment_proof_of_someone_else(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenantA, $kosA, $kamarA, $penghuniA] = $this->makeStay($owner);
        $tagihanA = $this->makeTagihan($penghuniA, $kosA, $kamarA);

        Storage::disk('local')->put('bukti-pembayaran/secret-proof.pdf', 'proof-content');

        $pembayaranA = Pembayaran::factory()->create([
            'tagihan_id' => $tagihanA->id,
            'penghuni_id' => $penghuniA->id,
            'verification_status' => 'pending',
            'proof_file' => 'bukti-pembayaran/secret-proof.pdf',
        ]);

        $stranger = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($stranger)
            ->get(route('tenant.pembayaran.show', $pembayaranA))
            ->assertForbidden();

        $this->actingAs($stranger)
            ->get(route('pembayaran.proof', $pembayaranA))
            ->assertForbidden();

        $allowed = $this->actingAs($tenantA)->get(route('pembayaran.proof', $pembayaranA));
        $allowed->assertOk();
    }

    public function test_guest_cannot_open_payment_proof(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenantA, $kosA, $kamarA, $penghuniA] = $this->makeStay($owner);
        $tagihanA = $this->makeTagihan($penghuniA, $kosA, $kamarA);

        $pembayaranA = Pembayaran::factory()->create([
            'tagihan_id' => $tagihanA->id,
            'penghuni_id' => $penghuniA->id,
            'verification_status' => 'pending',
            'proof_file' => 'bukti-pembayaran/secret-proof.pdf',
        ]);

        $this->get(route('pembayaran.proof', $pembayaranA))->assertRedirect(route('login'));
    }

    public function test_tenant_cannot_access_any_admin_surface(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $urls = [
            route('admin.kamar.index'),
            route('admin.pembayaran.index'),
            route('owner.kos.index'),
            route('super-admin.users.index'),
            route('super-admin.audit-log.index'),
        ];

        foreach ($urls as $url) {
            $this->actingAs($tenant)->get($url)->assertForbidden();
        }
    }

    public function test_admin_is_scoped_to_assigned_kos_only(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenantA, $kosA, $kamarA, $penghuniA] = $this->makeStay($owner);
        [$tenantB, $kosB, $kamarB, $penghuniB] = $this->makeStay($owner);

        $tagihanA = $this->makeTagihan($penghuniA, $kosA, $kamarA);
        $tagihanB = $this->makeTagihan($penghuniB, $kosB, $kamarB);

        $bookingA = $this->makeBooking($tenantA, $kosA, $kamarA);
        $bookingB = $this->makeBooking($tenantB, $kosB, $kamarB);

        $pembayaranB = Pembayaran::factory()->create([
            'tagihan_id' => $tagihanB->id,
            'penghuni_id' => $penghuniB->id,
            'verification_status' => 'pending',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignedKos()->attach($kosA->id);

        $this->actingAs($admin)->get(route('admin.kamar.show', $kamarA))->assertOk();
        $this->actingAs($admin)->get(route('admin.tagihan.show', $tagihanA))->assertOk();
        $this->actingAs($admin)->get(route('admin.booking.show', $bookingA))->assertOk();

        $index = $this->actingAs($admin)->get(route('admin.tagihan.index'));
        $index->assertOk();
        $index->assertSee($tagihanA->bill_number);
        $index->assertDontSee($tagihanB->bill_number);

        $this->actingAs($admin)->get(route('admin.kamar.show', $kamarB))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.tagihan.show', $tagihanB))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.booking.show', $bookingB))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.pembayaran.verify', $pembayaranB))->assertForbidden();

        $this->assertDatabaseHas('pembayarans', [
            'id' => $pembayaranB->id,
            'verification_status' => 'pending',
        ]);
    }
}
