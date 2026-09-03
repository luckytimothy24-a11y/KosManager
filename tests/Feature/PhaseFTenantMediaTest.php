<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhaseFTenantMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function createTenant(): User
    {
        return User::factory()->create(['role' => 'tenant']);
    }

    protected function createKos(array $attributes = []): Kos
    {
        $kos = Kos::factory()->create(array_merge(['status' => 'active', 'name' => 'Kos Media'], $attributes));
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        return $kos;
    }

    private function bootPublicStorageFake(): void
    {
        Storage::fake('public');
    }

    // ── Image present ──────────────────────────────────────────

    public function test_kos_with_photo_renders_the_hero_image(): void
    {
        $this->bootPublicStorageFake();
        $tenant = $this->createTenant();
        $kos = $this->createKos(['photo' => 'kos/kenanga.jpg']);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        $response->assertSee('storage/kos/kenanga.jpg', false);
    }

    public function test_hero_image_has_meaningful_alt_text(): void
    {
        $this->bootPublicStorageFake();
        $tenant = $this->createTenant();
        $kos = $this->createKos(['photo' => 'kos/mawar.jpg', 'name' => 'Kos Media']);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        $response->assertSee('alt="Foto Kos Media"', false);
    }

    public function test_viewer_trigger_exists_when_photo_present(): void
    {
        $this->bootPublicStorageFake();
        $tenant = $this->createTenant();
        $kos = $this->createKos(['photo' => 'kos/melati.jpg']);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        $triggerLabel = 'aria-label="Perbesar foto Kos Media"';
        $response->assertSee($triggerLabel, false);
        $response->assertSee('aria-haspopup="dialog"', false);
    }

    public function test_viewer_close_control_is_accessible(): void
    {
        $this->bootPublicStorageFake();
        $tenant = $this->createTenant();
        $kos = $this->createKos(['photo' => 'kos/melati.jpg']);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        $response->assertSee('aria-label="Tutup foto"', false);
        // Viewer diwadahi role dialog yang accessible.
        $response->assertSee('role="dialog"', false);
        $response->assertSee('aria-modal="true"', false);
    }

    public function test_escape_keyboard_behaviour_is_wired_in_component(): void
    {
        $this->bootPublicStorageFake();
        $tenant = $this->createTenant();
        $kos = $this->createKos(['photo' => 'kos/single.jpg']);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        // Perilaku Escape dirender sebagai atribut Alpine pada komponen viewer.
        $response->assertSee('@keydown.escape.window', false);
    }

    public function test_photo_viewer_opens_on_click_via_alpine(): void
    {
        $this->bootPublicStorageFake();
        $tenant = $this->createTenant();
        $kos = $this->createKos(['photo' => 'kos/single.jpg']);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        $viewerContent = $response->getContent();
        // Pemanggil viewer mengikat state viewerOpen = true saat tombol foto diklik.
        $this->assertStringContainsString('@click="viewerOpen = true', $viewerContent);
        // Kondisi tampil viewer mengacu ke state yang sama.
        $this->assertStringContainsString('x-if="viewerOpen', $viewerContent);
    }

    public function test_one_photo_status_does_not_imply_multiple_photos(): void
    {
        $this->bootPublicStorageFake();
        $tenant = $this->createTenant();
        $kos = $this->createKos(['photo' => 'kos/single.jpg']);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        $response->assertSee('1 foto', false);
        // Tidak boleh ada indikasi lebih dari satu foto / galeri.
        $response->assertDontSee('galeri');
        $response->assertDontSee('1/3');
    }

    // ── No photo (fallback) ────────────────────────────────────

    public function test_kos_without_photo_renders_existing_fallback(): void
    {
        $tenant = $this->createTenant();
        $kos = $this->createKos(['photo' => null]);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        // Huruf/inisial nama masih dipakai sebagai fallback.
        $response->assertSee('>K</span>', false);
    }

    public function test_kos_without_photo_introduces_no_fake_image_url(): void
    {
        $tenant = $this->createTenant();
        $kos = $this->createKos(['photo' => null]);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        $content = $response->getContent();

        // Tidak boleh ada URL gambar kos buatan saat tidak ada foto.
        $this->assertStringNotContainsString('storage/'.$kos->id.'/', $content);
        $this->assertStringNotContainsString('/storage/kos/undefined', $content);
        $this->assertStringNotContainsString('/storage/placeholder', $content);
        // Tidak ada viewer ketika tidak ada gambar.
        $this->assertStringNotContainsString('aria-label="Perbesar foto', $content);
    }

    public function test_kos_without_photo_has_no_broken_image_presentation(): void
    {
        $tenant = $this->createTenant();
        $kos = $this->createKos(['photo' => null]);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        $content = $response->getContent();

        // Hero tanpa foto tidak boleh memuat elemen img yang akan rusak.
        $this->assertStringNotContainsString('/storage/></img', $content);
        $this->assertStringNotContainsString('src="/storage/"', $content);
    }

    // ── Isolation ──────────────────────────────────────────────

    public function test_hero_and_viewer_do_not_render_other_kos_photo(): void
    {
        $this->bootPublicStorageFake();
        $tenant = $this->createTenant();
        $kosA = $this->createKos(['name' => 'Kos A', 'photo' => 'kos/a.jpg']);
        $kosB = $this->createKos(['name' => 'Kos B', 'photo' => 'kos/b.jpg']);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.show', $kosA));

        $response->assertOk();
        $hero = $this->extractHeroSection($response->getContent());
        // Media presentation (hero + viewer) hanya mengacu ke foto kos saat ini.
        $this->assertStringContainsString('storage/kos/a.jpg', $hero);
        $this->assertStringNotContainsString('storage/kos/b.jpg', $hero);
    }

    public function test_viewer_only_references_current_kos_photo(): void
    {
        $this->bootPublicStorageFake();
        $tenant = $this->createTenant();
        $kosA = $this->createKos(['name' => 'Kos A', 'photo' => 'kos/a.jpg']);
        $kosB = $this->createKos(['name' => 'Kos B', 'photo' => 'kos/b.jpg']);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.show', $kosA));

        $response->assertOk();
        $hero = $this->extractHeroSection($response->getContent());
        $this->assertStringContainsString('@click="viewerOpen = true', $hero);
        $this->assertStringContainsString('storage/kos/a.jpg', $hero);
        $this->assertStringNotContainsString('storage/kos/b.jpg', $hero);
    }

    private function extractHeroSection(string $html): string
    {
        $start = strpos($html, 'x-data="{ viewerOpen: false }"');
        if ($start === false) {
            return '';
        }

        $end = strpos($html, 'class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6"', $start);

        return substr($html, $start, ($end === false ? strlen($html) : $end) - $start);
    }
}
