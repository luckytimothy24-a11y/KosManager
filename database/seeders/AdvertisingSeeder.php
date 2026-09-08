<?php

namespace Database\Seeders;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingOrder;
use App\Models\AdvertisingPackage;
use App\Models\Kos;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AdvertisingSeeder extends Seeder
{
    /**
     * Seed data advertising demo (KHUSUS DEVELOPMENT).
     *
     * 1. Paket iklan (legacy promosi kos + paket advertiser pihak ketiga dengan
     *    placement). Harga/placement SELALU diambil dari database, tidak pernah
     *    di-hardcode di blade.
     * 2. Kampanye promosi kos owner (demo) yang sedang live (bila kos ada) —
     *    mempertahankan fitur advertising lama yang sudah ada. Order-nya PAID
     *    (mencontoh alur owner yang membayar).
     * 3. Kampanye ADVERTISER PIHAK KETIGA demo (kos_id NULL):
     *     - AD-PARTNER-1 Demo Partner WiFi (marketplace)
     *     - AD-PARTNER-2 Demo Laundry (homepage)
     *     - AD-PARTNER-3 Demo Furniture (detail)
     *     - AD-PARTNER-4 Demo Jasa Pindahan (native)
     *    Campaign DEMO ini berstatus PENDING_PAYMENT (belum live) dengan order
     *    PENDING (piutang) — konsisten dengan aturan M3: kampanye pihak ketiga
     *    hanya live setelah dana diterima, dan revenue hanya dihitung setelah
     *    order di-mark paid.
     *
     * Semua nama advertiser bersifat fiktif/"Demo" — tidak mengklaim kemitraan
     * dengan pihak nyata mana pun.
     *
     * PERINGATAN: seeder ini TIDAK boleh dijalankan di environment yang
     * dipakai untuk laporan revenue (staging/production) — data demo AD-DEMO-*
     * tetap memuat order paid yang akan menggelembungkan angka revenue.
     *
     * Idempotent: setiap item di-seed hanya bila belum ada (guard per kode/campaign_number).
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException(
                'AdvertisingSeeder hanya memuat data demo development (termasuk order PAID AD-DEMO-*). '.
                'Dilarang dijalankan di environment production — gunakan `php artisan db:seed` (DatabaseSeeder) yang '.
                'secara otomatis mengecualikan seeder ini di production.'
            );
        }

        $packages = [
            [
                'code' => 'FEATURED',
                'name' => 'Paket Featured',
                'description' => 'Promosi kos legacy: label Featured, boost prioritas, dan ikut ditampilkan di homepage.',
                'price' => 150000,
                'duration_days' => 30,
                'is_featured' => true,
                'is_sponsored' => false,
                'is_homepage' => true,
                'placement' => 'marketplace',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'SPONSORED',
                'name' => 'Paket Sponsored',
                'description' => 'Promosi kos legacy: boost dengan label Sponsored di marketplace.',
                'price' => 75000,
                'duration_days' => 30,
                'is_featured' => false,
                'is_sponsored' => true,
                'is_homepage' => false,
                'placement' => 'marketplace',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'HOMEPAGE',
                'name' => 'Paket Homepage',
                'description' => 'Promosi kos legacy: ditampilkan sebagai banner di beranda tenant.',
                'price' => 200000,
                'duration_days' => 30,
                'is_featured' => true,
                'is_sponsored' => true,
                'is_homepage' => true,
                'placement' => 'homepage',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'code' => 'MARKETPLACE_BANNER',
                'name' => 'Marketplace Banner',
                'description' => 'Iklan advertiser pihak ketiga di halaman daftar kos (marketplace).',
                'price' => 500000,
                'duration_days' => 7,
                'is_featured' => false,
                'is_sponsored' => true,
                'is_homepage' => false,
                'placement' => 'marketplace',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'code' => 'HOMEPAGE_BANNER',
                'name' => 'Homepage Banner',
                'description' => 'Iklan advertiser pihak ketiga di dashboard/beranda tenant.',
                'price' => 1000000,
                'duration_days' => 7,
                'is_featured' => false,
                'is_sponsored' => true,
                'is_homepage' => true,
                'placement' => 'homepage',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'code' => 'NATIVE_AD',
                'name' => 'Native Advertisement',
                'description' => 'Iklan native advertiser pihak ketiga yang menyatu halus dengan konten.',
                'price' => 750000,
                'duration_days' => 7,
                'is_featured' => false,
                'is_sponsored' => true,
                'is_homepage' => false,
                'placement' => 'native',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        $byCode = [];

        foreach ($packages as $p) {
            $pkg = AdvertisingPackage::where('code', $p['code'])->first();
            if (! $pkg) {
                $pkg = AdvertisingPackage::create($p);
            } else {
                $pkg->update([
                    'is_active' => true,
                    'placement' => $p['placement'],
                ]);
            }

            $byCode[$p['code']] = $pkg;
        }

        $owner = User::where('email', 'owner@example.com')->first();
        $secondOwner = User::where('email', 'gnamaga@example.com')->first() ?? $owner;
        $superAdmin = User::where('role', 'super_admin')->first() ?? User::first();

        if (! $owner) {
            return;
        }

        $now = now();

        // --------------------------------------------------------------------
        // Kampanye promosi kos owner demo (legacy) — hanya bila kos tersedia.
        // --------------------------------------------------------------------
        $kosFeatured = Kos::where('name', 'Kos Melati')->where('owner_id', $owner->id)->first();
        $kosSponsored = Kos::where('name', 'Kos Mawar')->where('owner_id', $owner->id)->first();
        $kosSecond = Kos::where('name', 'PT Wahyudin Utama')->where('owner_id', $secondOwner->id)->first();

        if ($kosFeatured) {
            $this->makeCampaign($owner, $superAdmin, $kosFeatured, $byCode['FEATURED'], [
                'campaign_number' => 'AD-DEMO-1',
                'is_featured' => true,
                'is_sponsored' => false,
                'is_homepage' => true,
                'start_days' => -10,
                'end_days' => 20,
            ], $now);
        }

        if ($kosSponsored) {
            $this->makeCampaign($owner, $superAdmin, $kosSponsored, $byCode['SPONSORED'], [
                'campaign_number' => 'AD-DEMO-2',
                'is_featured' => false,
                'is_sponsored' => true,
                'is_homepage' => false,
                'start_days' => -5,
                'end_days' => 25,
            ], $now);
        }

        if ($kosSecond) {
            $this->makeCampaign($secondOwner, $superAdmin, $kosSecond, $byCode['SPONSORED'], [
                'campaign_number' => 'AD-DEMO-4',
                'is_featured' => false,
                'is_sponsored' => true,
                'is_homepage' => false,
                'start_days' => -2,
                'end_days' => 28,
            ], $now);
        }

        // --------------------------------------------------------------------
        // Kampanye advertiser PIHAK KETIGA demo (kos_id NULL, placement jelas).
        // Setiap kampanye DIKAITKAN ke entitas Partner (mitra monetisasi) bila
        // tersedia — DANA, Shopee, GoPay — dengan metadata kontrak.
        // --------------------------------------------------------------------
        $partners = Partner::whereIn('slug', ['dana', 'shopee', 'gopay'])->get()->keyBy('slug');

        $partnerCampaigns = [
            [
                'campaign_number' => 'AD-PARTNER-1',
                'advertiser_name' => 'Demo Partner WiFi',
                'headline' => 'WiFi Cepat Tanpa Ribet untuk Anak Kos',
                'advertiser_description' => 'Paket internet khusus penghuni kos: pemasangan cepat, sinyal stabil, dan harga ramah mahasiswa.',
                'cta_label' => 'Cek Paket',
                'destination_url' => 'https://example.com/demo-wifi',
                'placement' => 'marketplace',
                'package_code' => 'MARKETPLACE_BANNER',
                'partner_slug' => 'shopee',
            ],
            [
                'campaign_number' => 'AD-PARTNER-2',
                'advertiser_name' => 'Demo Laundry',
                'headline' => 'Laundry Antar Jemput, Bersih Harum Tiap Hari',
                'advertiser_description' => 'Layanan laundry antar-jemput ke kos Anda. Jaminan bebas kelilipan dan harga per kilogram.',
                'cta_label' => 'Coba Sekarang',
                'destination_url' => 'https://example.com/demo-laundry',
                'placement' => 'homepage',
                'package_code' => 'HOMEPAGE_BANNER',
                'partner_slug' => 'dana',
            ],
            [
                'campaign_number' => 'AD-PARTNER-3',
                'advertiser_name' => 'Demo Furniture',
                'headline' => 'Furnitur Hemat untuk Isi Kamar Kos',
                'advertiser_description' => 'Kasur, meja belajar, dan lemari ramah kantong mahasiswa. Gratis antar untuk area kampus.',
                'cta_label' => 'Lihat Katalog',
                'destination_url' => 'https://example.com/demo-furniture',
                'placement' => 'detail',
                'package_code' => 'MARKETPLACE_BANNER',
                'partner_slug' => 'shopee',
            ],
            [
                'campaign_number' => 'AD-PARTNER-4',
                'advertiser_name' => 'Demo Jasa Pindahan',
                'headline' => 'Pindahan Kos Jadi Mudah & Cepat',
                'advertiser_description' => 'Jasa pindahan kos dengan harga tetap dan tanpa repot. Tenaga berpengalaman untuk barang aman sampai tujuan.',
                'cta_label' => 'Pesan Sekarang',
                'destination_url' => 'https://example.com/demo-pindahan',
                'placement' => 'native',
                'package_code' => 'NATIVE_AD',
                'partner_slug' => 'gopay',
            ],

            // --- Kampanye LIVE (active, sudah dibayar) untuk partner ternama ---
            // DANA: iklan cashless untuk bayar tagihan kos di marketplace.
            [
                'campaign_number' => 'AD-DANA-1',
                'advertiser_name' => 'DANA',
                'headline' => 'Bayar kebutuhan harian jadi lebih praktis.',
                'advertiser_description' => 'Temukan kemudahan transaksi digital untuk menemani aktivitas harianmu.',
                'cta_label' => 'Lihat Promo',
                'destination_url' => 'https://www.dana.id',
                'placement' => 'marketplace',
                'package_code' => 'MARKETPLACE_BANNER',
                'partner_slug' => 'dana',
                'live' => true,
            ],
            // Shopee: iklan marketplace untuk belanja kebutuhan kos (homepage).
            [
                'campaign_number' => 'AD-SHOPEE-1',
                'advertiser_name' => 'Shopee',
                'headline' => 'Lengkapi kebutuhan kos tanpa bikin kantong boncos.',
                'advertiser_description' => 'Mulai dari perlengkapan kamar sampai kebutuhan harian, temukan pilihan menarik untuk anak kos.',
                'cta_label' => 'Belanja Sekarang',
                'destination_url' => 'https://shopee.co.id',
                'placement' => 'homepage',
                'package_code' => 'HOMEPAGE_BANNER',
                'partner_slug' => 'shopee',
                'live' => true,
            ],
            // GoPay: iklan untuk pembayaran cepat di detail kos.
            [
                'campaign_number' => 'AD-GOPAY-1',
                'advertiser_name' => 'GoPay',
                'headline' => 'Urusan sehari-hari, jadi lebih praktis.',
                'advertiser_description' => 'Temukan kemudahan pembayaran digital untuk berbagai kebutuhanmu.',
                'cta_label' => 'Selengkapnya',
                'destination_url' => 'https://gopay.co.id',
                'placement' => 'detail',
                'package_code' => 'MARKETPLACE_BANNER',
                'partner_slug' => 'gopay',
                'live' => true,
            ],
            // GoPay: iklan tenant_dashboard — slot baru di dashboard tenant.
            [
                'campaign_number' => 'AD-GOPAY-2',
                'advertiser_name' => 'GoPay',
                'headline' => 'Urusan sehari-hari, jadi lebih praktis.',
                'advertiser_description' => 'Temukan kemudahan pembayaran digital untuk berbagai kebutuhanmu.',
                'cta_label' => 'Selengkapnya',
                'destination_url' => 'https://gopay.co.id/home',
                'placement' => 'tenant_dashboard',
                'package_code' => 'NATIVE_AD',
                'partner_slug' => 'gopay',
                'live' => true,
            ],
            // DANA: iklan cashless di carousel homepage (slide tambahan).
            [
                'campaign_number' => 'AD-DANA-2',
                'advertiser_name' => 'DANA',
                'headline' => 'Bayar tagihan kos langsung dari dompet digital.',
                'advertiser_description' => 'Bayar cepat, riwayat otomatis tercatat, dan aman.',
                'cta_label' => 'Coba Sekarang',
                'destination_url' => 'https://www.dana.id/promo',
                'placement' => 'homepage',
                'package_code' => 'HOMEPAGE_BANNER',
                'partner_slug' => 'dana',
                'live' => true,
            ],
            // Shopee: iklan belanja kebutuhan kos di marketplace (compact kedua).
            [
                'campaign_number' => 'AD-SHOPEE-2',
                'advertiser_name' => 'Shopee',
                'headline' => 'Perlengkapan kamar sampai kebutuhan harian tersedia.',
                'advertiser_description' => 'Gratis ongkir dan voucher khusus anak kos.',
                'cta_label' => 'Belanja Sekarang',
                'destination_url' => 'https://shopee.co.id/deals',
                'placement' => 'marketplace',
                'package_code' => 'MARKETPLACE_BANNER',
                'partner_slug' => 'shopee',
                'live' => true,
            ],
            // GoPay: iklan pembayaran cepat di carousel homepage (slide tambahan).
            [
                'campaign_number' => 'AD-GOPAY-3',
                'advertiser_name' => 'GoPay',
                'headline' => 'Isi saldo, bayar kebutuhan, semua di satu tempat.',
                'advertiser_description' => 'Pembayaran digital cepat tanpa ribet.',
                'cta_label' => 'Selengkapnya',
                'destination_url' => 'https://gopay.co.id',
                'placement' => 'homepage',
                'package_code' => 'HOMEPAGE_BANNER',
                'partner_slug' => 'gopay',
                'live' => true,
            ],
        ];

        foreach ($partnerCampaigns as $spec) {
            $this->makePartnerCampaign(
                $owner,
                $superAdmin,
                $byCode[$spec['package_code']],
                $spec,
                $now,
                $partners->get($spec['partner_slug'] ?? '')
            );
        }

        $this->command?->warn('AdvertisingSeeder: data demo advertising dibuat untuk DEVELOPMENT. JANGAN dijalankan di staging/production (AD-DEMO-* memuat order paid).');
    }

    private function makeCampaign(
        User $owner,
        ?User $approver,
        Kos $kos,
        AdvertisingPackage $package,
        array $spec,
        Carbon $now
    ): void {
        if (! $package || AdvertisingCampaign::where('campaign_number', $spec['campaign_number'])->exists()) {
            return;
        }

        $campaign = AdvertisingCampaign::create([
            'campaign_number' => $spec['campaign_number'],
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'status' => AdvertisingCampaign::STATUS_ACTIVE,
            'starts_at' => $now->copy()->addDays($spec['start_days']),
            'ends_at' => $now->copy()->addDays($spec['end_days']),
            'budget' => $package->price,
            'is_featured' => $spec['is_featured'],
            'is_sponsored' => $spec['is_sponsored'],
            'is_homepage' => $spec['is_homepage'],
            'placement' => $package->placement,
            'approved_by' => $approver?->id,
            'approved_at' => $now->copy()->subDays(12),
        ]);

        AdvertisingOrder::create([
            'order_number' => 'ORD-'.$spec['campaign_number'],
            'campaign_id' => $campaign->id,
            'owner_id' => $owner->id,
            'amount' => $package->price,
            'status' => 'paid',
            'paid_at' => $now->copy()->subDays(12),
        ]);
    }

    private function makePartnerCampaign(
        User $owner,
        ?User $approver,
        AdvertisingPackage $package,
        array $spec,
        Carbon $now,
        ?Partner $partner = null
    ): void {
        if (! $package || AdvertisingCampaign::where('campaign_number', $spec['campaign_number'])->exists()) {
            return;
        }

        $live = (bool) ($spec['live'] ?? false);

        $campaign = AdvertisingCampaign::create([
            'campaign_number' => $spec['campaign_number'],
            'owner_id' => $owner->id,
            'kos_id' => null,
            'partner_id' => $partner?->id,
            'package_id' => $package->id,
            'status' => $live ? AdvertisingCampaign::STATUS_ACTIVE : AdvertisingCampaign::STATUS_PENDING_PAYMENT,
            'starts_at' => $now->copy()->subDays(7),
            'ends_at' => $now->copy()->addDays(21),
            'budget' => $package->price,
            'is_featured' => false,
            'is_sponsored' => true,
            'is_homepage' => $spec['placement'] === 'homepage',
            'advertiser_name' => $spec['advertiser_name'],
            'advertiser_description' => $spec['advertiser_description'],
            'headline' => $spec['headline'],
            'cta_label' => $spec['cta_label'],
            'destination_url' => $spec['destination_url'],
            'placement' => $spec['placement'],
            'contract_reference' => 'KTR-'.$spec['campaign_number'],
            'campaign_code' => $spec['campaign_number'],
            'monetization_type' => $partner?->monetization_type ?? 'deal',
            'target_audience' => 'Mahasiswa / penghuni kos',
            'approved_by' => $live ? $approver?->id : null,
            'approved_at' => $live ? $now->copy()->subDays(7) : null,
        ]);

        // Order pihak ketiga: PENDING (piutang) untuk kampanye belum dibayar;
        // PAID untuk kampanye LIVE (konsisten dengan aturan ledger: revenue
        // hanya dihitung setelah dana diterima).
        AdvertisingOrder::create([
            'order_number' => 'ORD-'.$spec['campaign_number'],
            'campaign_id' => $campaign->id,
            'owner_id' => $owner->id,
            'amount' => $package->price,
            'status' => $live ? 'paid' : 'pending',
            'paid_at' => $live ? $now->copy()->subDays(7) : null,
        ]);
    }
}
