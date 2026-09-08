<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PartnerSeeder extends Seeder
{
    /**
     * Seed partner MITRA MONETISASI demo (KHUSUS DEVELOPMENT).
     *
     * Entitas partner (DANA, Shopee, GoPay) yang menjadi referensi di balik
     * kampanye iklan B2B. Semua status ACTIVE agar dapat dipilih saat membuat
     * kampanye pihak ketiga. Deskripsi bersifat fiktif/"Demo" — tidak mengklaim
     * kemitraan terdaftar dengan pihak nyata.
     *
     * Idempotent: hanya dibuat bila slug belum ada.
     */
    public function run(): void
    {
        $partners = [
            [
                'name' => 'DANA',
                'slug' => 'dana',
                'description' => 'Dompet digital untuk transaksi sehari-hari, termasuk pembayaran tagihan dari kos.',
                'website_url' => 'https://www.dana.id',
                'monetization_type' => 'cpc',
                'contact_name' => 'PIC DANA',
                'contact_email' => 'partnership@dana.example',
            ],
            [
                'name' => 'Shopee',
                'slug' => 'shopee',
                'description' => 'Platform e-commerce untuk belanja kebutuhan kos dan pembayaran digital.',
                'website_url' => 'https://shopee.co.id',
                'monetization_type' => 'deal',
                'contact_name' => 'PIC Shopee',
                'contact_email' => 'partnership@shopee.example',
            ],
            [
                'name' => 'GoPay',
                'slug' => 'gopay',
                'description' => 'Uang elektronik untuk pembayaran cepat, termasuk pembelian pulsa dan makanan.',
                'website_url' => 'https://gopay.co.id',
                'monetization_type' => 'cpm',
                'contact_name' => 'PIC GoPay',
                'contact_email' => 'partnership@gopay.example',
            ],
        ];

        foreach ($partners as $spec) {
            $slug = Str::slug(array_key_exists('slug', $spec) ? $spec['slug'] : $spec['name']);

            if (Partner::where('slug', $slug)->exists()) {
                continue;
            }

            Partner::create([
                'name' => $spec['name'],
                'slug' => $slug,
                'description' => $spec['description'],
                'website_url' => $spec['website_url'],
                'monetization_type' => $spec['monetization_type'],
                'contact_name' => $spec['contact_name'],
                'contact_email' => $spec['contact_email'],
                'status' => Partner::STATUS_ACTIVE,
            ]);
        }
    }
}
