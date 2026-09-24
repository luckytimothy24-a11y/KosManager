<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ChatMessageTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private User $otherOwner;

    private Kos $kos;

    private Kamar $kamar;

    private Penghuni $penghuni;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->otherOwner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied']);
        $this->penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
    }

    public function test_tenant_can_open_chat_page_with_owner(): void
    {
        Message::factory()->create([
            'kos_id' => $this->kos->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => $this->owner->id,
            'message' => 'AC kamar saya rusak',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.messages.index'));

        $response->assertOk();
        $response->assertSee('Chat dengan Owner');
        $response->assertSee($this->owner->name);
        $response->assertSee('AC kamar saya rusak');
    }

    public function test_tenant_can_send_message_to_owner(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('tenant.messages.store'), [
            'message' => 'Bisa perbaiki lampu kamar?',
            'category' => 'fasilitas',
        ]);

        $response->assertRedirect(route('tenant.messages.index'));
        $this->assertDatabaseHas('messages', [
            'kos_id' => $this->kos->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => $this->owner->id,
            'category' => 'fasilitas',
            'message' => 'Bisa perbaiki lampu kamar?',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->owner->id,
            'type' => 'message',
        ]);
    }

    public function test_tenant_without_active_penghuni_cannot_send_message(): void
    {
        $this->penghuni->update(['status' => 'inactive']);

        $response = $this->actingAs($this->tenant)->post(route('tenant.messages.store'), [
            'message' => 'Halo owner',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_owner_sees_conversation_with_tenant(): void
    {
        Message::factory()->create([
            'kos_id' => $this->kos->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => $this->owner->id,
            'message' => 'Pintu kamar macet',
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.messages.index'));

        $response->assertOk();
        $response->assertSee($this->tenant->name);
        $response->assertSee('Pintu kamar macet');
        $response->assertSee('1'); // unread badge
    }

    public function test_owner_can_reply_to_tenant_message(): void
    {
        Message::factory()->create([
            'kos_id' => $this->kos->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => $this->owner->id,
            'message' => 'Pintu kamar macet',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.messages.reply', $this->tenant), [
            'message' => 'Nanti kami perbaiki.',
        ]);

        $response->assertRedirect(route('owner.messages.show', $this->tenant));
        $this->assertDatabaseHas('messages', [
            'kos_id' => $this->kos->id,
            'sender_id' => $this->owner->id,
            'receiver_id' => $this->tenant->id,
            'message' => 'Nanti kami perbaiki.',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->tenant->id,
            'type' => 'message',
        ]);
    }

    public function test_owner_cannot_reply_to_unrelated_tenant(): void
    {
        $stranger = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($this->owner)->post(route('owner.messages.reply', $stranger), [
            'message' => 'Halo',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_other_owner_cannot_view_tenant_conversation(): void
    {
        Message::factory()->create([
            'kos_id' => $this->kos->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => $this->owner->id,
            'message' => 'Rahasia',
        ]);

        $response = $this->actingAs($this->otherOwner)->get(route('owner.messages.show', $this->tenant));

        $response->assertForbidden();
    }

    public function test_reading_conversation_marks_owner_messages_and_incoming_as_read(): void
    {
        Message::factory()->create([
            'kos_id' => $this->kos->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => $this->owner->id,
            'message' => 'Dari tenant',
            'read_at' => null,
        ]);

        $this->actingAs($this->owner)->get(route('owner.messages.show', $this->tenant));

        $this->assertDatabaseHas('messages', [
            'kos_id' => $this->kos->id,
            'receiver_id' => $this->owner->id,
            'read_at' => now()->toDateTimeString(),
        ]);
    }
}
