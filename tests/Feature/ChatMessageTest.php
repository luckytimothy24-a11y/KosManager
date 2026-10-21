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

        $response->assertRedirect(route('tenant.messages.index', ['kos' => $this->kos->id]));
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

        $response->assertRedirect(route('owner.messages.show', ['tenant' => $this->tenant, 'kos' => $this->kos->id]));
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
        ]);
        $this->assertNotNull(\App\Models\Message::where('kos_id', $this->kos->id)->where('receiver_id', $this->owner->id)->first()->read_at);
    }

    public function test_messages_to_different_owners_stay_in_separate_threads(): void
    {
        $ownerB = User::factory()->create(['role' => 'owner']);
        $kosB = Kos::factory()->create(['owner_id' => $ownerB->id, 'status' => 'active']);
        $kamarB = Kamar::factory()->create(['kos_id' => $kosB->id, 'status' => 'occupied']);
        Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kosB->id,
            'kamar_id' => $kamarB->id,
            'status' => 'active',
        ]);

        // Chat ke owner kos A
        $this->actingAs($this->tenant)->post(route('tenant.messages.store'), [
            'message' => 'Pesan untuk owner A',
            'kos_id' => $this->kos->id,
        ])->assertRedirect(route('tenant.messages.index', ['kos' => $this->kos->id]));

        // Chat ke owner kos B
        $this->actingAs($this->tenant)->post(route('tenant.messages.store'), [
            'message' => 'Pesan untuk owner B',
            'kos_id' => $kosB->id,
        ])->assertRedirect(route('tenant.messages.index', ['kos' => $kosB->id]));

        $this->assertDatabaseHas('messages', [
            'kos_id' => $this->kos->id,
            'receiver_id' => $this->owner->id,
            'message' => 'Pesan untuk owner A',
        ]);
        $this->assertDatabaseHas('messages', [
            'kos_id' => $kosB->id,
            'receiver_id' => $ownerB->id,
            'message' => 'Pesan untuk owner B',
        ]);

        // Thread A aktif: panel & form menunjuk kos A (bukan kos B)
        $responseA = $this->actingAs($this->tenant)->get(route('tenant.messages.index', ['kos' => $this->kos->id]));
        $responseA->assertOk();
        $responseA->assertSee('Pesan untuk owner A');
        $responseA->assertSee('name="kos_id" value="'.$this->kos->id.'"', false);
        $responseA->assertSee($this->owner->name);

        // Thread B aktif: panel & form menunjuk kos B
        $responseB = $this->actingAs($this->tenant)->get(route('tenant.messages.index', ['kos' => $kosB->id]));
        $responseB->assertOk();
        $responseB->assertSee('Pesan untuk owner B');
        $responseB->assertSee('name="kos_id" value="'.$kosB->id.'"', false);
        $responseB->assertSee($ownerB->name);

        // Owner A tidak melihat pesan ke owner B
        $this->actingAs($this->owner)->get(route('owner.messages.show', ['tenant' => $this->tenant, 'kos' => $this->kos->id]))
            ->assertOk()
            ->assertSee('Pesan untuk owner A')
            ->assertDontSee('Pesan untuk owner B');

        $this->actingAs($ownerB)->get(route('owner.messages.show', ['tenant' => $this->tenant, 'kos' => $kosB->id]))
            ->assertOk()
            ->assertSee('Pesan untuk owner B')
            ->assertDontSee('Pesan untuk owner A');
    }

    public function test_tenant_can_send_to_active_kos_as_presales(): void
    {
        $strangerOwner = User::factory()->create(['role' => 'owner']);
        $strangerKos = Kos::factory()->create(['owner_id' => $strangerOwner->id, 'status' => 'active']);

        $this->actingAs($this->tenant)->post(route('tenant.messages.store'), [
            'message' => 'Halo stranger',
            'kos_id' => $strangerKos->id,
        ])->assertRedirect(route('tenant.messages.index', ['kos' => $strangerKos->id]));

        $this->assertDatabaseCount('messages', 1);
    }

    public function test_tenant_cannot_send_to_inactive_kos(): void
    {
        $strangerOwner = User::factory()->create(['role' => 'owner']);
        $strangerKos = Kos::factory()->create(['owner_id' => $strangerOwner->id, 'status' => 'inactive']);

        $this->actingAs($this->tenant)->post(route('tenant.messages.store'), [
            'message' => 'Halo stranger',
            'kos_id' => $strangerKos->id,
        ])->assertForbidden();

        $this->assertDatabaseCount('messages', 0);
    }
}
