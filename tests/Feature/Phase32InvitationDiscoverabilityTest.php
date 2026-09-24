<?php
namespace Tests\Feature;
use App\Models\Booking;
use App\Models\BookingOccupant;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class Phase32InvitationDiscoverabilityTest extends TestCase {
    use RefreshDatabase;
    private User $owner; private User $primary; private Kos $kos; private Kamar $kamar;
    protected function setUp(): void { parent::setUp();
        $this->owner=User::factory()->create(['role'=>'owner']);
        $this->primary=User::factory()->create(['role'=>'tenant','email'=>'primary@example.com']);
        $this->kos=Kos::factory()->create(['owner_id'=>$this->owner->id,'status'=>'active']);
        $this->kamar=Kamar::factory()->create(['kos_id'=>$this->kos->id,'status'=>'available','capacity'=>3,'billing_mode'=>'shared','monthly_price'=>1200000,'daily_price'=>50000,'weekly_price'=>350000]);
    }
    private function book(int $req=3, User $u=null): Booking { $u??=$this->primary; $payload=['kamar_id'=>$this->kamar->id,'start_date'=>now()->addDays(5)->format('Y-m-d'),'end_date'=>now()->addMonths(2)->format('Y-m-d'),'rental_type'=>'monthly','billing_mode'=>'shared','requested_occupants'=>$req]; $r=app(\App\Services\BookingService::class)->create($payload,$u->id); $this->assertTrue($r['ok']); return $r['booking']; }
    private function invite(Booking $b,string $email, User $actor=null): array { $actor??=$this->primary; $res=$this->actingAs($actor)->postJson(route('tenant.booking.invite',$b),['email'=>$email]); return [$res,$res->json('data.invite_token')]; }

    public function test_tenant_can_see_own_pending_invitation_index(): void {
        $b=$this->book(3); [$res,$token]=$this->invite($b,'friend@example.com');
        $friend=User::factory()->create(['role'=>'tenant','email'=>'friend@example.com']);
        $this->actingAs($friend)->get(route('tenant.invitations.index'))->assertOk()->assertSee('Undangan yang perlu ditanggapi')->assertSee($this->kos->name);
    }
    public function test_tenant_cannot_see_another_tenant_invitation(): void {
        $b=$this->book(3); [$res,$token]=$this->invite($b,'friend@example.com');
        $other=User::factory()->create(['role'=>'tenant','email'=>'other@example.com']);
        $this->actingAs($other)->get(route('tenant.invitations.index'))->assertOk()->assertDontSee($this->kos->name)->assertSee('Belum Ada Undangan');
    }
    public function test_valid_invitation_can_be_opened_via_token(): void {
        $b=$this->book(3); [$res,$token]=$this->invite($b,'friend@example.com');
        $friend=User::factory()->create(['role'=>'tenant','email'=>'friend@example.com']);
        $this->actingAs($friend)->get(route('tenant.invitations.show',$token))->assertOk();
    }
    public function test_invalid_token_rejected(): void {
        $u=User::factory()->create(['role'=>'tenant']); $this->actingAs($u)->get(route('tenant.invitations.show',str_repeat('a',64)))->assertStatus(404);
    }
    public function test_expired_invitation_rejected(): void {
        $b=$this->book(3); [$res,$token]=$this->invite($b,'friend@example.com');
        $occ=BookingOccupant::where('booking_id',$b->id)->where('email','friend@example.com')->first(); $occ->update(['invite_expires_at'=>now()->subDay()]);
        $friend=User::factory()->create(['role'=>'tenant','email'=>'friend@example.com']);
        $this->actingAs($friend)->get(route('tenant.invitations.show',$token))->assertStatus(410);
        // token nulled after expiry — fresh token path should also 410 or 404
        $b2=$this->kamar=Kamar::factory()->create(['kos_id'=>$this->kos->id,'status'=>'available','capacity'=>3,'billing_mode'=>'shared']);
        $b2=$this->book(3); [$r2,$t2]=$this->invite($b2,'friend@example.com');
        $occ2=BookingOccupant::where('booking_id',$b2->id)->where('email','friend@example.com')->first(); $occ2->update(['invite_expires_at'=>now()->subDay()]);
        $this->actingAs($friend)->postJson(route('tenant.invitations.accept',$t2))->assertStatus(410);
    }
    public function test_accept_via_id_works(): void {
        $b=$this->book(3); [$res,$token]=$this->invite($b,'friend@example.com');
        $friend=User::factory()->create(['role'=>'tenant','email'=>'friend@example.com']);
        $occ=BookingOccupant::where('booking_id',$b->id)->where('email','friend@example.com')->first();
        $this->actingAs($friend)->post(route('tenant.invitations.acceptId',$occ))->assertRedirect(route('tenant.invitations.index'));
        $this->assertSame('accepted',$occ->fresh()->status);
    }
    public function test_decline_via_id_works(): void {
        $b=$this->book(3); [$res,$token]=$this->invite($b,'friend@example.com');
        $friend=User::factory()->create(['role'=>'tenant','email'=>'friend@example.com']);
        $occ=BookingOccupant::where('booking_id',$b->id)->where('email','friend@example.com')->first();
        $this->actingAs($friend)->post(route('tenant.invitations.declineId',$occ))->assertRedirect(route('tenant.invitations.index'));
        $this->assertSame('declined',$occ->fresh()->status);
    }
    public function test_accepted_no_longer_pending(): void {
        $b=$this->book(3); [$res,$token]=$this->invite($b,'friend@example.com');
        $friend=User::factory()->create(['role'=>'tenant','email'=>'friend@example.com']);
        $occ=BookingOccupant::where('booking_id',$b->id)->where('email','friend@example.com')->first();
        $this->actingAs($friend)->postJson(route('tenant.invitations.accept',$token))->assertStatus(200);
        $this->actingAs($friend)->get(route('tenant.invitations.index'))->assertOk()->assertDontSee('Undangan yang perlu ditanggapi')->assertSee('Diterima');
    }
    public function test_declined_no_longer_pending(): void {
        $b=$this->book(3); [$res,$token]=$this->invite($b,'friend@example.com');
        $friend=User::factory()->create(['role'=>'tenant','email'=>'friend@example.com']);
        $occ=BookingOccupant::where('booking_id',$b->id)->where('email','friend@example.com')->first();
        $this->actingAs($friend)->postJson(route('tenant.invitations.decline',$token))->assertStatus(200);
        $this->actingAs($friend)->get(route('tenant.invitations.index'))->assertOk()->assertSee('Ditolak');
    }
    public function test_dashboard_entry_appears_when_pending(): void {
        $b=$this->book(3); [$res,$token]=$this->invite($b,'friend@example.com');
        $friend=User::factory()->create(['role'=>'tenant','email'=>'friend@example.com']);
        $this->actingAs($friend)->get('/dashboard')->assertOk()->assertSee('Undangan Kos')->assertSee('Menunggu')->assertSee(route('tenant.invitations.index',[],false));
    }
    public function test_dashboard_empty_state_when_none(): void {
        $u=User::factory()->create(['role'=>'tenant']); $this->actingAs($u)->get('/dashboard')->assertOk()->assertSee('Undangan Kos')->assertSee('Belum ada undangan');
    }
    public function test_no_leakage_cross_user_accept(): void {
        $b=$this->book(3); [$res,$token]=$this->invite($b,'friend@example.com');
        $other=User::factory()->create(['role'=>'tenant','email'=>'other@example.com']);
        $occ=BookingOccupant::where('booking_id',$b->id)->where('email','friend@example.com')->first();
        $this->actingAs($other)->post(route('tenant.invitations.acceptId',$occ))->assertStatus(403);
        $this->actingAs($other)->postJson(route('tenant.invitations.accept',$token))->assertStatus(403);
    }
    public function test_dashboard_and_index_badge_count_matches(): void {
        $b=$this->book(3); $this->invite($b,'friend@example.com');
        $this->kamar=Kamar::factory()->create(['kos_id'=>$this->kos->id,'status'=>'available','capacity'=>3,'billing_mode'=>'shared','monthly_price'=>1200000,'daily_price'=>50000,'weekly_price'=>350000]);
        $b2=$this->book(3); $this->invite($b2,'friend@example.com');
        $friend=User::factory()->create(['role'=>'tenant','email'=>'friend@example.com']);
        $res=$this->actingAs($friend)->get('/dashboard')->assertOk();
        $res->assertSee('2 Menunggu');
        $this->actingAs($friend)->get(route('tenant.invitations.index'))->assertOk()->assertSee('2 Menunggu');
    }
}
