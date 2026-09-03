<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = User::where('email', 'tenant@example.com')->first();
        $kos = Kos::first();
        $kamars = Kamar::where('kos_id', $kos->id)->get();

        Booking::create([
            'booking_code' => 'BK000001',
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamars[0]->id,
            'booking_date' => now()->subDays(10),
            'start_date' => now()->addDays(5),
            'end_date' => now()->addMonths(3),
            'rental_type' => 'monthly',
            'price' => $kamars[0]->monthly_price,
            'status' => 'approved',
        ]);
        $kamars[0]->update(['status' => 'booked']);

        Booking::create([
            'booking_code' => 'BK000002',
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamars[1]->id,
            'booking_date' => now()->subDays(8),
            'start_date' => now()->addDays(3),
            'end_date' => now()->addMonths(3),
            'rental_type' => 'monthly',
            'price' => $kamars[1]->monthly_price,
            'status' => 'approved',
        ]);
        $kamars[1]->update(['status' => 'booked']);

        Booking::create([
            'booking_code' => 'BK000003',
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamars[2]->id,
            'booking_date' => now()->subDays(15),
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(2),
            'rental_type' => 'monthly',
            'price' => $kamars[2]->monthly_price,
            'status' => 'approved',
        ]);
        $kamars[2]->update(['status' => 'occupied']);

        Booking::create([
            'booking_code' => 'BK000004',
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamars[3]->id,
            'booking_date' => now()->subDays(20),
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(3),
            'rental_type' => 'monthly',
            'price' => $kamars[3]->monthly_price,
            'status' => 'cancelled',
        ]);

        Booking::create([
            'booking_code' => 'BK000005',
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamars[4]->id,
            'booking_date' => now()->subDays(30),
            'start_date' => now()->subDays(25),
            'end_date' => now()->subDays(5),
            'rental_type' => 'monthly',
            'price' => $kamars[4]->monthly_price,
            'status' => 'completed',
        ]);
    }
}
