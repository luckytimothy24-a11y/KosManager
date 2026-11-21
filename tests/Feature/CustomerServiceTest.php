<?php

namespace Tests\Feature;

use App\Models\CsConversation;
use App\Models\CsMessage;
use App\Models\Notification;
use App\Models\User;
use App\Support\NotificationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private User $otherTenant;

    private User $admin;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = User::factory()->create(['role' => 'pencari']);
        $this->otherTenant = User::factory()->create(['role' => 'pencari']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    }

    private function conversationFor(
        User $tenant,
        string $status = CsConversation::STATUS_OPEN,
        string $subject = 'Kendala akun'
    ): CsConversation {
        return CsConversation::create([
            'tenant_id' => $tenant->id,
            'status' => $status,
            'subject' => $subject,
            'resolved_at' => $status === CsConversation::STATUS_RESOLVED ? now() : null,
        ]);
    }

    public function test_tenant_can_create_cs_conversation_separate_from_owner_messages(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('tenant.cs.store'), [
            'subject' => 'Tidak bisa login',
            'message' => 'Mohon bantuan reset akun.',
        ]);

        $conversation = CsConversation::firstOrFail();

        $response->assertRedirect(route('tenant.cs.show', $conversation));

        $this->assertSame((int) $this->tenant->id, (int) $conversation->tenant_id);
        $this->assertSame(CsConversation::STATUS_OPEN, $conversation->status);
        $this->assertDatabaseHas('cs_messages', [
            'cs_conversation_id' => $conversation->id,
            'sender_id' => $this->tenant->id,
            'message' => 'Mohon bantuan reset akun.',
        ]);
        // CS tidak boleh menulis ke tabel messages milik Hubungi Owner.
        $this->assertDatabaseCount('messages', 0);
        // Admin menerima notifikasi CS.
        $this->assertTrue(
            Notification::where('user_id', $this->admin->id)
                ->where('type', NotificationType::CUSTOMER_SERVICE)
                ->exists()
        );
    }

    public function test_tenant_index_only_shows_own_conversations(): void
    {
        $mine = $this->conversationFor($this->tenant, subject: 'Subjek milik saya');
        $other = $this->conversationFor($this->otherTenant, subject: 'Subjek tenant lain');

        $response = $this->actingAs($this->tenant)->get(route('tenant.cs.index'));

        $response->assertOk();
        $response->assertSee($mine->subject);
        $response->assertDontSee($other->subject);
    }

    public function test_tenant_cannot_view_or_reply_to_other_tenants_conversation(): void
    {
        $conversation = $this->conversationFor($this->otherTenant);

        $this->actingAs($this->tenant)
            ->get(route('tenant.cs.show', $conversation))
            ->assertForbidden();

        $this->actingAs($this->tenant)
            ->post(route('tenant.cs.reply', $conversation), ['message' => 'Mencoba menyusup'])
            ->assertForbidden();

        $this->assertDatabaseMissing('cs_messages', ['message' => 'Mencoba menyusup']);
    }

    public function test_tenant_reply_reopens_resolved_conversation(): void
    {
        $conversation = $this->conversationFor($this->tenant, CsConversation::STATUS_RESOLVED);

        $response = $this->actingAs($this->tenant)->post(route('tenant.cs.reply', $conversation), [
            'message' => 'Masih butuh bantuan lanjutan.',
        ]);

        $response->assertRedirect(route('tenant.cs.show', $conversation));

        $conversation->refresh();
        $this->assertSame(CsConversation::STATUS_OPEN, $conversation->status);
        $this->assertNull($conversation->resolved_at);
        $this->assertDatabaseHas('cs_messages', [
            'cs_conversation_id' => $conversation->id,
            'sender_id' => $this->tenant->id,
            'message' => 'Masih butuh bantuan lanjutan.',
        ]);
    }

    public function test_admin_reply_sets_in_progress_and_assigns_admin(): void
    {
        $conversation = $this->conversationFor($this->tenant);

        CsMessage::create([
            'cs_conversation_id' => $conversation->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => null,
            'message' => 'Halo tim CS.',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.cs.reply', $conversation), [
            'message' => 'Halo, kami bantu ya.',
        ]);

        $response->assertRedirect(route('admin.cs.show', $conversation));

        $conversation->refresh();
        $this->assertSame(CsConversation::STATUS_IN_PROGRESS, $conversation->status);
        $this->assertSame((int) $this->admin->id, (int) $conversation->assigned_admin_id);
        $this->assertDatabaseHas('cs_messages', [
            'cs_conversation_id' => $conversation->id,
            'sender_id' => $this->admin->id,
            'receiver_id' => $this->tenant->id,
            'message' => 'Halo, kami bantu ya.',
        ]);
        $this->assertTrue(
            Notification::where('user_id', $this->tenant->id)
                ->where('type', NotificationType::CUSTOMER_SERVICE)
                ->exists()
        );
    }

    public function test_admin_can_resolve_conversation(): void
    {
        $conversation = $this->conversationFor($this->tenant);

        $response = $this->actingAs($this->admin)->patch(route('admin.cs.status', $conversation), [
            'status' => CsConversation::STATUS_RESOLVED,
        ]);

        $response->assertRedirect(route('admin.cs.show', $conversation));

        $conversation->refresh();
        $this->assertSame(CsConversation::STATUS_RESOLVED, $conversation->status);
        $this->assertNotNull($conversation->resolved_at);
        $this->assertSame((int) $this->admin->id, (int) $conversation->assigned_admin_id);
    }

    public function test_admin_cannot_set_invalid_status(): void
    {
        $conversation = $this->conversationFor($this->tenant);

        $this->actingAs($this->admin)
            ->patch(route('admin.cs.status', $conversation), ['status' => 'invalid'])
            ->assertSessionHasErrors('status');
    }

    public function test_tenant_opening_thread_marks_admin_messages_read(): void
    {
        $conversation = $this->conversationFor($this->tenant);

        $message = CsMessage::create([
            'cs_conversation_id' => $conversation->id,
            'sender_id' => $this->admin->id,
            'receiver_id' => $this->tenant->id,
            'message' => 'Balasan admin.',
        ]);

        $this->actingAs($this->tenant)
            ->get(route('tenant.cs.show', $conversation))
            ->assertOk();

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_admin_opening_thread_marks_tenant_messages_read(): void
    {
        $conversation = $this->conversationFor($this->tenant);

        $message = CsMessage::create([
            'cs_conversation_id' => $conversation->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => null,
            'message' => 'Pesan tenant.',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.cs.show', $conversation))
            ->assertOk();

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_admin_and_super_admin_can_access_inbox_but_owner_cannot(): void
    {
        $conversation = $this->conversationFor($this->tenant);
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($this->admin)->get(route('admin.cs.index'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.cs.show', $conversation))->assertOk();
        $this->actingAs($owner)->get(route('admin.cs.index'))->assertForbidden();
        $this->actingAs($this->tenant)->get(route('admin.cs.index'))->assertForbidden();
    }
}
