<?php

namespace Database\Factories;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingPackage;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdvertisingCampaign>
 */
class AdvertisingCampaignFactory extends Factory
{
    protected $model = AdvertisingCampaign::class;

    public function definition(): array
    {
        return [
            'campaign_number' => 'AD'.fake()->numerify('######'),
            'owner_id' => User::factory(),
            'kos_id' => Kos::factory(),
            'package_id' => AdvertisingPackage::factory(),
            'status' => AdvertisingCampaign::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(6),
            'budget' => 50000,
            'is_featured' => false,
            'is_sponsored' => true,
            'is_homepage' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => AdvertisingCampaign::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(6),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => [
            'status' => AdvertisingCampaign::STATUS_SUSPENDED,
            'suspension_reason' => 'Melanggar aturan platform',
        ]);
    }

    public function pendingReview(): static
    {
        return $this->state(fn () => ['status' => AdvertisingCampaign::STATUS_PENDING_REVIEW]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => AdvertisingCampaign::STATUS_REJECTED,
            'rejection_reason' => 'Kos tidak memenuhi syarat',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => AdvertisingCampaign::STATUS_COMPLETED,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDay(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => AdvertisingCampaign::STATUS_ACTIVE,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDay(),
        ]);
    }

    /**
     * Campaign advertiser PIHAK KETIGA (kos_id NULL) — tidak terkait kos tertentu.
     */
    public function thirdParty(string $placement = 'marketplace'): static
    {
        return $this->state(fn () => [
            'kos_id' => null,
            'advertiser_name' => 'Demo Partner',
            'advertiser_description' => 'Penawaran khusus untuk penghuni kos.',
            'headline' => 'Penawaran khusus untuk penghuni kos',
            'cta_label' => 'Lihat Penawaran',
            'destination_url' => 'https://example.com/demo-partner',
            'placement' => $placement,
            'is_featured' => false,
            'is_sponsored' => true,
            'is_homepage' => $placement === 'homepage',
        ]);
    }
}
