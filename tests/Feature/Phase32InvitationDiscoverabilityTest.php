<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingOccupant;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase32InvitationDiscoverabilityTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $primary;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->primary = User::factory()->create(['role' => 'tenant', 'email' => 'primary@example.com']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available', 'capacity' => 1, 'billing_mode' => 'personal', 'monthly_price' => 1200000, 'daily_price' => 50000, 'weekly_price' => 350000]);
    }

    private function book(?User $u = null): Booking
    {
        $u ??= $this->primary;
        $payload = ['kamar_id' => $this->kamar->id, 'start_date' => now()->addDays(5)->format('Y-m-d'), 'end_date' => now()->addMonths(2)->format('Y-m-d'), 'rental_type' => 'monthly', 'billing_mode' => 'personal', 'requested_occupants' => 1];
        $r = app(BookingService::class)->create($payload, $u->id);
        $this->assertTrue($r['ok']);

        return $r['booking'];
    }

    public function test_primary_occupant_exists_on_booking(): void
    {
        $b = $this->book();
        $occ = BookingOccupant::where('booking_id', $b->id)->where('occupant_index', 1)->first();
        $this->assertNotNull($occ);
        $this->assertSame('accepted', $occ->status);
    }

    public function test_invitation_creation_disabled_returns_403(): void
    {
        $b = $this->book();
        $res = $this->actingAs($this->primary)->postJson(route('tenant.booking.invite', $b), ['email' => 'friend@example.com']);
        $res->assertStatus(403);
    }

    public function test_invalid_token_rejected(): void
    {
        $u = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($u)->get(route('tenant.invitations.show', str_repeat('a', 64)))->assertStatus(404);
    }

    public function test_tenant_sees_empty_invitations_when_none(): void
    {
        $u = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($u)->get(route('tenant.invitations.index'))->assertOk()->assertSee('Belum Ada Undangan');
    }

    public function test_occupants_index_shows_primary(): void
    {
        $b = $this->book();
        $res = $this->actingAs($this->primary)->getJson(route('tenant.booking.occupants.index', $b));
        $res->assertStatus(200);
        $res->assertJsonCount(1, 'data');
        $res->assertJsonPath('data.0.occupant_index', 1);
        $res->assertJsonPath('data.0.status', 'accepted');
    }

    public function test_occupant_cancel_disabled(): void
    {
        $b = $this->book();
        $occ = BookingOccupant::where('booking_id', $b->id)->where('occupant_index', 1)->first();
        $res = $this->actingAs($this->primary)->deleteJson(route('tenant.booking.occupants.cancel', [$b, $occ]));
        $res->assertStatus(403);
    }

    public function test_primary_occupant_single_per_booking(): void
    {
        $b = $this->book();
        $count = BookingOccupant::where('booking_id', $b->id)->where('status', BookingOccupant::STATUS_ACCEPTED)->count();
        $this->assertSame(1, $count);
    }

    public function test_requested_occupants_hardcoded_to_one(): void
    {
        $b = $this->book();
        $this->assertSame(1, (int) $b->requested_occupants);
    }

    public function test_billing_mode_hardcoded_to_personal(): void
    {
        $b = $this->book();
        $this->assertSame('personal', $b->billing_mode);
    }

    public function test_primary_cannot_be_cancelled(): void
    {
        $b = $this->book();
        $occ = BookingOccupant::where('booking_id', $b->id)->where('occupant_index', 1)->first();
        $res = $this->actingAs($this->primary)->deleteJson(route('tenant.booking.occupants.cancel', [$b, $occ]));
        $res->assertStatus(403);
    }

    public function test_multi_occupant_feature_disabled(): void
    {
        $b = $this->book();
        $res = $this->actingAs($this->primary)->postJson(route('tenant.booking.invite', $b), ['email' => 'secondary@example.com']);
        $res->assertStatus(403);
        
        $secondary_count = BookingOccupant::where('booking_id', $b->id)->where('occupant_index', '>', 1)->count();
        $this->assertSame(0, $secondary_count);
    }

    public function test_occupancy_remains_zero_before_checkin(): void
    {
        $b = $this->book();
        $this->assertSame(0, $this->kamar->fresh()->occupancy());
    }
}
