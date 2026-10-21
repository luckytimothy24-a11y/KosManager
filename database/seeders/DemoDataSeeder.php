<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\DamageReport;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Data demo tambahan agar presentasi terlihat hidup.
 * Jalankan setelah semua seeder utama: php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedExtraTenants();
        $this->seedExtraOwnerData();
        $this->seedBookingVariety();
        $this->seedPayments();
        $this->seedDamageReports();
        $this->seedMessages();
    }

    private function seedExtraTenants(): void
    {
        $tenants = User::where('role', 'tenant')
            ->where('email', '!=', 'tenant@example.com')
            ->get();

        $kosList = Kos::where('status', 'active')->get();
        if ($kosList->isEmpty() || $tenants->isEmpty()) {
            return;
        }

        $fakerNames = ['Budi Santoso', 'Rina Wulandari', 'Ahmad Fauzi', 'Dewi Lestari', 'Rizky Pratama'];
        foreach ($tenants as $i => $tenant) {
            $name = $fakerNames[$i] ?? 'Tenant '.($i + 1);
            if ($tenant->name === $name || str_starts_with($tenant->name, 'Tenant')) {
                $tenant->update(['name' => $name]);
            }
        }

        $usedRooms = Booking::pluck('kamar_id')->toArray();

        foreach ($tenants->take(3) as $i => $tenant) {
            $kos = $kosList[$i % $kosList->count()];
            $availableRoom = Kamar::where('kos_id', $kos->id)
                ->where('status', 'available')
                ->whereNotIn('id', $usedRooms)
                ->first();

            if (! $availableRoom) {
                continue;
            }

            $booking = Booking::create([
                'booking_code' => 'BK'.str_pad(10 + $i, 6, '0', STR_PAD_LEFT),
                'user_id' => $tenant->id,
                'kos_id' => $kos->id,
                'kamar_id' => $availableRoom->id,
                'booking_date' => now()->subDays(rand(3, 15)),
                'start_date' => now()->subDays(rand(1, 5)),
                'end_date' => now()->addMonths(rand(2, 6)),
                'rental_type' => 'monthly',
                'price' => $availableRoom->monthly_price,
                'status' => 'approved',
            ]);
            $availableRoom->update(['status' => 'occupied']);

            $penghuni = Penghuni::create([
                'user_id' => $tenant->id,
                'kos_id' => $kos->id,
                'kamar_id' => $availableRoom->id,
                'identity_number' => '32'.str_pad(rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
                'phone' => $tenant->phone ?? '08'.rand(1000000000, 9999999999),
                'check_in_date' => $booking->start_date,
                'status' => 'active',
            ]);

            $kontrak = Kontrak::create([
                'contract_number' => 'KT-'.strtoupper(Str::random(12)),
                'penghuni_id' => $penghuni->id,
                'kos_id' => $kos->id,
                'kamar_id' => $availableRoom->id,
                'rental_type' => 'monthly',
                'rental_price' => $availableRoom->monthly_price,
                'billing_mode' => 'personal',
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'status' => 'active',
            ]);

            Tagihan::create([
                'bill_number' => 'TG'.str_pad(20 + $i, 6, '0', STR_PAD_LEFT),
                'penghuni_id' => $penghuni->id,
                'kontrak_id' => $kontrak->id,
                'kamar_id' => $availableRoom->id,
                'bill_type' => 'rent',
                'period_start' => now()->subMonth(),
                'period_end' => now(),
                'subtotal' => $availableRoom->monthly_price,
                'discount' => 0,
                'penalty' => 0,
                'total' => $availableRoom->monthly_price,
                'due_date' => now()->addDays(rand(1, 14)),
                'status' => 'unpaid',
            ]);

            $usedRooms[] = $availableRoom->id;
        }
    }

    private function seedExtraOwnerData(): void
    {
        $owners = User::where('role', 'owner')
            ->where('email', '!=', 'owner@example.com')
            ->get();

        if ($owners->isEmpty()) {
            return;
        }

        $ownerNames = ['Siti Rahayu', 'Joko Widodo'];
        foreach ($owners->take(2) as $i => $owner) {
            $name = $ownerNames[$i] ?? 'Owner '.($i + 1);
            if (str_starts_with($owner->name, 'Owner') || str_starts_with($owner->name, 'John')) {
                $owner->update(['name' => $name, 'phone' => '08'.rand(1000000000, 9999999999)]);
            }

            if ($owner->ownedKos()->count() > 0) {
                continue;
            }

            $kosData = [
                [
                    'name' => $name === 'Siti Rahayu' ? 'Kos Putri Melati' : 'Kos Exclusive Borobudur',
                    'address' => $name === 'Siti Rahayu'
                        ? 'Jl. Terban No. 22, Gondokusuman, Yogyakarta'
                        : 'Jl. Borobudur Selatan No. 8, Sleman',
                    'description' => $name === 'Siti Rahayu'
                        ? 'Kost putri eksklusif di pusat kota Yogyakarta. Keamanan 24 jam, WiFi gratis, ruang cuci bersama.'
                        : 'Kos premium dekat Candi Borobudur. Pemandangan alam, udara sejuk, cocok untuk remote worker.',
                    'phone' => $owner->phone,
                    'status' => 'active',
                ],
            ];

            foreach ($kosData as $data) {
                $kos = Kos::create(array_merge($data, [
                    'owner_id' => $owner->id,
                    'latitude' => -7.7956 + (rand(-10, 10) / 1000),
                    'longitude' => 110.3695 + (rand(-10, 10) / 1000),
                ]));

                $roomPrefix = strtoupper(substr(uniqid(), -4));
                foreach (['A' => 4, 'B' => 3] as $block => $count) {
                    for ($j = 1; $j <= $count; $j++) {
                        $prices = [600000, 800000, 1000000, 1200000];
                        $price = $prices[array_rand($prices)];

                        Kamar::create([
                            'kos_id' => $kos->id,
                            'room_number' => $roomPrefix.'-'.$block.$j,
                            'room_name' => 'Kamar '.$block.$j,
                            'room_type' => ['Standard', 'Deluxe', 'VIP', 'Sharing'][array_rand(['Standard', 'Deluxe', 'VIP', 'Sharing'])],
                            'capacity' => rand(1, 2),
                            'billing_mode' => 'personal',
                            'monthly_price' => $price,
                            'daily_price' => (int) ($price / 30),
                            'weekly_price' => (int) ($price / 4),
                            'area' => rand(12, 25).' m²',
                            'floor' => $block === 'A' ? 1 : 2,
                            'status' => $j <= 2 ? 'occupied' : 'available',
                            'description' => 'Kamar '.$block.$j.' dengan fasilitas lengkap',
                        ]);
                    }
                }
            }
        }
    }

    private function seedBookingVariety(): void
    {
        $owner = User::where('email', 'owner@example.com')->first();
        $tenants = User::where('role', 'tenant')->get();
        $kos = Kos::where('owner_id', $owner->id)->first();

        if (! $kos || $tenants->isEmpty()) {
            return;
        }

        $pendingRoom = Kamar::where('kos_id', $kos->id)
            ->where('status', 'available')
            ->whereNotIn('id', Booking::pluck('kamar_id'))
            ->first();

        if ($pendingRoom) {
            $tenant2 = $tenants->first(function ($t) {
                return $t->email !== 'tenant@example.com';
            }) ?? $tenants->first();

            Booking::create([
                'booking_code' => 'BK'.str_pad(50, 6, '0', STR_PAD_LEFT),
                'user_id' => $tenant2->id,
                'kos_id' => $kos->id,
                'kamar_id' => $pendingRoom->id,
                'booking_date' => now()->subDays(2),
                'start_date' => now()->addDays(7),
                'end_date' => now()->addMonths(3),
                'rental_type' => 'monthly',
                'price' => $pendingRoom->monthly_price,
                'status' => 'pending',
            ]);
        }

        $rejectedRoom = Kamar::where('kos_id', $kos->id)
            ->where('status', 'available')
            ->whereNotIn('id', Booking::pluck('kamar_id'))
            ->first();

        if ($rejectedRoom) {
            Booking::create([
                'booking_code' => 'BK'.str_pad(51, 6, '0', STR_PAD_LEFT),
                'user_id' => $tenants->last()->id,
                'kos_id' => $kos->id,
                'kamar_id' => $rejectedRoom->id,
                'booking_date' => now()->subDays(5),
                'start_date' => now()->addDays(10),
                'end_date' => now()->addMonths(3),
                'rental_type' => 'monthly',
                'price' => $rejectedRoom->monthly_price,
                'status' => 'rejected',
                'rejection_reason' => 'Kamar sedang dalam perawatan.',
            ]);
        }
    }

    private function seedPayments(): void
    {
        $tagihans = Tagihan::where('status', 'unpaid')->take(2)->get();

        foreach ($tagihans as $i => $tagihan) {
            $pembayaran = Pembayaran::create([
                'payment_number' => 'PAY'.str_pad(100 + $i, 6, '0', STR_PAD_LEFT),
                'penghuni_id' => $tagihan->penghuni_id,
                'tagihan_id' => $tagihan->id,
                'amount' => $tagihan->total,
                'payment_method' => $i === 0 ? 'transfer_bank' : 'cash',
                'payment_date' => now()->subDays(rand(1, 3)),
                'notes' => $i === 0 ? 'Transfer BCA' : 'Bayar tunai via owner',
                'verification_status' => 'approved',
                'verified_at' => now()->subDays(1),
            ]);

            $tagihan->update(['status' => 'paid']);
        }

        $unpaidTagihan = Tagihan::where('status', 'unpaid')->first();
        if ($unpaidTagihan) {
            Pembayaran::create([
                'payment_number' => 'PAY'.str_pad(200, 6, '0', STR_PAD_LEFT),
                'penghuni_id' => $unpaidTagihan->penghuni_id,
                'tagihan_id' => $unpaidTagihan->id,
                'amount' => $unpaidTagihan->total,
                'payment_method' => 'transfer_bank',
                'payment_date' => now()->subHours(6),
                'notes' => 'Transfer BCA pending verifikasi',
                'verification_status' => 'pending',
            ]);
        }
    }

    private function seedDamageReports(): void
    {
        $penghunis = Penghuni::where('status', 'active')->get();
        if ($penghunis->isEmpty()) {
            return;
        }

        $reports = [
            ['category' => 'Plumbing', 'description' => 'Keran kamar mandi bocor sudah 3 hari. Air terus menetes dan membuat lantai basah.', 'status' => 'pending'],
            ['category' => 'Electrical', 'description' => 'Stop kontak di dekat meja belajar tidak berfungsi. Lampu laptop tidak bisa dicharge.', 'status' => 'reviewing'],
            ['category' => 'Furniture', 'description' => 'Pintu lemari pakaian engselnya longgar dan hampir lepas. Perlu segera diperbaiki.', 'status' => 'resolved'],
        ];

        foreach ($penghunis->take(3) as $i => $penghuni) {
            $report = $reports[$i % count($reports)];
            DamageReport::create([
                'user_id' => $penghuni->user_id,
                'kos_id' => $penghuni->kos_id,
                'kamar_id' => $penghuni->kamar_id,
                'category' => $report['category'],
                'description' => $report['description'],
                'status' => $report['status'],
                'owner_reply' => $report['status'] === 'resolved' ? 'Sudah diperbaiki oleh teknisi. Terima kasih atas laporannya.' : null,
                'resolved_at' => $report['status'] === 'resolved' ? now()->subDays(2) : null,
            ]);
        }
    }

    private function seedMessages(): void
    {
        $owner = User::where('email', 'owner@example.com')->first();
        $tenant = User::where('email', 'tenant@example.com')->first();

        if (! $owner || ! $tenant) {
            return;
        }

        if (Message::count() > 0) {
            return;
        }

        $kos = Kos::where('owner_id', $owner->id)->first();
        if (! $kos) {
            return;
        }

        Message::create([
            'sender_id' => $tenant->id,
            'receiver_id' => $owner->id,
            'kos_id' => $kos->id,
            'message' => 'Halo Pak, saya mau tanya apakah ada parkir mobil di kos ini? Saya rencana bawa mobil pribadi.',
            'read_at' => now()->subHours(3),
        ]);

        Message::create([
            'sender_id' => $owner->id,
            'receiver_id' => $tenant->id,
            'kos_id' => $kos->id,
            'message' => 'Halo, ada kok parkir mobil di belakang. Gratis untuk penghuni.',
            'read_at' => null,
        ]);

        Message::create([
            'sender_id' => $tenant->id,
            'receiver_id' => $owner->id,
            'kos_id' => $kos->id,
            'message' => 'Baik pak, terima kasih infonya. Saya mau tanya juga soal tagihan bulan depan, apakah sudah bisa dilihat?',
            'read_at' => null,
        ]);
    }
}
