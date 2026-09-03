<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseDLocationDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function tenant(): User
    {
        return User::factory()->create(['role' => 'tenant']);
    }

    protected function kos(string $address, string $status = 'active', ?int $ownerId = null): Kos
    {
        return Kos::factory()->create([
            'owner_id' => $ownerId ?: User::factory()->create(['role' => 'owner'])->id,
            'address' => $address,
            'status' => $status,
        ]);
    }

    // ── Dashboard location discovery ───────────────────────────

    public function test_dashboard_tenant_shows_location_discovery(): void
    {
        $this->kos('Jl. Sudirman No. 10');
        $this->kos('Jl. Ahmad Yani No. 25');

        $response = $this->actingAs($this->tenant())->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Jelajahi berdasarkan lokasi');
        $response->assertSee('Jl. Sudirman No. 10');
        $response->assertSee('Jl. Ahmad Yani No. 25');
    }

    public function test_locations_come_from_real_kos_address(): void
    {
        $this->kos('Jl. Merdeka No. 1');

        $response = $this->actingAs($this->tenant())->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Jl. Merdeka No. 1');
        $response->assertDontSee('Kota Yogyakarta');
        $response->assertDontSee('Yogyakarta');
    }

    public function test_duplicate_addresses_are_grouped_and_counted(): void
    {
        $this->kos('Jl. Sudirman No. 10');
        $this->kos('Jl. Sudirman No. 10');
        $this->kos('Jl. Sudirman No. 10');

        $response = $this->actingAs($this->tenant())->get('/dashboard');

        $response->assertOk();
        // Pada bagian discovery lokasi, alamat yang sama muncul sebagai satu kartu (title dipakai sekali per kartu).
        $section = $this->extractLocationSection($response->getContent());
        $this->assertSame(1, substr_count($section, 'title="Jl. Sudirman No. 10"'));
        $response->assertSee('3 kos');
    }

    public function test_inactive_kos_do_not_create_locations(): void
    {
        $this->kos('Jl. Satu No. 1', 'active');
        $this->kos('Jl. Nonaktif No. 1', 'inactive');

        $response = $this->actingAs($this->tenant())->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Jl. Satu No. 1');
        $response->assertDontSee('Jl. Nonaktif No. 1');
    }

    public function test_location_count_matches_active_kos_only(): void
    {
        // 2 active + 1 inactive pada alamat yang sama -> count harus 2.
        $this->kos('Jl. Gatot No. 5', 'active');
        $this->kos('Jl. Gatot No. 5', 'active');
        $this->kos('Jl. Gatot No. 5', 'inactive');

        $response = $this->actingAs($this->tenant())->get('/dashboard');

        $response->assertOk();
        $response->assertSee('2 kos');
    }

    public function test_dashboard_empty_location_state(): void
    {
        // Tidak ada kos aktif -> lokasi kosong.
        $response = $this->actingAs($this->tenant())->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Belum ada lokasi kos untuk dijelajahi.');
    }

    public function test_location_card_links_to_listing_with_loc(): void
    {
        $this->kos('Jl. Sudirman No. 10');

        $response = $this->actingAs($this->tenant())->get('/dashboard');

        $response->assertOk();
        $response->assertSee('/tenant/kos?loc=');

        $listing = $this->actingAs($this->tenant())->get('/tenant/kos?loc='.rawurlencode('Jl. Sudirman No. 10'));
        $listing->assertOk();
        $listing->assertSee('Jl. Sudirman No. 10');
    }

    // ── Location listing ────────────────────────────────────────

    public function test_selecting_location_returns_only_matching_kos(): void
    {
        $match = $this->kos('Jl. Sudirman No. 10');
        $match2 = $this->kos('Jl. Sudirman No. 10');
        $other = $this->kos('Jl. Lain No. 99');

        $response = $this->actingAs($this->tenant())->get('/tenant/kos?loc='.rawurlencode('Jl. Sudirman No. 10'));

        $response->assertOk();
        $response->assertSee($match->name);
        $response->assertSee($match2->name);
        $response->assertDontSee($other->name);
    }

    public function test_invalid_location_returns_no_unrelated_kos(): void
    {
        $this->kos('Jl. Sudirman No. 10');

        $response = $this->actingAs($this->tenant())->get('/tenant/kos?loc='.rawurlencode('Jl. Tidak Ada No. 777'));

        $response->assertOk();
        $response->assertSee('Kos tidak ditemukan di lokasi ini');
    }

    public function test_location_filter_is_exact_match(): void
    {
        $this->kos('Jl. Sudirman No. 10');
        $partial = $this->kos('Jl. Sudirman No. 100');

        $response = $this->actingAs($this->tenant())->get('/tenant/kos?loc='.rawurlencode('Jl. Sudirman No. 10'));

        $response->assertOk();
        $response->assertDontSee($partial->name);
    }

    // ── Query persistence ──────────────────────────────────────

    public function test_search_and_location_work_together(): void
    {
        $this->kos('Jl. Sudirman No. 10');
        $hotel = $this->kos('Jl. Sudirman No. 10');
        $hotel->update(['name' => 'Kos Melati Jaya']);

        $this->kos('Jl. Sudirman No. 10');

        $response = $this->actingAs($this->tenant())
            ->get('/tenant/kos?loc='.rawurlencode('Jl. Sudirman No. 10').'&q='.rawurlencode('Melati'));

        $response->assertOk();
        $response->assertSee('Kos Melati Jaya', false);
    }

    public function test_price_filter_and_location_work_together(): void
    {
        $kosA = $this->kos('Jl. Sudirman No. 10');
        Kamar::factory()->create(['kos_id' => $kosA->id, 'status' => 'available', 'monthly_price' => 1000000]);
        $kosB = $this->kos('Jl. Sudirman No. 10');
        Kamar::factory()->create(['kos_id' => $kosB->id, 'status' => 'available', 'monthly_price' => 3000000]);

        $response = $this->actingAs($this->tenant())
            ->get('/tenant/kos?loc='.rawurlencode('Jl. Sudirman No. 10').'&price_max=1500000');

        $response->assertOk();
        $response->assertSee($kosA->name);
        $response->assertDontSee($kosB->name);
    }

    public function test_sort_and_location_work_together(): void
    {
        $kosA = $this->kos('Jl. Sudirman No. 10');
        Kamar::factory()->create(['kos_id' => $kosA->id, 'status' => 'available', 'monthly_price' => 5000000]);
        $kosB = $this->kos('Jl. Sudirman No. 10');
        Kamar::factory()->create(['kos_id' => $kosB->id, 'status' => 'available', 'monthly_price' => 1000000]);

        $response = $this->actingAs($this->tenant())
            ->get('/tenant/kos?loc='.rawurlencode('Jl. Sudirman No. 10').'&sort=harga_terendah');

        $response->assertOk();
        // Kamar termurah (kosB) tampil di urutan pertama -> posisi kosB sebelum kosA.
        $html = $response->getContent();
        $this->assertLessThan(strpos($html, $kosA->name), strpos($html, $kosB->name));
    }

    public function test_availability_filter_and_location_work_together(): void
    {
        $withAvail = $this->kos('Jl. Sudirman No. 10');
        Kamar::factory()->create(['kos_id' => $withAvail->id, 'status' => 'available']);
        $noAvail = $this->kos('Jl. Sudirman No. 10');

        $response = $this->actingAs($this->tenant())
            ->get('/tenant/kos?loc='.rawurlencode('Jl. Sudirman No. 10').'&tersedia_only=1');

        $response->assertOk();
        $response->assertSee($withAvail->name);
        $response->assertDontSee($noAvail->name);
    }

    public function test_pagination_preserves_location(): void
    {
        // Lebih dari jumlah per-halaman (9) untuk memicu pagination.
        foreach (range(1, 12) as $i) {
            $this->kos('Jl. Sudirman No. 10');
        }
        $other = $this->kos('Jl. Lain No. 99');
        $other->update(['name' => 'Kos Lokasi Berbeda Sekali']);

        $page1 = $this->actingAs($this->tenant())->get('/tenant/kos?loc='.rawurlencode('Jl. Sudirman No. 10'));
        $page1->assertOk();

        $page2 = $this->actingAs($this->tenant())->get('/tenant/kos?loc='.rawurlencode('Jl. Sudirman No. 10').'&page=2');
        $page2->assertOk();
        // Halaman 2 tidak boleh menampilkan kos dari lokasi lain (nama kos lokasi lain tidak muncul di hasil).
        $page2->assertDontSee('Kos Lokasi Berbeda Sekali');
    }

    public function test_reset_clears_location_state(): void
    {
        $this->kos('Jl. Sudirman No. 10');

        $response = $this->actingAs($this->tenant())->get('/tenant/kos?loc='.rawurlencode('Jl. Sudirman No. 10'));

        $response->assertOk();
        $resetLink = 'href="'.$this->app->url->to('/tenant/kos').'"';
        // Reset link tersedia dan menuju URL bersih (tanpa loc).
        $response->assertSee($resetLink, false);
    }

    // ── Security / isolation ───────────────────────────────────

    public function test_unauthorized_access_still_protected(): void
    {
        $this->kos('Jl. Sudirman No. 10');

        $this->get('/tenant/kos?loc='.rawurlencode('Jl. Sudirman No. 10'))->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_owner_cannot_access_tenant_listing_with_loc(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->kos('Jl. Sudirman No. 10', 'active', $owner->id);

        $this->actingAs($owner)->get('/tenant/kos?loc='.rawurlencode('Jl. Sudirman No. 10'))->assertForbidden();
    }

    private function extractLocationSection(string $html): string
    {
        $start = strpos($html, 'Jelajahi berdasarkan lokasi');
        if ($start === false) {
            return '';
        }

        $end = strpos($html, 'Rekomendasi untukmu', $start);

        return substr($html, $start, ($end === false ? strlen($html) : $end) - $start);
    }
}
