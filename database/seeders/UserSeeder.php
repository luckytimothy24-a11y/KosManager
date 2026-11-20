<?php

namespace Database\Seeders;

use App\Models\OwnerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @throws ValidationException
     */
    public function run(): void
    {
        // Production environment: skip creating demo users to avoid affecting live data
        if (app()->environment('production')) {
            return;
        }

        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'phone' => '081234567890',
            'address' => 'Jl. Super Admin No. 1',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567891',
            'address' => 'Jl. Admin No. 2',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'owner',
            'phone' => '081234567892',
            'address' => 'Jl. Owner No. 3',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Pencari Kos',
            'email' => 'pencari@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'pencari',
            'phone' => '081234567893',
            'address' => 'Jl. Pencari No. 4',
            'is_active' => true,
        ]);

        $owner = User::where('email', 'owner@example.com')->first();
        if ($owner) {
            OwnerProfile::firstOrCreate(
                ['user_id' => $owner->id],
                [
                    'bank_name' => 'BCA',
                    'bank_account_number' => '1234567890',
                    'bank_account_holder' => $owner->name,
                    'verification_status' => 'verified',
                ]
            );
        }

        User::factory()->count(2)->create([
            'role' => 'owner',
            'is_active' => true,
        ]);

        User::factory()->count(5)->create([
            'role' => 'pencari',
            'is_active' => true,
        ]);
    }
}
