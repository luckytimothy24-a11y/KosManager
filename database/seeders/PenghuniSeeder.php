<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Database\Seeder;

class PenghuniSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = User::where('email', 'tenant@example.com')->first();
        $approvedBooking = Booking::where('status', 'approved')->first();

        Penghuni::create([
            'user_id' => $tenant->id,
            'kos_id' => $approvedBooking->kos_id,
            'kamar_id' => $approvedBooking->kamar_id,
            'identity_number' => '3201234567890001',
            'phone' => $tenant->phone,
            'status' => 'active',
        ]);

        $pendingBooking = Booking::where('status', 'pending')->first();

        Penghuni::create([
            'user_id' => $tenant->id,
            'kos_id' => $pendingBooking->kos_id,
            'kamar_id' => $pendingBooking->kamar_id,
            'identity_number' => '3201234567890002',
            'phone' => $tenant->phone,
            'status' => 'active',
        ]);
    }
}
