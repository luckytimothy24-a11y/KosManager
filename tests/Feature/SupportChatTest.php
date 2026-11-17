<?php

namespace Tests\Feature;

use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SupportChatTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private User $otherTenant;

    private User $admin;

    private User $otherAdmin;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->otherAdmin = User::factory()->create(['role' => 'admin']);
        $this->tenant = User::factory()->create(['role' => 'pencari']);
        $this->otherTenant = User::factory()->create(['role' => 'pencari']);
        $this->owner = User::factory()->create(['role' => 'owner']);
    }

    private function tenantMessage(string $text = 'Halo CS', ?User $tenant = null): SupportMessage
    {
        $tenant ??= $this->tenant;

        return SupportMessage::create([
            'tenant_id' => $tenant->id,
            'sender_id' => $tenant->id,
            'receiver_id' => $this->admin->id,
            'message' => $text,
        ]);
    }

    private function csReply(string $text = 'Halo, ada yang bisa dibantu?', ?User $admin = null): SupportMessage
    {
        return SupportMessage::create([
            'tenant_id' => $this->tenant->id,
            'sender_id' => ($admin ?? $this->admin)->id,
            'receiver_id' => $this->tenant->id,
            'message' => $text,
        ]);
    }

    public function test_tenant_can_open_support_page(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.support.index'));

        $response->assertOk();
        $response->assertSee('Bantuan & CS');
        $response->assertSee('Chat dengan CS KosManager');
        $response->assertSee('Kirim ke CS');
        $response->assertSee('Dukungan resmi aplikasi');
        $response->assertSee('action="'.route('tenant.support.store').'"', false);
        $response->assertDontSee('$el.submit()', false);
    }

    public function test_tenant_can_send_message_to_cs(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('tenant.support.store'), [
            'message' => 'Booking saya belum terkonfirmasi.',
        ]);

        $response->assertRedirect(route('tenant.support.index'));
        $this->assertDatabaseHas('support_messages', [
            'tenant_id' => $this->tenant->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => $this->admin->id,
            'message' => 'Booking saya belum terkonfirmasi.',
            'read_at' => null,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->id,
            'type' => 'message',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->otherAdmin->id,
            'type' => 'message',
        ]);
    }

    public function test_tenant_message_requires_body(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('tenant.support.store'), [
            'message' => '   ',
        ]);

        $response->assertSessionHasErrors('message');
        $this->assertDatabaseCount('support_messages', 0);
    }

    public function test_admin_can_see_thread_and_reply(): void
    {
        $this->tenantMessage('Saya ingin mengubah email akun.');

        $this->actingAs($this->admin)
            ->get(route('admin.support.index'))
            ->assertOk()
            ->assertSee($this->tenant->name)
            ->assertSee('Saya ingin mengubah email akun.');

        $this->actingAs($this->admin)
            ->get(route('admin.support.show', $this->tenant))
            ->assertOk()
            ->assertSee('Saya ingin mengubah email akun.');

        $response = $this->actingAs($this->admin)->post(route('admin.support.store', $this->tenant), [
            'message' => 'Silakan kirim email lama dan baru.',
        ]);

        $response->assertRedirect(route('admin.support.show', $this->tenant));
        $this->assertDatabaseHas('support_messages', [
            'tenant_id' => $this->tenant->id,
            'sender_id' => $this->admin->id,
            'receiver_id' => $this->tenant->id,
            'message' => 'Silakan kirim email lama dan baru.',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->tenant->id,
            'type' => 'message',
        ]);

        $this->actingAs($this->tenant)
            ->get(route('tenant.support.index'))
            ->assertOk()
            ->assertSee('Silakan kirim email lama dan baru.');
    }

    public function test_reply_from_another_admin_is_visible_and_becomes_thread_cs(): void
    {
        $this->tenantMessage('Pertanyaan pertama');

        $this->actingAs($this->otherAdmin)->post(route('admin.support.store', $this->tenant), [
            'message' => 'Balasan dari admin kedua.',
        ])->assertRedirect(route('admin.support.show', $this->tenant));

        $this->actingAs($this->tenant)
            ->get(route('tenant.support.index'))
            ->assertOk()
            ->assertSee('Balasan dari admin kedua.')
            ->assertSee('Tim CS KosManager');

        $this->actingAs($this->tenant)->post(route('tenant.support.store'), [
            'message' => 'Pertanyaan lanjutan',
        ])->assertRedirect(route('tenant.support.index'));

        $this->assertDatabaseHas('support_messages', [
            'tenant_id' => $this->tenant->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => $this->otherAdmin->id,
            'message' => 'Pertanyaan lanjutan',
        ]);
    }

    public function test_tenant_does_not_see_other_tenant_thread(): void
    {
        $this->tenantMessage('Pesan rahasia tenant lain', $this->otherTenant);

        $this->actingAs($this->tenant)
            ->get(route('tenant.support.index'))
            ->assertOk()
            ->assertDontSee('Pesan rahasia tenant lain');

        $this->actingAs($this->otherTenant)
            ->get(route('tenant.support.index'))
            ->assertOk()
            ->assertSee('Pesan rahasia tenant lain');
    }

    public function test_non_admin_cannot_access_admin_support(): void
    {
        $this->tenantMessage();

        $this->actingAs($this->owner)->get(route('admin.support.index'))->assertForbidden();
        $this->actingAs($this->tenant)->get(route('admin.support.index'))->assertForbidden();
        $this->actingAs($this->owner)->get(route('admin.support.show', $this->tenant))->assertForbidden();
        $this->actingAs($this->owner)->post(route('admin.support.store', $this->tenant), [
            'message' => 'Bukan CS',
        ])->assertForbidden();

        $this->assertDatabaseMissing('support_messages', ['message' => 'Bukan CS']);
    }

    public function test_admin_support_show_only_accepts_tenant_accounts(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.support.show', $this->owner))
            ->assertNotFound();

        $this->actingAs($this->admin)
            ->post(route('admin.support.store', $this->owner), ['message' => 'Halo'])
            ->assertNotFound();
    }

    public function test_opening_support_marks_messages_read(): void
    {
        $incoming = $this->csReply('Balasan yang belum dibaca');
        $this->assertNull($incoming->fresh()->read_at);

        $this->actingAs($this->tenant)->get(route('tenant.support.index'))->assertOk();
        $this->assertNotNull($incoming->fresh()->read_at);

        $fromTenant = $this->tenantMessage('Pesan yang belum dibaca CS');
        $this->assertNull($fromTenant->fresh()->read_at);

        $this->actingAs($this->admin)->get(route('admin.support.show', $this->tenant))->assertOk();
        $this->assertNotNull($fromTenant->fresh()->read_at);
    }

    public function test_support_unread_badge_is_shared_with_layout(): void
    {
        $this->csReply('Balasan untuk tenant');

        $this->actingAs($this->tenant)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Bantuan & CS')
            ->assertViewHas('sidebarBadges', fn (array $badges) => ($badges['supportUnread'] ?? 0) === 1);

        SupportMessage::create([
            'tenant_id' => $this->otherTenant->id,
            'sender_id' => $this->otherTenant->id,
            'receiver_id' => $this->otherAdmin->id,
            'message' => 'Butuh bantuan',
        ]);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pesan CS')
            ->assertViewHas('sidebarBadges', fn (array $badges) => ($badges['supportUnread'] ?? 0) === 1);
    }

    public function test_latest_endpoint_reports_new_cs_replies(): void
    {
        $first = $this->tenantMessage('Pesan awal');
        $reply = $this->csReply('Balasan CS');

        $this->actingAs($this->tenant)
            ->getJson(route('tenant.support.latest', ['since' => $first->id]))
            ->assertOk()
            ->assertJsonPath('data.latest_id', $reply->id)
            ->assertJsonPath('data.new_count', 1)
            ->assertJsonPath('data.unread', 1);
    }
}
