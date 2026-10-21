<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_dropdown_shows_typed_icons_and_unread_markers(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        Notification::factory()->create([
            'user_id' => $owner->id,
            'type' => 'booking',
            'title' => 'Booking Baru Masuk',
            'is_read' => false,
        ]);
        Notification::factory()->create([
            'user_id' => $owner->id,
            'type' => 'message',
            'title' => 'Pesan Baru Diterima',
            'is_read' => false,
        ]);

        $response = $this->actingAs($owner)->get(route('dashboard'));
        $response->assertOk();
        // Ikon per tipe hadir di dropdown.
        $response->assertSee('ri-calendar-check-line', escape: false);
        $response->assertSee('ri-chat-3-line', escape: false);
        // Penanda unread + pill jumlah.
        $response->assertSee('2 baru');
        // Chip label tipe.
        $response->assertSee('Booking');
        $response->assertSee('Pesan');
    }

    public function test_admin_dropdown_renders_same_design(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Notification::factory()->create([
            'user_id' => $admin->id,
            'type' => 'billing',
            'title' => 'Tagihan Overdue',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('ri-bill-line', escape: false);
        $response->assertSee('1 baru');
    }

    public function test_dropdown_item_with_url_is_clickable_and_marks_read(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $notif = Notification::factory()->create([
            'user_id' => $owner->id,
            'type' => 'booking',
            'is_read' => false,
            'data' => ['url' => route('dashboard')],
        ]);

        // Item dengan URL me-render form tandai-dibaca di dropdown.
        $response = $this->actingAs($owner)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('drop-read-'.$notif->id, escape: false);

        // Klik (submit) menandai dibaca via endpoint existing.
        $this->actingAs($owner)->post(route('notifications.read', $notif))
            ->assertRedirect();
        $this->assertTrue($notif->fresh()->is_read);
    }

    public function test_index_renders_typed_icons_and_clickable_items(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        Notification::factory()->create([
            'user_id' => $owner->id,
            'type' => 'billing',
            'title' => 'Tagihan Wajib Bayar',
            'is_read' => false,
            'data' => ['url' => route('dashboard')],
        ]);
        Notification::factory()->create([
            'user_id' => $owner->id,
            'type' => 'checkout',
            'title' => 'Check-Out Masuk',
            'is_read' => true,
        ]);

        $response = $this->actingAs($owner)->get(route('notifications.index'));
        $response->assertOk();
        $response->assertSee('ri-bill-line', escape: false);
        $response->assertSee('ri-logout-box-line', escape: false);
        $response->assertSee('Baru');
        $response->assertSee('mark-read-', escape: false);
    }

    public function test_dropdown_empty_state(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($tenant)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Belum ada notifikasi');
    }
}
