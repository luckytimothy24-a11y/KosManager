<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_create_shows_stepper(): void
    {
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get(route('tenant.booking.create', ['kos_id' => $kos->id]));

        $response->assertOk();
        $response->assertSee('Pilih Kamar');
        $response->assertSee('Periode Sewa');
        $response->assertSee('Review');
    }

    public function test_booking_create_wizard_submits_successfully(): void
    {
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'rental_type' => 'monthly',
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test No 1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $tenant->id,
            'kamar_id' => $kamar->id,
            'status' => 'pending',
        ]);
    }
}
