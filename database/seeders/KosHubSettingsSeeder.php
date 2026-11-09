<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KosHubSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'commission_rate', 'value' => '5', 'type' => 'integer', 'group' => 'payment'],
            ['key' => 'service_fee_pencari', 'value' => '10000', 'type' => 'integer', 'group' => 'payment'],
            ['key' => 'payment_deadline_hours', 'value' => '24', 'type' => 'integer', 'group' => 'payment'],
            ['key' => 'auto_confirm_hours', 'value' => '24', 'type' => 'integer', 'group' => 'booking'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
