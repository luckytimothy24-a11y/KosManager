<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ChatNotificationClickTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied']);
        Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
    }

    public function test_tenant_message_stores_owner_conversation_url_on_notification(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.messages.store'), [
            'message' => 'Bisa perbaiki lampu kamar?',
            'category' => 'fasilitas',
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', ['user_id' => $this->owner->id]);

        $notification = Notification::where('user_id', $this->owner->id)->latest()->first();
        $this->assertEquals(route('owner.messages.show', $this->tenant), $notification->data['url'] ?? null);
    }

    public function test_owner_reply_stores_tenant_messages_url_on_notification(): void
    {
        Message::factory()->create([
            'kos_id' => $this->kos->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => $this->owner->id,
            'category' => 'fasilitas',
            'message' => 'AC kamar saya rusak',
        ]);

        $this->actingAs($this->owner)->post(route('owner.messages.reply', $this->tenant), [
            'message' => 'Saya cek dulu ya',
        ])->assertRedirect();

        $notification = Notification::where('user_id', $this->tenant->id)->latest()->first();
        $this->assertNotNull($notification);
        $this->assertEquals(route('tenant.messages.index'), $notification->data['url'] ?? null);
    }

    public function test_notifications_page_shows_buka_link_for_message_notification(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.messages.store'), [
            'message' => 'Kapan jadwal perbaikan?',
            'category' => 'fasilitas',
        ]);

        $notification = Notification::where('user_id', $this->owner->id)->latest()->first();
        $this->actingAs($this->owner)->post(route('notifications.read', $notification));

        $response = $this->actingAs($this->owner)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Buka');
        $response->assertSee(route('owner.messages.show', $this->tenant));
    }

    public function test_owner_chat_show_has_quick_replies_and_topic_context(): void
    {
        Message::factory()->create([
            'kos_id' => $this->kos->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => $this->owner->id,
            'category' => 'fasilitas',
            'message' => 'AC kamar saya rusak',
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.messages.show', $this->tenant));

        $response->assertOk();
        $response->assertSee('Balasan Cepat');
        $response->assertSee('Saya cek dulu ya');
        $response->assertSee('Silakan kirim bukti pembayaran');
        $response->assertSee('Topik: Fasilitas');
        $response->assertSee($this->kamar->room_number);
    }
}
