<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Suite khusus Phase G — Tenant Booking & Post-Booking Experience.
 *
 * Memverifikasi konsistensi pembatalan (G1), panduan langkah berikutnya (G2),
 * jembatan kontrak/tagihan (G3), kejelasan status selesai/kedaluwarsa (G4),
 * terminologi harga (G5), aksesibilitas (G6), timeline dibatalkan/ditolak (G7),
 * aksi detail eksplisit (G8), isolasi tenant, dan bebas N+1.
 */
class PhaseGBookingExperienceTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available']);
    }

    private function makeBooking(string $status, array $attrs = []): Booking
    {
        return Booking::factory()->create(array_merge([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => $status,
        ], $attrs));
    }

    // ---------------------------------------------------------------
    // G1 — Konsistensi pembatalan
    // ---------------------------------------------------------------

    public function test_tenant_can_cancel_own_approved_booking(): void
    {
        $booking = $this->makeBooking('approved');

        $this->actingAs($this->tenant)
            ->post(route('tenant.booking.cancel', $booking))
            ->assertRedirect(route('tenant.booking.index'));

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
    }

    public function test_tenant_can_cancel_own_pending_booking(): void
    {
        // UI menawarkan pembatalan untuk pending, dan server harus menerimanya (G1).
        $booking = $this->makeBooking('pending');

        $this->actingAs($this->tenant)
            ->post(route('tenant.booking.cancel', $booking))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
    }

    public function test_tenant_cannot_cancel_completed_booking(): void
    {
        $booking = $this->makeBooking('completed');

        $this->actingAs($this->tenant)
            ->post(route('tenant.booking.cancel', $booking))
            ->assertStatus(403);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'completed']);
    }

    public function test_tenant_cannot_cancel_expired_or_rejected_or_cancelled_booking(): void
    {
        foreach (['expired', 'rejected', 'cancelled'] as $status) {
            $booking = $this->makeBooking($status);

            $this->actingAs($this->tenant)
                ->post(route('tenant.booking.cancel', $booking))
                ->assertStatus(403);

            $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => $status]);
        }
    }

    public function test_tenant_cannot_cancel_someone_elses_booking(): void
    {
        $other = User::factory()->create(['role' => 'tenant']);
        $booking = Booking::factory()->create([
            'user_id' => $other->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);

        $this->actingAs($this->tenant)
            ->post(route('tenant.booking.cancel', $booking))
            ->assertStatus(403);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'approved']);
    }

    // ---------------------------------------------------------------
    // G1 — Aksi pembatalan tidak tersedia di UI untuk state invalid
    // ---------------------------------------------------------------

    public function test_cancel_action_is_not_offered_for_completed_booking(): void
    {
        $booking = $this->makeBooking('completed');

        $response = $this->actingAs($this->tenant)
            ->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertDontSeeText('Batalkan Booking');
    }

    public function test_cancel_action_is_not_offered_for_expired_booking(): void
    {
        $booking = $this->makeBooking('expired');

        $response = $this->actingAs($this->tenant)
            ->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertDontSeeText('Batalkan Booking');
    }

    public function test_cancel_action_is_not_offered_in_list_for_terminal_booking(): void
    {
        $this->makeBooking('completed');
        $this->makeBooking('expired');

        $response = $this->actingAs($this->tenant)
            ->get(route('tenant.booking.index'));

        $response->assertOk();
        $response->assertDontSeeText('Batalkan Booking');
    }

    // ---------------------------------------------------------------
    // G1 — Isolasi tenant
    // ---------------------------------------------------------------

    public function test_tenant_cannot_view_another_tenants_booking_detail(): void
    {
        $other = User::factory()->create(['role' => 'tenant']);
        $booking = Booking::factory()->create([
            'user_id' => $other->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);

        $this->actingAs($this->tenant)
            ->get(route('tenant.booking.show', $booking))
            ->assertStatus(403);
    }

    public function test_tenant_index_only_lists_own_bookings(): void
    {
        $other = User::factory()->create(['role' => 'tenant']);
        $mine = $this->makeBooking('approved');
        $theirs = Booking::factory()->create([
            'user_id' => $other->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.index'));

        $response->assertOk();
        $response->assertSeeText($mine->booking_code);
        $response->assertDontSeeText($theirs->booking_code);
    }

    // ---------------------------------------------------------------
    // G2 — Panduan langkah berikutnya
    // ---------------------------------------------------------------

    public function test_index_renders_next_action_guidance_for_approved_booking(): void
    {
        $this->makeBooking('approved');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.index'));

        $response->assertOk();
        $response->assertSeeText('minta pengelola memproses check-in');
    }

    public function test_detail_renders_next_action_guidance_for_expired_booking(): void
    {
        $booking = $this->makeBooking('expired');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertSeeText('Langkah Selanjutnya');
        $response->assertSeeText('berakhir tanpa check-in');
    }

    // ---------------------------------------------------------------
    // G4 — Kejelasan status selesai / expired
    // ---------------------------------------------------------------

    public function test_completed_booking_detail_explains_completed_state(): void
    {
        $booking = $this->makeBooking('completed');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertSeeText('terdaftar sebagai penghuni');
    }

    public function test_expired_booking_detail_explains_expired_state(): void
    {
        $booking = $this->makeBooking('expired');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertSeeText('kedaluwarsa');
    }

    // ---------------------------------------------------------------
    // G3 — Jembatan kontrak & tagihan
    // ---------------------------------------------------------------

    public function test_completed_booking_shows_contract_bridge(): void
    {
        $booking = $this->makeBooking('completed');

        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);

        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertSeeText('Kontrak & Tagihan');
        $response->assertSeeText('Nomor Kontrak');
        $response->assertSeeText($kontrak->contract_number);
    }

    public function test_completed_booking_shows_billing_bridge(): void
    {
        $booking = $this->makeBooking('completed');

        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);

        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertSeeText('Lihat Tagihan Terbaru');
        $response->assertSee(route('tenant.tagihan.show', $tagihan));
    }

    public function test_completed_booking_without_contract_or_bill_shows_no_bridge(): void
    {
        $booking = $this->makeBooking('completed');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertDontSeeText('Nomor Kontrak');
        $response->assertDontSeeText('Lihat Tagihan Terbaru');
    }

    // ---------------------------------------------------------------
    // G5 — Terminologi harga
    // ---------------------------------------------------------------

    public function test_index_price_is_labeled_rental_price_not_total(): void
    {
        $this->makeBooking('approved');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.index'));

        $response->assertOk();
        $response->assertSeeText('Harga Sewa');
        $response->assertDontSeeText('Total');
    }

    public function test_detail_price_is_labeled_rental_price_not_total(): void
    {
        $booking = $this->makeBooking('approved');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertSeeText('Harga sewa');
        $response->assertDontSeeText('Total harga sewa');
    }

    // ---------------------------------------------------------------
    // Empty states
    // ---------------------------------------------------------------

    public function test_empty_booking_index_shows_empty_state(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.index'));

        $response->assertOk();
        $response->assertSeeText('Belum ada booking');
        $response->assertSeeText('Buat Booking Sekarang');
    }

    // ---------------------------------------------------------------
    // G7 — Timeline dibatalkan / ditolak
    // ---------------------------------------------------------------

    public function test_cancelled_booking_detail_shows_distinct_terminal_state(): void
    {
        $booking = $this->makeBooking('cancelled');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        // Tidak boleh berpura-pura "Booking Dikonfirmasi" sebagai langkah progres.
        $response->assertSeeText('Booking dihentikan dan tidak dapat dilanjutkan');
    }

    public function test_rejected_booking_detail_shows_distinct_terminal_state(): void
    {
        $booking = $this->makeBooking('rejected');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertSeeText('Booking tidak dilanjutkan oleh pengelola kos');
    }

    // ---------------------------------------------------------------
    // G8 — Aksi detail eksplisit
    // ---------------------------------------------------------------

    public function test_index_card_offers_explicit_detail_action(): void
    {
        $booking = $this->makeBooking('approved');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.index'));

        $response->assertOk();
        $response->assertSee(route('tenant.booking.show', $booking));
        $response->assertSeeText('Detail');
    }

    // ---------------------------------------------------------------
    // G6 — Semantik aksesibilitas
    // ---------------------------------------------------------------

    public function test_cancel_action_dialog_has_accessible_semantics(): void
    {
        $this->makeBooking('approved');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.index'));

        $response->assertOk();
        $response->assertSee('aria-haspopup="dialog"', false);
        $response->assertSee('role="dialog"', false);
        $response->assertSee('aria-modal="true"', false);
    }

    // ---------------------------------------------------------------
    // N+1 — Eager loading di index
    // ---------------------------------------------------------------

    public function test_booking_index_does_not_hit_n_plus_one_for_kos_and_kamar(): void
    {
        for ($i = 0; $i < 5; $i++) {
            Booking::factory()->create([
                'user_id' => $this->tenant->id,
                'kos_id' => $this->kos->id,
                'kamar_id' => $this->kamar->id,
                'status' => 'approved',
            ]);
        }

        \DB::flushQueryLog();
        \DB::enableQueryLog();

        $this->actingAs($this->tenant)->get(route('tenant.booking.index'))->assertOk();

        $queries = collect(\DB::getQueryLog());

        // Eager loading harus menggabungkan kos & kamar menjadi satu query "where id in (...)"
        // per tabel, bukan 1 query per baris booking (N+1).
        $kosQueries = $queries->filter(fn ($q) => str_contains($q['query'], 'from "kos"'));
        $kamarQueries = $queries->filter(fn ($q) => str_contains($q['query'], 'from "kamar"'));

        $this->assertLessThanOrEqual(2, $kosQueries->count(), 'Harusnya max 1 query kos untuk eager load.');
        $this->assertLessThanOrEqual(2, $kamarQueries->count(), 'Harusnya max 1 query kamar untuk eager load.');
    }
}
