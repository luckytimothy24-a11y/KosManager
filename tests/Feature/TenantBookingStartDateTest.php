<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use App\Support\StatusLabels;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Tanggal mulai booking = input tenant.
 * Default UI: monthly → bulan depan; weekly → +7 hari.
 * Server SELALU memakai start_date tenant setelah validasi.
 */
class TenantBookingStartDateTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::factory()->create(['role' => 'owner']);
        $this->kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'monthly_price' => 1500000,
            'weekly_price' => 400000,
            'daily_price' => 100000,
        ]);
    }

    private function storeBooking(array $overrides = [])
    {
        return $this->actingAs($this->tenant)->post(route('tenant.booking.store'), array_merge([
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'rental_type' => 'monthly',
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test No 1',
        ], $overrides));
    }

    // ── Default form (UI helper) ─────────────────────────────

    public function test_monthly_default_start_date_is_next_month(): void
    {
        $today = Carbon::today();
        $expected = $today->copy()->addMonthNoOverflow()->toDateString();

        $this->assertSame($expected, StatusLabels::defaultBookingStartDate('monthly'));
    }

    public function test_weekly_default_start_date_is_plus_7_days(): void
    {
        $expected = Carbon::today()->addDays(7)->toDateString();

        $this->assertSame($expected, StatusLabels::defaultBookingStartDate('weekly'));
    }

    public function test_monthly_end_date_default_follows_start(): void
    {
        $start = '2026-11-15';
        $this->assertSame('2026-12-15', StatusLabels::defaultBookingEndDate($start, 'monthly'));
    }

    public function test_weekly_end_date_default_follows_start(): void
    {
        $start = '2026-10-09';
        $this->assertSame('2026-10-16', StatusLabels::defaultBookingEndDate($start, 'weekly'));
    }

    public function test_monthly_edge_case_month_end(): void
    {
        // 31 Jan → default Februari (no overflow)
        $this->assertSame('2026-02-28', StatusLabels::defaultBookingEndDate('2026-01-31', 'monthly'));
        // 31 Okt → 30 Nov? addMonthNoOverflow: 31 Okt → 30 Nov
        $this->assertSame('2026-11-30', StatusLabels::defaultBookingEndDate('2026-10-31', 'monthly'));
    }

    public function test_create_form_shows_monthly_default_start(): void
    {
        $response = $this->actingAs($this->tenant)
            ->get(route('tenant.booking.create', ['kos_id' => $this->kos->id]));

        $response->assertOk();
        $expected = Carbon::today()->addMonthNoOverflow()->toDateString();
        $response->assertSee($expected, false);
        // Field editable, bukan hidden
        $response->assertSee('name="start_date"', false);
        $this->assertStringNotContainsString('name="start_date" type="hidden"', $response->getContent());
        // Anti flash hitam: background eksplisit pada date input
        $response->assertSee('bg-white', false);
        $response->assertSee('dark:bg-slate-700', false);
    }

    public function test_date_inputs_have_explicit_background_for_picker(): void
    {
        $response = $this->actingAs($this->tenant)
            ->get(route('tenant.booking.create', ['kos_id' => $this->kos->id]));

        $response->assertOk();
        $content = $response->getContent();

        // Kedua input date punya bg eksplisit (anti flash)
        $this->assertSame(2, substr_count($content, 'id="start-date"') + substr_count($content, 'id="end-date"'));
        $this->assertStringContainsString('id="start-date"', $content);
        $this->assertStringContainsString('bg-white dark:bg-slate-700', $content);
    }

    public function test_app_css_has_color_scheme_for_date_inputs(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString("input[type='date']", $css);
        $this->assertStringContainsString('color-scheme: light', $css);
        $this->assertStringContainsString('color-scheme: dark', $css);
        $this->assertStringContainsString('calendar-picker-indicator', $css);
    }

    public function test_create_form_shows_weekly_default_start(): void
    {
        $response = $this->actingAs($this->tenant)
            ->get(route('tenant.booking.create', ['kos_id' => $this->kos->id, 'rental_type' => 'weekly']));

        $response->assertOk();
        $expected = Carbon::today()->addDays(7)->toDateString();
        $response->assertSee($expected, false);
    }

    public function test_end_date_has_min_equals_start_date(): void
    {
        $start = Carbon::today()->addMonthNoOverflow()->toDateString();

        $response = $this->actingAs($this->tenant)
            ->get(route('tenant.booking.create', ['kos_id' => $this->kos->id]));

        $response->assertOk();
        $response->assertSee('id="end-date"', false);
        $response->assertSee('min="'.$start.'"', false);
    }

    public function test_monthly_end_default_follows_selected_start_on_form(): void
    {
        $chosenStart = '2026-11-30';
        $expectedEnd = '2026-12-30'; // +1 bulan no overflow (30 Nov → 30 Des)

        $response = $this->actingAs($this->tenant)
            ->get(route('tenant.booking.create', [
                'kos_id' => $this->kos->id,
                'rental_type' => 'monthly',
                'start_date' => $chosenStart,
            ]));

        $response->assertOk();
        $response->assertSee('value="'.$chosenStart.'"', false);
        $response->assertSee('value="'.$expectedEnd.'"', false);
        $response->assertSee('min="'.$chosenStart.'"', false);
    }

    public function test_weekly_end_default_follows_selected_start_on_form(): void
    {
        $chosenStart = '2026-10-09';
        $expectedEnd = '2026-10-16'; // +7 hari

        $response = $this->actingAs($this->tenant)
            ->get(route('tenant.booking.create', [
                'kos_id' => $this->kos->id,
                'rental_type' => 'weekly',
                'start_date' => $chosenStart,
            ]));

        $response->assertOk();
        $response->assertSee('value="'.$chosenStart.'"', false);
        $response->assertSee('value="'.$expectedEnd.'"', false);
        $response->assertSee('min="'.$chosenStart.'"', false);
    }

    public function test_end_date_auto_follow_logic_applies_to_all_kos(): void
    {
        // Kos kedua — form tetap sama (shared), default end mengikuti start
        $owner2 = User::factory()->create(['role' => 'owner']);
        $kos2 = Kos::factory()->create(['owner_id' => $owner2->id, 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos2->id, 'status' => 'available']);

        $start = '2026-11-15';
        $expectedEnd = '2026-12-15';

        foreach ([$this->kos, $kos2] as $kos) {
            $response = $this->actingAs($this->tenant)
                ->get(route('tenant.booking.create', [
                    'kos_id' => $kos->id,
                    'rental_type' => 'monthly',
                    'start_date' => $start,
                ]));

            $response->assertOk();
            $response->assertSee('value="'.$expectedEnd.'"', false);
            $response->assertSee('min="'.$start.'"', false);
        }
    }

    public function test_server_rejects_end_date_before_start_date(): void
    {
        $this->storeBooking([
            'start_date' => '2026-11-30',
            'end_date' => '2026-11-02', // sebelum start
            'rental_type' => 'monthly',
        ])->assertSessionHasErrors('end_date');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_server_accepts_end_date_following_start_date(): void
    {
        $start = '2026-11-30';
        $end = '2026-12-31'; // mengikuti monthly

        $this->storeBooking([
            'start_date' => $start,
            'end_date' => $end,
            'rental_type' => 'monthly',
        ])->assertRedirect();

        $booking = Booking::where('user_id', $this->tenant->id)->firstOrFail();
        $this->assertSame($start, $booking->start_date->toDateString());
        $this->assertSame($end, $booking->end_date->toDateString());
    }

    // ── Tenant override = source of truth ────────────────────

    public function test_tenant_selected_start_date_is_saved(): void
    {
        $chosen = Carbon::today()->addDays(20)->toDateString();

        $this->storeBooking([
            'start_date' => $chosen,
            'end_date' => Carbon::parse($chosen)->addMonthNoOverflow()->toDateString(),
            'rental_type' => 'monthly',
        ])->assertRedirect();

        $booking = Booking::where('user_id', $this->tenant->id)->firstOrFail();

        $this->assertSame($chosen, $booking->start_date->toDateString());
        // Bukan default bulan depan
        $this->assertNotSame(
            Carbon::today()->addMonthNoOverflow()->toDateString(),
            $booking->start_date->toDateString()
        );
    }

    public function test_weekly_tenant_override_start_date_saved(): void
    {
        // Default weekly = +7; tenant memilih +20
        $chosen = Carbon::today()->addDays(20)->toDateString();

        $this->storeBooking([
            'start_date' => $chosen,
            'end_date' => Carbon::parse($chosen)->addDays(7)->toDateString(),
            'rental_type' => 'weekly',
        ])->assertRedirect();

        $booking = Booking::where('user_id', $this->tenant->id)->firstOrFail();

        $this->assertSame($chosen, $booking->start_date->toDateString());
        $this->assertSame('weekly', $booking->rental_type);
        $this->assertNotSame(Carbon::today()->addDays(7)->toDateString(), $booking->start_date->toDateString());
    }

    public function test_api_tenant_override_start_date_saved(): void
    {
        $chosen = Carbon::today()->addDays(15)->toDateString();
        $token = $this->tenant->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => $chosen,
            'end_date' => Carbon::parse($chosen)->addMonthNoOverflow()->toDateString(),
            'rental_type' => 'monthly',
        ])->assertStatus(201);

        $booking = Booking::where('user_id', $this->tenant->id)->firstOrFail();
        $this->assertSame($chosen, $booking->start_date->toDateString());
        $this->assertEquals('pending', $booking->status);
    }

    // ── Validation server-side ───────────────────────────────

    public function test_invalid_start_date_is_rejected(): void
    {
        $this->storeBooking([
            'start_date' => 'not-a-date',
            'end_date' => now()->addMonth()->toDateString(),
        ])->assertSessionHasErrors('start_date');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_past_start_date_is_rejected(): void
    {
        $this->storeBooking([
            'start_date' => Carbon::today()->subDay()->toDateString(),
            'end_date' => Carbon::today()->addMonth()->toDateString(),
        ])->assertSessionHasErrors('start_date');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_end_date_before_start_date_is_rejected(): void
    {
        $this->storeBooking([
            'start_date' => Carbon::today()->addDays(10)->toDateString(),
            'end_date' => Carbon::today()->addDays(5)->toDateString(),
        ])->assertSessionHasErrors('end_date');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_overlap_on_selected_date_is_rejected(): void
    {
        // Existing approved booking 20 Okt – 30 Okt
        $existingStart = Carbon::today()->addDays(20);
        Booking::factory()->create([
            'user_id' => User::factory()->create(['role' => 'tenant'])->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
            'start_date' => $existingStart,
            'end_date' => $existingStart->copy()->addDays(10),
        ]);
        $this->kamar->update(['status' => 'booked']);

        // Tenant memilih tanggal overlap
        $this->storeBooking([
            'start_date' => $existingStart->copy()->addDays(2)->toDateString(),
            'end_date' => $existingStart->copy()->addDays(8)->toDateString(),
        ])->assertSessionHasErrors('kamar_id');

        $this->assertDatabaseCount('bookings', 1); // hanya existing
    }

    // ── Payment / status tidak berubah oleh start date ──────

    public function test_start_date_does_not_auto_confirm_booking(): void
    {
        $this->storeBooking([
            'start_date' => Carbon::today()->addDays(15)->toDateString(),
        ])->assertRedirect();

        $booking = Booking::where('user_id', $this->tenant->id)->firstOrFail();
        $this->assertEquals('pending', $booking->status);
        $this->assertDatabaseMissing('penghunis', ['user_id' => $this->tenant->id, 'status' => 'active']);
    }

    // ── Security: IDOR / manipulation ────────────────────────

    public function test_tenant_cannot_change_other_tenant_booking_start_date(): void
    {
        $other = User::factory()->create(['role' => 'tenant']);
        $otherBooking = Booking::factory()->create([
            'user_id' => $other->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending',
            'start_date' => Carbon::today()->addDays(10),
            'end_date' => Carbon::today()->addMonths(2),
        ]);

        $this->actingAs($this->tenant)
            ->post(route('tenant.booking.cancel', $otherBooking))
            ->assertForbidden();

        $this->assertSame('pending', $otherBooking->fresh()->status);
    }

    public function test_start_date_cannot_manipulate_payment_or_amount(): void
    {
        $this->storeBooking([
            'start_date' => Carbon::today()->addDays(10)->toDateString(),
            'end_date' => Carbon::today()->addMonths(1)->toDateString(),
            'rental_type' => 'monthly',
        ])->assertRedirect();

        $booking = Booking::where('user_id', $this->tenant->id)->firstOrFail();
        // Price tetap dari server (kamar.monthly_price)
        $this->assertEquals(1500000, (float) $booking->price);
        $this->assertEquals('pending', $booking->status);
    }
}
