<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Notification;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class IdentityConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private User $otherTenant;

    private Kos $kos;

    private Kamar $kamar;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant', 'phone' => '081234567890']);
        $this->otherTenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'booked']);
        $this->booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);
    }

    public function test_tenant_can_view_identity_confirmation_page(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.checkin.identity', $this->booking));

        $response->assertOk();
        $response->assertSee('Lengkapi Data Diri');
        $response->assertSee($this->tenant->name);
        $response->assertSee($this->tenant->email);
        $response->assertSee($this->kos->name);
        $response->assertSee($this->kamar->room_number);
    }

    public function test_tenant_can_confirm_own_identity_with_data(): void
    {
        $response = $this->actingAs($this->tenant)->post(
            route('tenant.checkin.identity.confirm', $this->booking),
            [
                'identity_number' => '3374012345670001',
                'phone' => '08123456789',
                'address' => 'Jl. Merdeka No. 1, Yogyakarta',
            ]
        );

        $response->assertRedirect(route('tenant.checkin.identity', $this->booking));
        // New flow: tenant submit -> pending owner review, NOT self-confirmed
        $fresh = $this->booking->fresh();
        $this->assertFalse($fresh->identityConfirmed());
        $this->assertNull($fresh->identity_confirmed_at);
        $this->assertTrue($fresh->identityPending());
        $this->assertEquals('pending', $fresh->identityStatus());
        $this->assertEquals('3374012345670001', $fresh->identity_number);
        $this->assertEquals('08123456789', $fresh->phone);
        $this->assertEquals('Jl. Merdeka No. 1, Yogyakarta', $fresh->address);
        $this->assertNotNull($fresh->identity_data_submitted_at);
    }

    public function test_confirmation_requires_identity_number(): void
    {
        $response = $this->actingAs($this->tenant)->post(
            route('tenant.checkin.identity.confirm', $this->booking),
            [
                'phone' => '08123456789',
                'address' => 'Jl. Test',
            ]
        );

        $response->assertSessionHasErrors('identity_number');
        $this->assertFalse($this->booking->fresh()->identityConfirmed());
    }

    public function test_confirmation_requires_phone(): void
    {
        $response = $this->actingAs($this->tenant)->post(
            route('tenant.checkin.identity.confirm', $this->booking),
            [
                'identity_number' => '3374012345670001',
                'address' => 'Jl. Test',
            ]
        );

        $response->assertSessionHasErrors('phone');
        $this->assertFalse($this->booking->fresh()->identityConfirmed());
    }

    public function test_confirmation_requires_address(): void
    {
        $response = $this->actingAs($this->tenant)->post(
            route('tenant.checkin.identity.confirm', $this->booking),
            [
                'identity_number' => '3374012345670001',
                'phone' => '08123456789',
            ]
        );

        $response->assertSessionHasErrors('address');
        $this->assertFalse($this->booking->fresh()->identityConfirmed());
    }

    public function test_confirming_twice_is_rejected(): void
    {
        $this->booking->update(['identity_confirmed_at' => now()]);

        $response = $this->actingAs($this->tenant)->post(
            route('tenant.checkin.identity.confirm', $this->booking),
            [
                'identity_number' => '3374012345670001',
                'phone' => '08123456789',
                'address' => 'Jl. Test',
            ]
        );

        $response->assertStatus(400);
    }

    public function test_tenant_cannot_view_other_tenants_identity_confirmation(): void
    {
        $response = $this->actingAs($this->otherTenant)->get(route('tenant.checkin.identity', $this->booking));

        $response->assertForbidden();
    }

    public function test_tenant_cannot_confirm_other_tenants_identity(): void
    {
        $response = $this->actingAs($this->otherTenant)->post(
            route('tenant.checkin.identity.confirm', $this->booking),
            [
                'identity_number' => '3374012345670001',
                'phone' => '08123456789',
                'address' => 'Jl. Test',
            ]
        );

        $response->assertForbidden();
        $this->assertFalse($this->booking->fresh()->identityConfirmed());
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_owner_cannot_use_tenant_confirmation_route(): void
    {
        $response = $this->actingAs($this->owner)->get(route('tenant.checkin.identity', $this->booking));

        $response->assertForbidden();
    }

    public function test_identity_page_unavailable_for_pending_booking(): void
    {
        $this->booking->update(['status' => 'pending']);

        $response = $this->actingAs($this->tenant)->get(route('tenant.checkin.identity', $this->booking));

        $response->assertStatus(404);
    }

    public function test_confirmation_notifies_kos_owner(): void
    {
        $this->actingAs($this->tenant)->post(
            route('tenant.checkin.identity.confirm', $this->booking),
            [
                'identity_number' => '3374012345670001',
                'phone' => '08123456789',
                'address' => 'Jl. Test',
            ]
        );

        $notification = Notification::where('user_id', $this->owner->id)
            ->where('type', 'identity')
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame('Pengajuan Check-In Baru', $notification->title);
        $this->assertStringContainsString($this->tenant->name, $notification->message);
    }

    public function test_owner_can_view_confirmed_status_on_booking(): void
    {
        $this->booking->update(['identity_confirmed_at' => now(), 'identity_confirmed_by' => $this->owner->id, 'identity_data_submitted_at' => now(), 'identity_number' => '3374012345670001', 'phone' => '08123456789', 'address' => 'Jl. Test']);

        $response = $this->actingAs($this->owner)->get(route('owner.booking.show', $this->booking));

        $response->assertOk();
        $response->assertSee('Check-In Dikonfirmasi');
        $response->assertDontSee('Menunggu Konfirmasi Owner');
        $response->assertDontSee('Belum Mengisi');
    }

    public function test_owner_booking_page_shows_pending_identity_status(): void
    {
        $this->booking->update(['identity_data_submitted_at' => now(), 'identity_number' => '3374012345670001', 'phone' => '08123456789', 'address' => 'Jl. Test']);

        $response = $this->actingAs($this->owner)->get(route('owner.booking.show', $this->booking));

        $response->assertOk();
        $response->assertSee('Menunggu Konfirmasi Owner');
        $response->assertDontSee('Check-In Dikonfirmasi');
    }

    public function test_owner_checkin_blocked_when_identity_not_confirmed(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.checkin.process', $this->booking));

        $response->assertStatus(400);
        $this->assertDatabaseHas('bookings', ['id' => $this->booking->id, 'status' => 'approved']);
        $this->assertDatabaseCount('penghunis', 0);
    }

    public function test_owner_checkin_blocked_when_identity_data_not_submitted(): void
    {
        $this->booking->update(['identity_confirmed_at' => now()]);

        $response = $this->actingAs($this->owner)->post(route('owner.checkin.process', $this->booking));

        $response->assertStatus(400);
        $this->assertDatabaseHas('bookings', ['id' => $this->booking->id, 'status' => 'approved']);
        $this->assertDatabaseCount('penghunis', 0);
    }

    public function test_owner_checkin_proceeds_after_identity_confirmation(): void
    {
        $this->booking->update([
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
            'identity_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.checkin.process', $this->booking));

        $response->assertRedirect(route('owner.booking.index'));
        $this->assertDatabaseHas('bookings', ['id' => $this->booking->id, 'status' => 'completed']);
        $this->assertDatabaseHas('penghunis', [
            'user_id' => $this->tenant->id,
            'kamar_id' => $this->kamar->id,
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'status' => 'active',
        ]);
    }

    public function test_owner_checkin_index_does_not_render_form_before_confirmation(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.checkin.index'));

        $response->assertOk();
        $response->assertSee('Belum Mengisi');
        $response->assertDontSee('Konfirmasi Check-In');
        $response->assertDontSee('Proses Check-In');
    }

    public function test_owner_checkin_index_renders_readonly_data_for_confirmed_booking(): void
    {
        $this->booking->update([
            'identity_number' => '3374012345670001',
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka',
            'identity_data_submitted_at' => now(),
            'identity_confirmed_at' => now(),
            'identity_confirmed_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.checkin.index'));

        $response->assertOk();
        $response->assertSee('Dikonfirmasi');
        // owner checkin pending -> masked KTP but still shows WA; for confirmed state masking also applied
        $response->assertDontSee('name="identity_number"');
        $response->assertSee('Proses Check-In');
    }

    public function test_owner_checkin_index_does_not_render_identity_form_fields(): void
    {
        $this->booking->update([
            'identity_number' => '3374012345670001',
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka',
            'identity_data_submitted_at' => now(),
            'identity_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.checkin.index'));

        $response->assertOk();
        $response->assertDontSee('name="identity_number"');
        $response->assertDontSee('name="phone"');
        $response->assertDontSee('name="address"');
    }

    public function test_identity_data_flows_to_penghuni_on_checkin(): void
    {
        $this->booking->update([
            'identity_number' => '3374012345670001',
            'phone' => '08987654321',
            'address' => 'Jl. Pahlawan No. 42, Semarang',
            'identity_data_submitted_at' => now(),
            'identity_confirmed_at' => now(),
        ]);

        $this->actingAs($this->owner)->post(route('owner.checkin.process', $this->booking));

        $penghuni = Penghuni::where('user_id', $this->tenant->id)->first();
        $this->assertNotNull($penghuni);
        $this->assertEquals('3374012345670001', $penghuni->identity_number);
        $this->assertEquals('08987654321', $penghuni->phone);
        $this->assertEquals('Jl. Pahlawan No. 42, Semarang', $penghuni->address);
    }
}
