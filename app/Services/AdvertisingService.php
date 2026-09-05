<?php

namespace App\Services;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingEvent;
use App\Models\AdvertisingOrder;
use App\Models\AdvertisingPackage;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdvertisingService
{
    /**
     * Buat campaign advertising untuk kos milik owner.
     *
     * Harga & kemampuan diambil dari package di server (TIDAK pernah percaya
     * client). Hanya kos milik owner sendiri yang boleh diiklankan.
     *
     * @param  array<string, mixed>  $data  [kos_id, package_id]
     * @return array{ok: bool, error?: string, message?: string, campaign?: AdvertisingCampaign}
     */
    public function create(User $owner, array $data): array
    {
        $kos = Kos::whereKey($data['kos_id'])->first();
        $package = AdvertisingPackage::whereKey($data['package_id'])->where('is_active', true)->first();

        if (! $kos || (int) $kos->owner_id !== (int) $owner->id) {
            return ['ok' => false, 'error' => 'ownership', 'message' => 'Anda hanya dapat mempromosikan kos milik Anda sendiri.'];
        }

        if (! $package) {
            return ['ok' => false, 'error' => 'package', 'message' => 'Paket promosi tidak valid atau sudah tidak aktif.'];
        }

        if ($package->price <= 0) {
            return ['ok' => false, 'error' => 'price', 'message' => 'Paket promosi tidak valid.'];
        }

        $campaign = AdvertisingCampaign::create([
            'campaign_number' => 'AD'.strtoupper(Str::random(8)),
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'status' => AdvertisingCampaign::STATUS_PENDING_PAYMENT,
            'starts_at' => $data['starts_at'] ?? now(),
            'ends_at' => ($data['starts_at'] ?? now())->copy()->addDays((int) $package->duration_days),
            'budget' => $package->price,
            'is_featured' => $package->is_featured,
            'is_sponsored' => $package->is_sponsored,
            'is_homepage' => $package->is_homepage,
            'placement' => $package->placement,
        ]);

        return ['ok' => true, 'campaign' => $campaign];
    }

    /**
     * Buat campaign ADVERTISER PIHAK KETIGA (tidak terkait kos tertentu).
     *
     * Harga & kemampuan diambil dari package di server. Identitas advertiser,
     * creative, CTA, dan destination URL wajib diisi; placement disalin dari
     * package (dapat di-override). kos_id KOSONG menjadi penanda pihak ketiga.
     *
     * @param  array<string, mixed>  $data  [package_id, advertiser_name, headline, advertiser_description, cta_label, destination_url, placement?]
     * @return array{ok: bool, error?: string, message?: string, campaign?: AdvertisingCampaign}
     */
    public function createThirdParty(User $owner, array $data): array
    {
        $package = AdvertisingPackage::whereKey(data_get($data, 'package_id'))
            ->where('is_active', true)
            ->first();

        if (! $package) {
            return ['ok' => false, 'error' => 'package', 'message' => 'Paket iklan tidak valid atau sudah tidak aktif.'];
        }

        if ($package->price <= 0) {
            return ['ok' => false, 'error' => 'price', 'message' => 'Paket iklan tidak valid.'];
        }

        $placement = data_get($data, 'placement') ?: $package->placement;

        if (! in_array($placement, AdvertisingCampaign::PLACEMENTS, true)) {
            return ['ok' => false, 'error' => 'placement', 'message' => 'Placement iklan tidak valid.'];
        }

        $destinationUrl = trim((string) data_get($data, 'destination_url', ''));
        $scheme = strtolower((string) parse_url($destinationUrl, PHP_URL_SCHEME));

        if ($destinationUrl === '' || ! in_array($scheme, ['http', 'https'], true)) {
            return ['ok' => false, 'error' => 'destination', 'message' => 'Destination URL harus berupa tautan http/https yang valid.'];
        }

        $campaign = AdvertisingCampaign::create([
            'campaign_number' => 'AD'.strtoupper(Str::random(8)),
            'owner_id' => $owner->id,
            'kos_id' => null,
            'package_id' => $package->id,
            'status' => AdvertisingCampaign::STATUS_PENDING_PAYMENT,
            'starts_at' => $data['starts_at'] ?? now(),
            'ends_at' => ($data['starts_at'] ?? now())->copy()->addDays((int) $package->duration_days),
            'budget' => $package->price,
            'is_featured' => $package->is_featured,
            'is_sponsored' => $package->is_sponsored,
            'is_homepage' => $package->is_homepage,
            'advertiser_name' => trim((string) data_get($data, 'advertiser_name')),
            'advertiser_logo' => data_get($data, 'advertiser_logo') ?: null,
            'advertiser_description' => data_get($data, 'advertiser_description') ?: null,
            'headline' => trim((string) data_get($data, 'headline')),
            'description' => data_get($data, 'description') ?: null,
            'image' => data_get($data, 'image') ?: null,
            'cta_label' => trim((string) data_get($data, 'cta_label')),
            'destination_url' => $destinationUrl,
            'placement' => $placement,
        ]);

        return ['ok' => true, 'campaign' => $campaign];
    }

    /**
     * Simulasikan pembayaran advertising: buat order berstatus paid dengan
     * nominal server-side dari package. Idempotent — bila order paid sudah ada,
     * tidak membuat duplikat. mentransisikan pending_payment -> pending_review.
     *
     * @return array{ok: bool, error?: string, message?: string, order?: AdvertisingOrder, campaign?: AdvertisingCampaign}
     */
    public function pay(AdvertisingCampaign $campaign): array
    {
        if ($campaign->status !== AdvertisingCampaign::STATUS_PENDING_PAYMENT) {
            return ['ok' => false, 'error' => 'invalid_state', 'message' => 'Campaign tidak dalam status menunggu pembayaran.'];
        }

        $existingOrder = AdvertisingOrder::where('campaign_id', $campaign->id)
            ->where('status', AdvertisingOrder::STATUS_PAID)
            ->first();

        $order = $existingOrder;

        if (! $order) {
            $order = \DB::transaction(function () use ($campaign) {
                $locked = AdvertisingCampaign::whereKey($campaign->id)->lockForUpdate()->firstOrFail();

                if ($locked->status !== AdvertisingCampaign::STATUS_PENDING_PAYMENT) {
                    return null;
                }

                $order = AdvertisingOrder::create([
                    'order_number' => 'AO'.strtoupper(Str::random(12)),
                    'campaign_id' => $locked->id,
                    'owner_id' => $locked->owner_id,
                    'amount' => $locked->budget,
                    'status' => AdvertisingOrder::STATUS_PAID,
                    'paid_at' => now(),
                ]);

                $locked->update(['status' => AdvertisingCampaign::STATUS_PENDING_REVIEW]);

                return $order;
            });

            if (! $order) {
                return ['ok' => false, 'error' => 'invalid_state', 'message' => 'Campaign tidak dapat diproses.'];
            }
        } else {
            $campaign->update(['status' => AdvertisingCampaign::STATUS_PENDING_REVIEW]);
        }

        return ['ok' => true, 'order' => $order, 'campaign' => $campaign->fresh()];
    }

    /**
     * Approve kampanye oleh admin/super admin (state pending_review).
     */
    public function approve(AdvertisingCampaign $campaign, User $moderator): bool
    {
        if ($campaign->status !== AdvertisingCampaign::STATUS_PENDING_REVIEW) {
            return false;
        }

        $updates = [
            'approved_by' => $moderator->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ];

        $now = now();

        if ($campaign->ends_at && $campaign->ends_at < $now) {
            $updates['status'] = AdvertisingCampaign::STATUS_COMPLETED;
        } elseif ($campaign->starts_at && $campaign->starts_at > $now) {
            $updates['status'] = AdvertisingCampaign::STATUS_APPROVED;
        } else {
            $updates['status'] = AdvertisingCampaign::STATUS_ACTIVE;
        }

        $campaign->update($updates);

        return true;
    }

    public function reject(AdvertisingCampaign $campaign, string $reason): bool
    {
        if ($campaign->status !== AdvertisingCampaign::STATUS_PENDING_REVIEW) {
            return false;
        }

        $campaign->update([
            'status' => AdvertisingCampaign::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    public function suspend(AdvertisingCampaign $campaign, string $reason): bool
    {
        if ($campaign->status !== AdvertisingCampaign::STATUS_ACTIVE) {
            return false;
        }

        $campaign->update([
            'status' => AdvertisingCampaign::STATUS_SUSPENDED,
            'suspension_reason' => $reason,
        ]);

        return true;
    }

    public function cancel(AdvertisingCampaign $campaign): bool
    {
        if (! in_array($campaign->status, [
            AdvertisingCampaign::STATUS_PENDING_PAYMENT,
            AdvertisingCampaign::STATUS_PAID,
            AdvertisingCampaign::STATUS_PENDING_REVIEW,
        ], true)) {
            return false;
        }

        $campaign->update(['status' => AdvertisingCampaign::STATUS_CANCELLED]);

        return true;
    }

    /**
     * Aktifkan campaign approved yang sudah mencapai starts_at (dari scheduler).
     */
    public function activateEligible(): int
    {
        $now = now();

        return AdvertisingCampaign::where('status', AdvertisingCampaign::STATUS_APPROVED)
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', $now)
            ->whereNotNull('ends_at')
            ->where('ends_at', '>', $now)
            ->update(['status' => AdvertisingCampaign::STATUS_ACTIVE]);
    }

    /**
     * Selesaikan campaign aktif yang sudah melewati ends_at (dari scheduler).
     */
    public function completeExpired(): int
    {
        $now = now();

        return AdvertisingCampaign::whereIn('status', [
            AdvertisingCampaign::STATUS_ACTIVE,
            AdvertisingCampaign::STATUS_APPROVED,
        ])->whereNotNull('ends_at')->where('ends_at', '<=', $now)
            ->update(['status' => AdvertisingCampaign::STATUS_COMPLETED]);
    }

    /**
     * Campaign aktif yang sedang berjalan (untuk marketplace / dashboard).
     */
    public function activeCampaigns(?array $kosIds = null)
    {
        $query = AdvertisingCampaign::where('status', AdvertisingCampaign::STATUS_ACTIVE)
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', now())
            ->whereNotNull('ends_at')
            ->where('ends_at', '>', now());

        if ($kosIds !== null) {
            $query->whereIn('kos_id', $kosIds);
        }

        return $query;
    }

    /**
     * Kampanye promosi kos owner (kos_id TIDAK NULL) yang sedang live.
     *
     * Marketplace: kampanye is_featured ATAU is_sponsored.
     * Homepage:    kampanye is_homepage.
     *
     * Bila $kosIds diberikan, hanya kos yang ada dalam daftar tersebut yang
     * boleh tampil — digunakan agar promosi kos SELALU menghormati pencarian /
     * filter harga / fasilitas / ketersediaan yang sedang aktif di marketplace
     * (kos di luar filter tidak pernah muncul di section promosi).
     *
     * @param  array<int, int>  $kosIds
     * @return Collection<int, AdvertisingCampaign>
     */
    public function kosPromoCampaigns(string $placement, array $kosIds = [], int $limit = 4)
    {
        $query = $this->activeCampaigns()
            ->whereNotNull('kos_id');

        if ($placement === AdvertisingCampaign::PLACEMENT_HOMEPAGE) {
            $query->where('is_homepage', true);
        } else {
            $query->where(fn ($q) => $q->where('is_featured', true)->orWhere('is_sponsored', true));
        }

        if ($kosIds !== []) {
            $query->whereIn('kos_id', $kosIds);
        }

        return $query->orderByDesc('is_featured')
            ->orderByDesc('is_sponsored')
            ->orderBy('id')
            ->limit($limit)
            ->with(['kos' => function ($q) {
                $q->where('status', 'active')
                    ->withCount(['kamar as kamar_tersedia' => fn ($k) => $k->where('status', 'available')])
                    ->withMin(['kamar as harga_mulai' => fn ($k) => $k->where('status', 'available')], 'monthly_price')
                    ->withMin(['kamar as harga_harian_mulai' => fn ($k) => $k->where('status', 'available')->whereNotNull('daily_price')], 'daily_price')
                    ->with(['kamar' => fn ($k) => $k->where('status', 'available')->with('fasilitas')->limit(1), 'fasilitas' => fn ($f) => $f->active()]);
            }])
            ->get()
            ->filter(fn ($c) => $c->kos && $c->kos->exists)
            ->values();
    }

    /**
     * Buat campaign ADVERTISER PIHAK KETIGA oleh moderator (admin/super admin).
     *
     * Tidak ada simulasi pembayaran: moderator membuat kampanye atas nama
     * platform sehingga langsung berstatus active (atau approved bila jadwal
     * masih di masa depan). Dicatat sebagai approved oleh moderator.
     *
     * @param  array<string, mixed>  $data  [package_id, advertiser_name, headline, advertiser_description, cta_label, destination_url, placement?, starts_at?]
     * @return array{ok: bool, error?: string, message?: string, campaign?: AdvertisingCampaign}
     */
    public function createThirdPartyByModerator(User $moderator, array $data): array
    {
        $package = AdvertisingPackage::whereKey(data_get($data, 'package_id'))
            ->where('is_active', true)
            ->first();

        if (! $package) {
            return ['ok' => false, 'error' => 'package', 'message' => 'Paket iklan tidak valid atau sudah tidak aktif.'];
        }

        if ($package->price <= 0) {
            return ['ok' => false, 'error' => 'price', 'message' => 'Paket iklan tidak valid.'];
        }

        $placement = data_get($data, 'placement') ?: $package->placement;

        if (! in_array($placement, AdvertisingCampaign::PLACEMENTS, true)) {
            return ['ok' => false, 'error' => 'placement', 'message' => 'Placement iklan tidak valid.'];
        }

        $destinationUrl = trim((string) data_get($data, 'destination_url', ''));
        $scheme = strtolower((string) parse_url($destinationUrl, PHP_URL_SCHEME));

        if ($destinationUrl === '' || ! in_array($scheme, ['http', 'https'], true)) {
            return ['ok' => false, 'error' => 'destination', 'message' => 'Destination URL harus berupa tautan http/https yang valid.'];
        }

        $startsAt = data_get($data, 'starts_at') ? now()->parse($data['starts_at']) : now();
        $endsAt = $startsAt->copy()->addDays((int) $package->duration_days);
        $now = now();

        $campaign = \DB::transaction(function () use ($moderator, $package, $placement, $destinationUrl, $startsAt, $endsAt, $now, $data) {
            $campaign = AdvertisingCampaign::create([
                'campaign_number' => 'AD'.strtoupper(Str::random(8)),
                'owner_id' => $moderator->id,
                'kos_id' => null,
                'package_id' => $package->id,
                'status' => $startsAt > $now
                    ? AdvertisingCampaign::STATUS_APPROVED
                    : AdvertisingCampaign::STATUS_ACTIVE,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'budget' => $package->price,
                'is_featured' => $package->is_featured,
                'is_sponsored' => $package->is_sponsored,
                'is_homepage' => $package->is_homepage,
                'advertiser_name' => trim((string) data_get($data, 'advertiser_name')),
                'advertiser_logo' => data_get($data, 'advertiser_logo') ?: null,
                'advertiser_description' => data_get($data, 'advertiser_description') ?: null,
                'headline' => trim((string) data_get($data, 'headline')),
                'description' => data_get($data, 'description') ?: null,
                'image' => data_get($data, 'image') ?: null,
                'cta_label' => trim((string) data_get($data, 'cta_label')),
                'destination_url' => $destinationUrl,
                'placement' => $placement,
                'approved_by' => $moderator->id,
                'approved_at' => $now,
            ]);

            return $campaign;
        });

        return ['ok' => true, 'campaign' => $campaign];
    }

    /**
     * Perbarui creative/CTA/placement dari kampanye advertiser pihak ketiga.
     * Hanya data yang diizinkan yang diubah — tidak pernah menyentuh status,
     * periode, paket, atau kos_id (triwulan tetap dari data awal).
     *
     * @param  array<string, mixed>  $data
     */
    public function updateThirdParty(AdvertisingCampaign $campaign, array $data): bool
    {
        if ($campaign->kos_id !== null) {
            return false;
        }

        $placement = data_get($data, 'placement') ?: $campaign->placement;

        if (! in_array($placement, AdvertisingCampaign::PLACEMENTS, true)) {
            return false;
        }

        $destinationUrl = trim((string) data_get($data, 'destination_url', $campaign->destination_url));
        $scheme = strtolower((string) parse_url($destinationUrl, PHP_URL_SCHEME));

        if ($destinationUrl === '' || ! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $campaign->update([
            'advertiser_name' => trim((string) data_get($data, 'advertiser_name', $campaign->advertiser_name)),
            'advertiser_logo' => data_get($data, 'advertiser_logo') ?: $campaign->advertiser_logo,
            'advertiser_description' => data_get($data, 'advertiser_description') ?: $campaign->advertiser_description,
            'headline' => trim((string) data_get($data, 'headline', $campaign->headline)),
            'description' => data_get($data, 'description') ?: $campaign->description,
            'image' => data_get($data, 'image') ?: $campaign->image,
            'cta_label' => data_get($data, 'cta_label') ?: $campaign->cta_label,
            'destination_url' => $destinationUrl,
            'placement' => $placement,
        ]);

        return true;
    }

    /**
     * Catat event tracking (impression/click/conversion) dengan deduplikasi
     * reasonable untuk impression guna mencegah spam refresh/render berulang.
     */
    public function trackEvent(
        int $campaignId,
        string $type,
        ?int $userId,
        ?string $sessionKey,
        ?string $placement = null,
        bool $deduplicate = false
    ): bool {
        if (in_array($type, [AdvertisingEvent::TYPE_IMPRESSION, AdvertisingEvent::TYPE_CONVERSION], true) && $deduplicate) {
            $query = AdvertisingEvent::where('campaign_id', $campaignId)
                ->where('type', $type)
                ->where('created_at', '>=', now()->subMinutes(15));

            if ($userId !== null) {
                $query->where('user_id', $userId);
            }
            if ($sessionKey !== null) {
                $query->where('session_key', $sessionKey);
            }

            if ($query->exists()) {
                return false;
            }
        }

        AdvertisingEvent::create([
            'campaign_id' => $campaignId,
            'type' => $type,
            'user_id' => $userId,
            'session_key' => $sessionKey,
            'placement' => $placement,
            'created_at' => now(),
        ]);

        return true;
    }

    /**
     * Kampanye PIHAK KETIGA yang sedang live pada placement tertentu.
     *
     * Hanya campaign berstatus active, dalam rentang waktu tayang, berkos_id
     * NULL, memiliki identitas advertiser, dan placement cocok. Dirotasi adil
     * antar advertiser agar tidak ada advertiser yang selalu tampil pertama.
     */
    public function partnerAds(string $placement, int $limit = 4)
    {
        $campaigns = $this->activeCampaigns()
            ->whereNull('kos_id')
            ->whereNotNull('advertiser_name')
            ->where('placement', $placement)
            ->orderBy('id')
            ->with(['package', 'owner'])
            ->get();

        return $this->fairRotate($campaigns, 'owner_id', (int) now()->format('Ymd'))
            ->take($limit)
            ->values();
    }

    /**
     * Rotasi adil (fair rotation): campaign dirotasi berdasarkan key tertentu
     * (biasanya owner_id / advertiser) dengan seed deterministik per tanggal.
     * Setiap advertiser mendapat giliran pertama sebelum advertiser lain
     * mendapat giliran kedua (round-robin). Hasil konsisten dalam satu hari.
     *
     * @param  Collection<int, AdvertisingCampaign>  $campaigns
     */
    public function fairRotate($campaigns, string $key = 'owner_id', ?int $seed = null)
    {
        if ($campaigns->isEmpty()) {
            return $campaigns;
        }

        $daySeed = $seed ?? (int) now()->format('Ymd');
        $rounds = [];

        foreach ($campaigns as $campaign) {
            $k = (string) data_get($campaign, $key);
            $rounds[$k][] = $campaign;
        }

        $keys = array_keys($rounds);

        $previousSeed = mt_srand();
        mt_srand($daySeed);
        shuffle($keys);
        mt_srand($previousSeed);

        $rotated = collect();

        $hasLeft = true;
        while ($hasLeft) {
            $hasLeft = false;
            foreach ($keys as $k) {
                if ($rounds[$k] !== []) {
                    $rotated->push(array_shift($rounds[$k]));
                    $hasLeft = true;
                }
            }
        }

        return $rotated;
    }

    /**
     * Destination URL untuk campaign pihak ketiga yang valid (http/https).
     * Mengembalikan null untuk campaign promosi kos.
     */
    public function destinationFor(AdvertisingCampaign $campaign): ?string
    {
        if (! $campaign->isThirdParty()) {
            return null;
        }

        $url = trim((string) $campaign->destination_url);
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($url === '' || ! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        return $url;
    }

    /**
     * Atribusi conversion: bila tenant baru saja mengklik iklan untuk kos X lalu
     * berhasil membuat booking pada kos yang sama, catat event conversion.
     *
     * Hanya dipanggil dari flow booking yang nyata (bukan klaim tanpa bukti).
     */
    public function recordConversionIfApplicable(int $userId, int $kosId): void
    {
        $adClick = session()->pull('ad_click');

        if (! $adClick || (int) ($adClick['kos_id'] ?? 0) !== $kosId) {
            return;
        }

        $campaignId = (int) ($adClick['campaign_id'] ?? 0);

        if ($campaignId <= 0) {
            return;
        }

        $this->trackEvent($campaignId, 'conversion', $userId, session()->getId(), 'marketplace', true);
    }
}
