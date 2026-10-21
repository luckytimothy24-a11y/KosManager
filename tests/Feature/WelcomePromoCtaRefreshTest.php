<?php

namespace Tests\Feature;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingPackage;
use App\Models\Kos;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomePromoCtaRefreshTest extends TestCase
{
    use RefreshDatabase;

    private function makePartnerAd(string $slug, string $name): AdvertisingCampaign
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $partner = Partner::factory()->create(['slug' => $slug, 'name' => $name]);
        $package = AdvertisingPackage::factory()->create(['placement' => 'homepage']);

        return AdvertisingCampaign::factory()
            ->active()
            ->thirdParty('homepage')
            ->create([
                'owner_id' => $owner->id,
                'package_id' => $package->id,
                'partner_id' => $partner->id,
                'advertiser_name' => $name,
                'headline' => "Promo khusus dari {$name}",
                'cta_label' => 'Lihat Promo',
            ]);
    }

    public function test_welcome_renders_universal_badges_and_brand_partner_cards(): void
    {
        $this->makePartnerAd('shopee', 'Shopee');
        $this->makePartnerAd('dana', 'DANA');
        $this->makePartnerAd('gopay', 'GoPay');

        $owner = User::factory()->create(['role' => 'owner']);
        Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);

        $html = $this->get(route('welcome'))
            ->assertOk()
            ->getContent();

        // Universal CTA badges (gradient merah–oranye)
        $this->assertStringContainsString('Promo Terbatas', $html);
        $this->assertStringContainsString('Kos Populer', $html);
        $this->assertStringContainsString('Untuk Anda', $html);
        $this->assertStringContainsString('Untuk Pemilik Kos', $html);
        $this->assertGreaterThanOrEqual(4, substr_count($html, 'from-red-500'), 'Minimal 4 badge universal merah–oranye');

        // Brand-colored partner cards
        $this->assertStringContainsString('from-[#fff3ee]', $html, 'Shopee wash');
        $this->assertStringContainsString('from-[#eaf4ff]', $html, 'DANA wash');
        $this->assertStringContainsString('from-[#eefbf1]', $html, 'GoPay wash');

        // Role-aware CTA tetap ada
        $this->assertStringContainsString('Daftar sebagai Penghuni', $html);
        $this->assertStringContainsString('Daftar Gratis', $html);
        $this->assertStringContainsString('Lihat Kos', $html);

        // Tracking & carousel tetap
        $this->assertStringContainsString('/ad/click/', $html);
        $this->assertStringContainsString('data-ad-id=', $html);
        $this->assertStringContainsString('x-data="{', $html);
        $this->assertStringContainsString('Sponsored', $html);

        // Label lama diganti
        $this->assertStringNotContainsString('Promo Spesial', $html);
    }

    public function test_partner_brand_themes_are_distinct(): void
    {
        $this->makePartnerAd('shopee', 'Shopee');
        $this->makePartnerAd('dana', 'DANA');

        $html = $this->get(route('welcome'))->getContent();

        $this->assertStringContainsString('from-[#fff3ee]', $html);
        $this->assertStringContainsString('from-[#eaf4ff]', $html);
        $this->assertStringNotContainsString('from-[#eefbf1]', $html);
    }

    public function test_unknown_partner_falls_back_to_default_theme(): void
    {
        $this->makePartnerAd('mitra-baru', 'Mitra Baru');

        $html = $this->get(route('welcome'))->getContent();

        $this->assertStringContainsString('Mitra Baru', $html);
        $this->assertStringNotContainsString('from-[#fff3ee]', $html);
    }

    public function test_tenant_sees_marketplace_cta_in_mid_band(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $this->makePartnerAd('shopee', 'Shopee');

        $html = $this->actingAs($tenant)->get(route('welcome'))->getContent();

        $this->assertStringContainsString('Jelajahi Kos', $html);
        $this->assertStringContainsString('Untuk Anda', $html);
        $this->assertStringContainsString('/tenant/kos', $html);
    }
}
