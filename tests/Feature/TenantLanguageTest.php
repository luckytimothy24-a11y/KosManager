<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_validation_errors_are_indonesian(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($tenant)->post(route('tenant.booking.store'), []);

        $response->assertSessionHasErrors();
        $errors = session('errors')->getBag('default');
        $this->assertNotEmpty($errors->all());
        foreach ($errors->all() as $message) {
            $this->assertDoesNotMatchRegularExpression(
                '/\bfield is\b|\bThe .* field\b/i',
                $message,
                "Pesan validasi masih Inggris: {$message}"
            );
        }
        $this->assertStringContainsString('wajib diisi', $errors->first('kamar_id'));
    }

    public function test_checkin_validation_errors_are_indonesian(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $booking = Booking::factory()->create([
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'user_id' => $tenant->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($tenant)->post(route('tenant.checkin.identity.confirm', $booking), []);

        $response->assertSessionHasErrors();
        $this->assertStringContainsString('wajib diisi', session('errors')->first('identity_number'));
    }

    public function test_kos_pages_have_no_english_leftovers(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        foreach ([route('tenant.kos.index'), route('tenant.kos.show', $kos)] as $url) {
            $content = $this->actingAs($tenant)->get($url)->assertOk()->getContent();
            foreach (['placeholder="Min"', 'placeholder="Max"', '>Reset<', 'Login untuk Booking', 'Live Chat'] as $english) {
                $this->assertStringNotContainsString($english, $content, "Masih ada '{$english}' di {$url}");
            }
        }

        $index = $this->actingAs($tenant)->get(route('tenant.kos.index'))->getContent();
        $this->assertStringContainsString('Atur Ulang', $index);
        $this->assertStringContainsString('Minimal', $index);
    }

    public function test_login_failure_message_is_indonesian(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'tidakada@example.com',
            'password' => 'salah12345',
        ]);

        $response->assertSessionHasErrors('email');
        $message = strtolower(session('errors')->first('email'));
        $this->assertDoesNotMatchRegularExpression(
            '/these credentials do not match|the provided/i',
            session('errors')->first('email')
        );
        $this->assertTrue(
            str_contains($message, 'salah') || str_contains($message, 'tidak sesuai'),
            "Pesan login tidak berbahasa Indonesia: {$message}"
        );
    }

    public function test_ask_form_validation_is_indonesian(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $seeker = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($seeker)->post(route('tenant.kos.ask.store', $kos), [
            'message' => '',
            'category' => 'ngawur',
        ])->assertSessionHasErrors(['message', 'category']);

        $errors = session('errors');
        $this->assertStringContainsString('wajib diisi', $errors->first('message'));
    }
}
