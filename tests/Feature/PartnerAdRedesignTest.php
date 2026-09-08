<?php

namespace Tests\Feature;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingPackage;
use App\Models\Fasilitas;
use App\Models\Kos;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PREMIUM PARTNER AD REDESIGN — pengujian komponen iklan partner baru
 * (partner-ad.blade.php):
 * - rendering data-driven (SPONSORED badge, brand, headline, deskripsi, CTA)
 * - layout per variant (banner | card | compact) & perilaku responsif
 * - tema visual per partner (DANA/Shopee/GoPay) yang TIDAK identik
 * - fallback aman saat logo/gambar kampanye tidak ada (tidak ada gambar rusak)
 * - keamanan: link iklan selalu lewat route tracking internal (tenant.ad.click),
 *   TIDAK target=_blank, punya aria-label (name aksesibel)
 * - tracking klik tetap tercatat
 */
class PartnerAdRedesignTest extends TestCase
{
    use RefreshDatabase;

    private function livePartnerCampaign(string $slug, string $placement, array $creative = []): AdvertisingCampaign
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $partner = Partner::factory()->create(['slug' => $slug, 'name' => ucfirst($slug)]);
        $package = AdvertisingPackage::factory()->create(['placement' => $placement]);

        return AdvertisingCampaign::factory()
            ->active()
            ->thirdParty($placement)
            ->create(array_merge([
                'owner_id' => $owner->id,
                'package_id' => $package->id,
                'partner_id' => $partner->id,
                'advertiser_name' => ucfirst($slug),
            ], $creative));
    }

    private function renderAd(AdvertisingCampaign $ad, string $placement, string $variant): string
    {
        return view('tenant.partials.partner-ad', compact('ad', 'placement', 'variant'))->render();
    }

    // ---------------------------------------------------------------------
    // Rendering dasar
    // ---------------------------------------------------------------------

    public function test_banner_renders_sponsored_badge_brand_headline_desc_cta_and_tracking_url(): void
    {
        $ad = $this->livePartnerCampaign('shopee', 'homepage', [
            'headline' => 'Lengkapi kebutuhan kos tanpa bikin kantong boncos.',
            'advertiser_description' => 'Mulai dari perlengkapan kamar sampai kebutuhan harian.',
            'cta_label' => 'Belanja Sekarang',
        ]);

        $html = $this->renderAd($ad, 'homepage', 'banner');

        $this->assertStringContainsString('Sponsored', $html);
        $this->assertStringContainsString('Shopee', $html);
        $this->assertStringContainsString('Lengkapi kebutuhan kos tanpa bikin kantong boncos.', $html);
        $this->assertStringContainsString('Mulai dari perlengkapan kamar sampai kebutuhan harian.', $html);
        $this->assertStringContainsString('Belanja Sekarang', $html);
        $this->assertStringContainsString('Iklan · Partner KosManager', $html);

        // Link iklan = route tracking internal, bukan langsung ke advertiser.
        $this->assertStringContainsString('/tenant/ad/click/'.$ad->id, $html);
        $this->assertStringContainsString('placement=homepage', $html);
    }

    public function test_card_variant_renders_compact_premium_markup(): void
    {
        $ad = $this->livePartnerCampaign('gopay', 'tenant_dashboard', [
            'headline' => 'Urusan sehari-hari, jadi lebih praktis.',
            'cta_label' => 'Selengkapnya',
        ]);

        $html = $this->renderAd($ad, 'tenant_dashboard', 'card');

        $this->assertStringContainsString('Sponsored', $html);
        $this->assertStringContainsString('Urusan sehari-hari, jadi lebih praktis.', $html);
        $this->assertStringContainsString('Selengkapnya', $html);
        $this->assertStringContainsString('line-clamp-2', $html);
        $this->assertStringContainsString('min-h-[44px]', $html);
    }

    public function test_compact_variant_renders_slim_row_with_full_touch_target(): void
    {
        $ad = $this->livePartnerCampaign('dana', 'marketplace', [
            'headline' => 'Bayar kebutuhan harian jadi lebih praktis.',
            'cta_label' => 'Lihat Promo',
        ]);

        $html = $this->renderAd($ad, 'marketplace', 'compact');

        // Baris ramping: monogram, label Sponsored, headline, CTA full-touch.
        $this->assertStringContainsString('Sponsored', $html);
        $this->assertStringContainsString('Bayar kebutuhan harian jadi lebih praktis.', $html);
        $this->assertStringContainsString('Lihat Promo', $html);
        $this->assertStringContainsString('min-h-[44px]', $html);
        $this->assertStringContainsString('truncate', $html);
    }

    // ---------------------------------------------------------------------
    // Tema visual per partner (diferensiasi, bukan kartu identik)
    // ---------------------------------------------------------------------

    public function test_partner_specific_visual_identity_dana_shopee_gopay(): void
    {
        $dana = $this->renderAd($this->livePartnerCampaign('dana', 'marketplace'), 'marketplace', 'banner');
        $shopee = $this->renderAd($this->livePartnerCampaign('shopee', 'homepage'), 'homepage', 'banner');
        $gopay = $this->renderAd($this->livePartnerCampaign('gopay', 'detail'), 'detail', 'banner');

        // Tiap partner punya palet gradasi & CTA yang berbeda (CTA cukup gelap agar
        // teks putih memenuhi WCAG AA >= 4.5:1).
        $this->assertStringContainsString('from-[#eaf4ff]', $dana);
        $this->assertStringContainsString('bg-[#0a5fd0]', $dana);
        $this->assertStringContainsString('from-[#fff3ee]', $shopee);
        $this->assertStringContainsString('bg-[#d33a1d]', $shopee);
        $this->assertStringContainsString('from-[#eefbf1]', $gopay);
        $this->assertStringContainsString('bg-[#008316]', $gopay);

        // Pastikan kartu tidak identik satu sama lain.
        $this->assertStringNotContainsString('from-[#eaf4ff]', $shopee);
        $this->assertStringNotContainsString('bg-[#d33a1d]', $dana);
        $this->assertStringNotContainsString('bg-[#008316]', $shopee);
    }

    public function test_unknown_partner_uses_neutral_default_theme(): void
    {
        $ad = $this->livePartnerCampaign('mitra-baru', 'homepage');

        $html = $this->renderAd($ad, 'homepage', 'banner');

        $this->assertStringContainsString('from-slate-50', $html);
        $this->assertStringContainsString('bg-primary-600', $html);
    }

    // ---------------------------------------------------------------------
    // Fallback aman — tanpa logo / tanpa gambar
    // ---------------------------------------------------------------------

    public function test_banner_without_logo_or_image_falls_back_to_typographic_monogram(): void
    {
        $ad = $this->livePartnerCampaign('dana', 'marketplace');

        $html = $this->renderAd($ad, 'marketplace', 'banner');

        // Monogram inisial muncul dan TIDAK ada <img> broken/placeholder.
        $this->assertStringContainsString('aria-hidden="true">D', $html);
        $this->assertStringNotContainsString('<img', $html);

        // Card & compact juga memakai monogram.
        $card = $this->renderAd($ad, 'marketplace', 'card');
        $this->assertStringContainsString('aria-hidden="true">D', $card);
        $this->assertStringNotContainsString('<img', $card);

        $compact = $this->renderAd($ad, 'marketplace', 'compact');
        $this->assertStringContainsString('aria-hidden="true">D', $compact);
    }

    public function test_campaign_with_logo_renders_registered_partner_logo(): void
    {
        $ad = $this->livePartnerCampaign('shopee', 'homepage');
        $ad->partner->update(['logo' => 'https://cdn.example.com/shopee.png']);

        $html = $this->renderAd($ad->fresh(), 'homepage', 'banner');

        $this->assertStringContainsString('https://cdn.example.com/shopee.png', $html);
    }

    // ---------------------------------------------------------------------
    // Keamanan & aksesibilitas
    // ---------------------------------------------------------------------

    public function test_ad_link_never_sets_target_blank_and_has_accessible_name(): void
    {
        $ad = $this->livePartnerCampaign('gopay', 'detail', [
            'cta_label' => 'Selengkapnya',
        ]);

        $html = $this->renderAd($ad, 'detail', 'banner');

        $this->assertStringNotContainsString('target="_blank"', $html);
        $this->assertStringContainsString('aria-label="Selengkapnya Gopay"', $html);
        // CTA berupa link menuju route tracking internal (bukan langsung brand).
        $expectedUrl = route('tenant.ad.click', ['campaign' => $ad->id, 'placement' => 'detail']);
        $this->assertStringContainsString('href="'.$expectedUrl.'"', $html);
    }

    // ---------------------------------------------------------------------
    // Responsif — struktur per breakpoint (proxy scrollWidth==clientWidth;
    // verifikasi piksel dilakukan pada Visual QA browser).
    // ---------------------------------------------------------------------

    public function test_banner_image_hidden_on_mobile_shown_on_md_plus(): void
    {
        $ad = $this->livePartnerCampaign('shopee', 'homepage', [
            'image' => 'https://cdn.example.com/shopee-banner.jpg',
        ]);

        $html = $this->renderAd($ad, 'homepage', 'banner');

        $this->assertStringContainsString('hidden w-[42%] md:block', $html);
    }

    public function test_component_uses_reduced_motion_safe_transitions(): void
    {
        $ad = $this->livePartnerCampaign('dana', 'marketplace');

        $banner = $this->renderAd($ad, 'marketplace', 'banner');
        $card = $this->renderAd($ad, 'marketplace', 'card');

        $this->assertStringContainsString('motion-safe:group-hover:translate-x-1', $banner);
        $this->assertStringContainsString('motion-safe:group-hover:translate-x-0.5', $card);
    }

    // ---------------------------------------------------------------------
    // Integrasi halaman & tracking klik
    // ---------------------------------------------------------------------

    public function test_dashboard_marketplace_detail_render_premium_sections(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $homepage = $this->livePartnerCampaign('shopee', 'homepage', [
            'headline' => 'Lengkapi kebutuhan kos tanpa bikin kantong boncos.',
        ]);
        $marketplace = $this->livePartnerCampaign('dana', 'marketplace', [
            'headline' => 'Bayar kebutuhan harian jadi lebih praktis.',
        ]);
        $detail = $this->livePartnerCampaign('gopay', 'detail', [
            'headline' => 'Urusan sehari-hari, jadi lebih praktis.',
        ]);

        $this->actingAs($tenant)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Promo &amp; Partner Pilihan', escape: false)
            ->assertSee('Temukan penawaran menarik untuk menemani kebutuhan harianmu.')
            ->assertSee('Lengkapi kebutuhan kos tanpa bikin kantong boncos.');

        $this->actingAs($tenant)
            ->get(route('tenant.kos.index'))
            ->assertOk()
            ->assertSee('Bayar kebutuhan harian jadi lebih praktis.');

        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $this->actingAs($tenant)
            ->get(route('tenant.kos.show', $kos))
            ->assertOk()
            ->assertSee('Urusan sehari-hari, jadi lebih praktis.');
    }

    public function test_clicking_rendered_ad_records_event_and_redirects_to_destination(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $ad = $this->livePartnerCampaign('dana', 'marketplace', [
            'destination_url' => 'https://destination.example/dana',
        ]);

        $html = $this->renderAd($ad, 'marketplace', 'banner');
        $this->assertStringContainsString('/tenant/ad/click/'.$ad->id.'?placement=marketplace', $html);

        $this->actingAs($tenant)
            ->get(route('tenant.ad.click', ['campaign' => $ad->id, 'placement' => 'marketplace']))
            ->assertRedirect('https://destination.example/dana');

        $this->assertDatabaseHas('advertising_events', [
            'campaign_id' => $ad->id,
            'type' => 'click',
            'placement' => 'marketplace',
            'user_id' => $tenant->id,
        ]);
    }

    // ---------------------------------------------------------------------
    // Regresi browser QA — markup carousel & accordion filter fasilitas (JS
    // Alpine harus tetap inisialisasi, bukan hanya teks yang tampil).
    // ---------------------------------------------------------------------

    public function test_dashboard_promo_carousel_has_alpine_root_scope(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $this->livePartnerCampaign('shopee', 'homepage', [
            'headline' => 'Lengkapi kebutuhan kos tanpa bikin kantong boncos.',
        ]);
        $this->livePartnerCampaign('dana', 'homepage');
        $this->livePartnerCampaign('gopay', 'homepage');

        $html = $this->actingAs($tenant)
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        // Pembuka <div x-data> WAJIB ada sebagai elemen HTML (bukan teks
        // yatim) — diregresi ketika mark ju carousel dilepas dari elemen.
        $this->assertStringContainsString('x-data="{', $html);
        $this->assertStringContainsString('go(i) { this.index = Math.max(0, Math.min(this.total - 1, i))', $html);
        $this->assertStringContainsString('@touchstart.passive="onTouchStart($event)"', $html);

        // Tombol navigasi & dot dijalankan oleh Alpine (bukan statis).
        $this->assertStringContainsString('aria-label="Slide berikutnya"', $html);
        $this->assertStringContainsString('@click="go(', $html);
    }

    public function test_marketplace_facility_accordion_xdata_uses_quoted_json_values(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        Fasilitas::factory()->create(['name' => 'AC']);
        Fasilitas::factory()->create(['name' => 'Shower']);

        $html = $this->actingAs($tenant)
            ->get(route('tenant.kos.index'))
            ->assertOk()
            ->getContent();

        // x-data yang DI-RENDAR dengan benar (Blade mem-html-encode quote agar
        // tidak memutus atribut HTML — browser meng-decoding kembali ke ').
        $this->assertStringContainsString("{ openFacility: 'room' }", html_entity_decode($html));
        $this->assertStringContainsString('openFacility: &#039;room&#039;', $html);

        // Tanda kerusakan lama: atribut terpotong karena double-quote (@json).
        $this->assertStringNotContainsString('openFacility: "', $html);
    }
}
