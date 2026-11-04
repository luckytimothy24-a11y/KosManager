<?php

namespace Database\Seeders;

use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo data: Pemilik Kos Asli + rekening tujuan + perjanjian komisi.
 *
 * Opt-in only (tidak dipanggil DatabaseSeeder):
 *   php artisan db:seed --class=DemoPropertyOwnerSeeder
 *
 * Idempotent: tidak menghapus dan tidak menimpa pemilik/perjanjian yang sudah ada.
 * Tidak pernah berjalan di production.
 */
class DemoPropertyOwnerSeeder extends Seeder
{
    private const KOS_NAME = 'Griya Srikandi Pogung';

    private const OWNER_EMAIL = 'budi.santoso@example.com';

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('DemoPropertyOwnerSeeder dilewati: production tidak boleh diisi data demo.');

            return;
        }

        $kos = Kos::query()
            ->where('name', self::KOS_NAME)
            ->where('status', 'active')
            ->first();

        if (! $kos) {
            $this->command?->warn('Kos "'.self::KOS_NAME.'" tidak ditemukan. Seeder dilewati.');

            return;
        }

        if ($kos->property_owner_id !== null) {
            $this->command?->info('Kos "'.$kos->name.'" sudah memiliki Pemilik Kos Asli. Seeder dilewati (tidak menimpa).');

            return;
        }

        DB::transaction(function () use ($kos) {
            $propertyOwner = PropertyOwner::firstOrCreate(
                ['email' => self::OWNER_EMAIL],
                [
                    'name' => 'Budi Santoso',
                    'phone' => '081234567890',
                ]
            );

            PropertyOwnerBankAccount::updateOrCreate(
                ['property_owner_id' => $propertyOwner->id],
                [
                    'bank_name' => 'BCA',
                    'account_number' => '001234567890',
                    'account_holder_name' => 'Budi Santoso',
                ]
            );

            $kos->update(['property_owner_id' => $propertyOwner->id]);

            if (! ManagementAgreement::where('kos_id', $kos->id)->exists()) {
                $approver = User::where('email', 'owner@example.com')->first();

                ManagementAgreement::create([
                    'kos_id' => $kos->id,
                    'property_owner_id' => $propertyOwner->id,
                    'commission_type' => 'percent',
                    'commission_value' => 10,
                    'effective_from' => '2026-01-01',
                    'effective_to' => null,
                    'approved_by' => $approver?->id,
                    'notes' => 'Data demo: pemilik fiktif, bukan perjanjian nyata.',
                ]);
            }
        });

        $this->command?->info('Demo Pemilik Kos Asli dibuat/dipastikan untuk: '.$kos->name);
    }
}
