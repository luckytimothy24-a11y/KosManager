<?php
namespace Tests\Feature;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class Phase33BookingCopyTest extends TestCase {
    use RefreshDatabase;
    public function test_booking_form_shows_human_readable_occupant_label(): void {
        $tenant = User::factory()->create(['role'=>'tenant']);
        $owner = User::factory()->create(['role'=>'owner']);
        $kos = Kos::factory()->create(['owner_id'=>$owner->id,'status'=>'active']);
        Kamar::factory()->create(['kos_id'=>$kos->id,'status'=>'available','capacity'=>3,'billing_mode'=>'shared','monthly_price'=>1000000]);
        $res = $this->actingAs($tenant)->get(route('tenant.booking.create',['kos_id'=>$kos->id]));
        $res->assertOk();
        $res->assertSee('Berapa orang yang akan tinggal?');
        $res->assertSee('Masukkan jumlah orang yang akan menempati kamar ini.');
        $res->assertSee('Kamar ini dapat dihuni hingga');
        $res->assertDontSee('Mode Tagihan');
        $res->assertSee('Pembayaran kamar');
        $res->assertSee('Saya tanggung sendiri');
        $res->assertSee('Dibagi dengan penghuni lain');
        $res->assertSee('Biaya kamar akan dibagi sesuai jumlah penghuni.');
        $res->assertSee('Total harga kamar');
        // Ensure visible help for multi-occupant exists (hidden but in HTML)
        $res->assertSee('Setelah booking dibuat, kamu dapat mengundang penghuni lain untuk bergabung.');
        // Backend values still present as input values (not as visible headings)
        $this->assertStringContainsString('name="requested_occupants"', $res->getContent());
        $this->assertStringContainsString('name="billing_mode"', $res->getContent());
    }
    public function test_booking_show_displays_human_copy(): void {
        $tenant = User::factory()->create(['role'=>'tenant']);
        $owner = User::factory()->create(['role'=>'owner']);
        $kos = Kos::factory()->create(['owner_id'=>$owner->id,'status'=>'active']);
        $kamar = Kamar::factory()->create(['kos_id'=>$kos->id,'status'=>'available','capacity'=>3,'billing_mode'=>'shared','monthly_price'=>1000000]);
        $booking = \App\Models\Booking::factory()->create(['user_id'=>$tenant->id,'kos_id'=>$kos->id,'kamar_id'=>$kamar->id,'status'=>'approved','requested_occupants'=>2,'billing_mode'=>'shared','start_date'=>now()->addDays(1),'end_date'=>now()->addMonths(1),'rental_type'=>'monthly','price'=>1000000]);
        $res = $this->actingAs($tenant)->get(route('tenant.booking.show',$booking));
        $res->assertOk();
        $res->assertSee('Jumlah penghuni');
        $res->assertSee('2 orang');
        $res->assertSee('Pembayaran');
        $res->assertSee('Dibagi dengan penghuni lain');
        $res->assertSee('Total harga kamar');
    }
    public function test_personal_booking_still_works(): void {
        $tenant = User::factory()->create(['role'=>'tenant']);
        $owner = User::factory()->create(['role'=>'owner']);
        $kos = Kos::factory()->create(['owner_id'=>$owner->id,'status'=>'active']);
        $kamar = Kamar::factory()->create(['kos_id'=>$kos->id,'status'=>'available','capacity'=>1,'billing_mode'=>'personal','monthly_price'=>1000000,'daily_price'=>50000,'weekly_price'=>300000]);
        $res = $this->actingAs($tenant)->post(route('tenant.booking.store'),[
            'kos_id'=>$kos->id,'kamar_id'=>$kamar->id,'start_date'=>now()->addDays(1)->format('Y-m-d'),'end_date'=>now()->addMonths(1)->format('Y-m-d'),'rental_type'=>'monthly','billing_mode'=>'personal','requested_occupants'=>1,
            'identity_number'=>'3201234567890001','phone'=>'08123456789','address'=>'Jl Test No 1'
        ]);
        $res->assertRedirect();
        $this->assertDatabaseHas('bookings',['user_id'=>$tenant->id,'kamar_id'=>$kamar->id,'billing_mode'=>'personal','requested_occupants'=>1]);
    }
}
