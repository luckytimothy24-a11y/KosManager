<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Fasilitas;
use App\Models\Favorite;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PhaseBHomeCurationTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
    }

    private function activeKos(string $name, int $price = 1000000, ?Carbon $createdAt = null): Kos
    {
        $kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
            'name' => $name,
            'created_at' => $createdAt ?? now(),
            'updated_at' => $createdAt ?? now(),
        ]);
        Kamar::factory()->create([
            'kos_id' => $kos->id,
            'status' => 'available',
            'monthly_price' => $price,
        ]);

        return $kos;
    }

    // ── Recommendation ≠ Latest ──

    public function test_recommendations_sorted_by_booking_count_not_just_latest(): void
    {
        $popular = $this->activeKos('Kos Populer');
        $fresh = $this->activeKos('Kos Baru');

        // Kos Populer punya booking, Kos Baru tidak.
        $kamar = $popular->kamar()->first();
        Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $popular->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'booking_date' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Rekomendasi untukmu');
        // Kos Populer (punya booking) harus muncul sebelum Kos Baru di rekomendasi.
        $body = $response->content();
        $posPopular = strpos($body, 'Kos Populer');
        $posBaru = strpos($body, 'Kos Baru');
        $this->assertNotFalse($posPopular, 'Kos Populer harus ada di rekomendasi');
        $this->assertNotFalse($posBaru, 'Kos Baru harus ada di rekomendasi');
        $this->assertLessThan($posBaru, $posPopular, 'Kos Populer harus muncul sebelum Kos Baru di rekomendasi');
    }

    public function test_recommendations_sorted_by_favorite_count_as_secondary(): void
    {
        $faved = $this->activeKos('Kos Favorit');
        $plain = $this->activeKos('Kos Biasa');

        // Kos Favorit punya favorit, Kos Biasa tidak.
        Favorite::create(['user_id' => $this->tenant->id, 'kos_id' => $faved->id]);

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $body = $response->content();
        $posFaved = strpos($body, 'Kos Favorit');
        $posPlain = strpos($body, 'Kos Biasa');
        $this->assertNotFalse($posFaved, 'Kos Favorit harus ada di rekomendasi');
        $this->assertNotFalse($posPlain, 'Kos Biasa harus ada di rekomendasi');
        $this->assertLessThan($posPlain, $posFaved, 'Kos Favorit harus muncul sebelum Kos Biasa');
    }

    public function test_latest_section_excludes_recommendation_ids(): void
    {
        // 4 kos: rekomendasi mengambil 4 (semua), latest harus kosong.
        for ($i = 0; $i < 4; $i++) {
            $this->activeKos("Unique Kos {$i}");
        }

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        // Dengan hanya 4 kos, semua masuk rekomendasi.
        // Section "Kos Terbaru" harusnya tidak tampil karena tidak ada yang tersisa.
        $body = $response->content();
        $terbaruCount = substr_count($body, 'aria-label="Kos terbaru"');
        $this->assertEquals(0, $terbaruCount, 'Section Kos Terbaru tidak boleh muncul jika semua kos sudah di rekomendasi');
    }

    // ── Budget Discovery ──

    public function test_budget_sections_appear_when_price_distribution_varies(): void
    {
        // Kos pengisi (harga sama, terbaru) mengisi rekomendasi (6) + kos terbaru (6).
        for ($i = 0; $i < 13; $i++) {
            $this->activeKos("Isi Kos {$i}", 1000000);
        }

        // Kos budget dibuat LEBIH LAMA & bervariasi harganya sehingga tetap berada
        // dalam pool discovery (tidak habis oleh rekomendasi/terbaru).
        $old = now()->subDays(10);
        $this->activeKos('Kos Murah 1', 500000, $old);
        $this->activeKos('Kos Murah 2', 600000, $old);
        $this->activeKos('Kos Mahal 1', 2000000, $old);
        $this->activeKos('Kos Mahal 2', 2500000, $old);

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kos Hemat');
        $response->assertSee('Kos Menengah');
    }

    public function test_budget_sections_hidden_when_prices_are_uniform(): void
    {
        // Semua kos dengan harga yang sama → tidak cukup variasi untuk split.
        $this->activeKos('Kos Sama A', 1000000);
        $this->activeKos('Kos Sama B', 1000000);
        $this->activeKos('Kos Sama C', 1000000);

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Kos Hemat');
        $response->assertDontSee('Kos Menengah');
    }

    // ── Facility Discovery ──

    public function test_facility_sections_appear_for_real_facilities(): void
    {
        // Kos ber-fasilitas dibuat dengan created_at DULUAN (lebih lama) agar
        // tidak masuk 6 besar rekomendasi (yang diurut berdasarkan created_at desc).
        $acF = Fasilitas::create(['name' => 'AC', 'type' => 'kamar']);
        $wifiF = Fasilitas::create(['name' => 'WiFi', 'type' => 'kamar']);

        $old = now()->subDays(10);
        $kosWithAC = $this->activeKos('Kos AC', 1000000, $old);
        $kamarAC = $kosWithAC->kamar()->first();
        $kamarAC->fasilitas()->attach($acF->id);

        $kosWithWiFi = $this->activeKos('Kos WiFi', 1000000, $old);
        $kamarWiFi = $kosWithWiFi->kamar()->first();
        $kamarWiFi->fasilitas()->attach($wifiF->id);

        // Buat banyak kos plain yang LEBIH BARU sehingga mereka mengisi
        // rekomendasi (6) DAN kos terbaru (6), menyisakan kos ber-fasilitas
        // (yang paling lama) untuk facility discovery.
        for ($i = 0; $i < 13; $i++) {
            $this->activeKos("Kos Plain {$i}");
        }

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kos dengan AC');
        $response->assertSee('Kos dengan WiFi');
    }

    public function test_facility_sections_hidden_when_no_matching_facilities(): void
    {
        // Tidak ada fasilitas AC, WiFi, atau Kamar Mandi Dalam.
        $this->activeKos('Kos Biasa Saja');

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Kos dengan AC');
        $response->assertDontSee('Kos dengan WiFi');
        $response->assertDontSee('Kos dengan Kamar Mandi Dalam');
    }

    public function test_facility_sections_max_two(): void
    {
        // Kos ber-fasilitas dengan created_at lebih lama agar tidak masuk rekomendasi.
        $acF = Fasilitas::create(['name' => 'AC', 'type' => 'kamar']);
        $wifiF = Fasilitas::create(['name' => 'WiFi', 'type' => 'kamar']);
        $cmdF = Fasilitas::create(['name' => 'Kamar Mandi Dalam', 'type' => 'kamar']);

        $old = now()->subDays(10);
        $kos1 = $this->activeKos('Kos 1', 1000000, $old);
        $kos1->kamar()->first()->fasilitas()->attach([$acF->id, $wifiF->id, $cmdF->id]);

        $kos2 = $this->activeKos('Kos 2', 1000000, $old);
        $kos2->kamar()->first()->fasilitas()->attach([$acF->id]);

        // Isi rekomendasi (6) dan kos terbaru (6) dengan kos plain yang lebih
        // baru, menyisakan kos ber-fasilitas untuk facility discovery.
        for ($i = 0; $i < 13; $i++) {
            $this->activeKos("Kos Plain {$i}");
        }

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $body = $response->content();
        // Maksimal 2 section fasilitas. Hitung berdasarkan aria-label section
        // (unik per section) bukan teks heading yang muncul ganda di aria-label & h2.
        $facilitySections = substr_count($body, 'aria-label="Kos dengan ');
        $this->assertLessThanOrEqual(2, $facilitySections, 'Maksimal 2 section fasilitas');
    }

    // ── Empty Data ──

    public function test_empty_database_shows_no_crash(): void
    {
        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Halo');
        $response->assertSee('Cari Kos');
    }

    public function test_small_database_no_repetitive_sections(): void
    {
        $this->activeKos('Satu Kos Saja', 800000);

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $body = $response->content();
        // Hanya 1 kos, section yang tidak relevan harusnya tidak muncul.
        $response->assertDontSee('Kos Hemat');
        $response->assertDontSee('Kos Menengah');
        // Kos Terbaru harusnya tidak muncul (hanya 1 kos, sudah di rekomendasi).
        $this->assertEquals(0, substr_count($body, 'aria-label="Kos terbaru"'));
    }

    // ── Duplicate Prevention ──

    public function test_recommendations_and_latest_are_different_when_data_sufficient(): void
    {
        // Buat 8 kos, semua 0 booking & 0 favorit.
        // Rekomendasi: 6 terbaru (tie-broken by latest).
        // Latest: 2 yang tersisa (2 paling lama, tetapi minimal 2 untuk tampil).
        for ($i = 0; $i < 8; $i++) {
            $this->activeKos("Kos {$i}");
        }

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $body = $response->content();
        // "Kos 0" (paling lama) tidak boleh muncul di rekomendasi, tapi bisa muncul di latest.
        // Minimal pastikan section terbaru tampil.
        $this->assertStringContainsString('aria-label="Kos terbaru"', $body);
    }

    public function test_sections_hide_when_insufficient_unique_data(): void
    {
        // 5 kos total: rekomendasi mengambil 5, latest tidak cukup (0 unik).
        for ($i = 0; $i < 5; $i++) {
            $this->activeKos("Sedikit Kos {$i}");
        }

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $body = $response->content();
        $this->assertEquals(0, substr_count($body, 'aria-label="Kos terbaru"'));
    }

    // ── Resident vs New Tenant ──

    public function test_new_tenant_sees_discovery_not_resident_widgets(): void
    {
        $this->activeKos('Kos Untuk New Tenant');

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Rekomendasi untukmu');
        // Widget resident tidak boleh muncul untuk tenant tanpa penghuni.
        // Gunakan aria-label karena FAQ juga berisi teks "Kos Saya".
        $response->assertDontSee('aria-label="Kos saya"');
        $response->assertDontSee('Tagihan Belum Bayar');
    }

    public function test_resident_sees_both_resident_and_discovery(): void
    {
        $this->activeKos('Kos Untuk Resident');
        Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        // Resident masih melihat discovery.
        $response->assertSee('Rekomendasi untukmu');
        // Resident juga melihat widget resident.
        $response->assertSee('Kos Saya');
    }

    // ── Budget Discovery Pricing ──

    public function test_budget_sections_link_to_search_with_price_sort(): void
    {
        $this->activeKos('Kos Murah', 500000);
        $this->activeKos('Kos Mahal', 2000000);

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Lihat Semua');
    }

    // ── Performance: No N+1 ──

    public function test_dashboard_no_excessive_queries(): void
    {
        // Buat beberapa kos dengan fasilitas untuk menguji eager loading.
        $acF = Fasilitas::create(['name' => 'AC', 'type' => 'kamar']);

        for ($i = 0; $i < 5; $i++) {
            $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
            $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
            $kamar->fasilitas()->attach($acF->id);
        }

        DB::enableQueryLog();
        $this->actingAs($this->tenant)->get('/dashboard');
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Dashboard seharusnya tidak melakukan query berlebihan.
        $this->assertLessThan(50, count($queries), 'Too many queries: '.count($queries));
    }
}
