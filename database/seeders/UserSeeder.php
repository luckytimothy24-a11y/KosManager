<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
            'name' => 'Tenant',
            'email' => 'tenant@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'tenant',
            'phone' => '081234567893',
            'address' => 'Jl. Tenant No. 4',
            'is_active' => true,
        ]);

        User::factory()->count(2)->create([
            'role' => 'owner',
            'is_active' => true,
        ]);

        User::factory()->count(5)->create([
            'role' => 'tenant',
            'is_active' => true,
        ]);
    }
}
