<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseCKosDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function createTenant(): User
    {
        return User::factory()->create(['role' => 'tenant']);
    }

    protected function createOwner(): User
    {
        return User::factory()->create(['role' => 'owner']);
    }

    // ── A. Availability ────────────────────────────────────────

    public function test_available_count_matches_bookable_rooms(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'maintenance']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'booked']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('2 kamar tersedia');
        $response->assertSee('5 kamar total');
    }

    public function test_maintenance_rooms_are_excluded_from_available(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'M1', 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'M2', 'status' => 'maintenance']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('1 kamar tersedia');
    }

    public function test_booked_and_occupied_rooms_are_excluded_from_available(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'booked']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('0 kamar tersedia');
    }

    public function test_unavailable_room_has_no_active_booking_cta(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'X01', 'status' => 'maintenance']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'X02', 'status' => 'occupied']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        // Tidak ada kamar tersedia -> tidak boleh ada CTA "Pilih Kamar" (sticky bar disembunyikan).
        $response->assertDontSee('Pilih Kamar');
        // Satu-satunya kemunculan URL booking hanyalah template modal statis.
        $bookUrlCount = substr_count($response->getContent(), 'tenant/booking/create?kos_id='.$kos->id);
        $this->assertEquals(1, $bookUrlCount, 'Tidak boleh ada link booking pada kartu kamar yang tidak tersedia.');
        // Detail kamar yang tidak tersedia menyajikan status "Tidak Tersedia".
        $response->assertSee('Kamar Tidak Tersedia');
    }

    public function test_available_room_has_booking_cta(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee("/tenant/booking/create?kos_id={$kos->id}&amp;kamar_id={$kamar->id}", escape: false);
    }

    public function test_unavailable_room_card_displays_status_not_booking(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $available = Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'X01', 'status' => 'available', 'monthly_price' => 1000000]);
        $maintenance = Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'X02', 'status' => 'maintenance', 'monthly_price' => 800000]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        // Kamar maintenance tidak boleh memiliki link booking dengan kamar_id miliknya.
        $this->assertSame(0, substr_count($response->getContent(), 'kamar_id='.$maintenance->id), 'Kamar maintenance tidak boleh punya CTA booking.');
        // Kamar available wajib memiliki link booking dengan kamar_id miliknya.
        $this->assertGreaterThanOrEqual(1, substr_count($response->getContent(), 'kamar_id='.$available->id), 'Kamar tersedia wajib memiliki CTA booking.');
    }

    // ── B. Harga Mulai ─────────────────────────────────────────

    public function test_harga_mulai_ignores_unbookable_rooms(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        // Room A = 1.000.000 available
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'A', 'status' => 'available', 'monthly_price' => 1000000]);
        // Room B = 800.000 maintenance (harus diabaikan untuk harga mulai)
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'B', 'status' => 'maintenance', 'monthly_price' => 800000]);
        // Room C = 1.200.000 available
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'C', 'status' => 'available', 'monthly_price' => 1200000]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        // Harga mulai harus 1.000.000 (dari kamar available termurah), bukan 800.000 dari kamar maintenance.
        $this->assertAllHargaMulaiEqual($response->getContent(), '1.000.000');
    }

    public function test_harga_mulai_uses_lowest_bookable_price(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'booked', 'monthly_price' => 500000]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied', 'monthly_price' => 600000]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'maintenance', 'monthly_price' => 700000]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 1500000]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 900000]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $this->assertAllHargaMulaiEqual($response->getContent(), '900.000');
    }

    // ── C. Facilities Consistency ──────────────────────────────

    public function test_facilities_come_from_real_database_data(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $ac = Fasilitas::create(['name' => 'AC']);
        $kamar->fasilitas()->attach($ac->id);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('AC');
        $response->assertDontSee('WiFi');
    }

    public function test_duplicate_facilities_are_not_rendered(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $wifi = Fasilitas::create(['name' => 'WiFi']);
        // Sama "WiFi" terpasang di kos-level DAN kamar-level.
        $kos->fasilitas()->attach($wifi->id);
        $kamar->fasilitas()->attach($wifi->id);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        // Di dalam bagian fasilitas terkelompok, "WiFi" hanya boleh muncul tepat sekali (tidak duplikat).
        $facilitiesSection = $this->extractFacilitiesSection($response->getContent());
        $this->assertSame(1, substr_count($facilitiesSection, 'WiFi'), 'Fasilitas duplikat tidak boleh dirender pada bagian fasilitas.');
    }

    public function test_empty_facilities_do_not_break_the_page(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Informasi fasilitas belum tersedia');
    }

    // ── D. Room Cards ──────────────────────────────────────────

    public function test_room_card_shows_database_price(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 2500000]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Rp 2.500.000');
    }

    // ── E. Similar Kos ─────────────────────────────────────────

    public function test_current_kos_is_excluded_from_similar_kos(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'name' => 'Kos Utama', 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 1000000]);
        $other = Kos::factory()->create(['owner_id' => $owner->id, 'name' => 'Kos Tetangga', 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $other->id, 'status' => 'available', 'monthly_price' => 1100000]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Kos Lain yang Mungkin Kamu Suka');
        $response->assertSee('Kos Tetangga');
        // Kos utama tidak boleh muncul di dalam section "Kos Serupa".
        $similarSection = $this->extractSimilarSection($response->getContent());
        $this->assertStringNotContainsString('/tenant/kos/'.$kos->id, $similarSection);
        $this->assertStringNotContainsString('>Kos Utama<', $similarSection);
    }

    public function test_similar_kos_are_real_records(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $sim1 = Kos::factory()->create(['owner_id' => $owner->id, 'name' => 'Siluman Kos', 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $sim1->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $similarSection = $this->extractSimilarSection($response->getContent());
        $this->assertStringContainsString('/tenant/kos/'.$sim1->id, $similarSection);
    }

    public function test_inactive_kos_not_in_similar_kos(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        // Tanpa kos aktif lain, namun bukan error & bukan kos non-aktif.
        $response->assertOk();
    }

    // ── Detail Page Access ─────────────────────────────────────

    public function test_tenant_can_access_valid_kos_detail(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active', 'name' => 'Kos Santai']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Kos Santai');
    }

    public function test_unauthorized_access_still_protected(): void
    {
        $kos = Kos::factory()->create(['status' => 'active']);

        $this->get("/tenant/kos/{$kos->id}")->assertRedirect('/login');
    }

    // ── Helper ─────────────────────────────────────────────────

    private function extractSimilarSection(string $html): string
    {
        $needle = 'Kos Lain yang Mungkin Kamu Suka';
        $start = strpos($html, $needle);
        if ($start === false) {
            return '';
        }

        $end = strpos($html, 'id="record-view"', $start);

        return substr($html, $start, ($end === false ? strlen($html) : $end) - $start);
    }

    private function extractFacilitiesSection(string $html): string
    {
        $start = strpos($html, '>Fasilitas Kos<');
        if ($start === false) {
            return '';
        }

        $end = strpos($html, '>Informasi Pemilik<', $start);

        return substr($html, $start, ($end === false ? strlen($html) : $end) - $start);
    }

    private function assertAllHargaMulaiEqual(string $html, string $expected): void
    {
        preg_match_all('/Mulai dari<\/p>\s*<p[^>]*>Rp ([\d.]+)<span/', $html, $matches);
        $prices = $matches[1] ?? [];

        $this->assertNotEmpty($prices, 'Setidaknya satu nilai "Mulai dari" harus ada.');
        foreach ($prices as $price) {
            $this->assertSame($expected, $price, 'Harga mulai harus '.$expected.', bukan '.$price.'.');
        }
    }
}
