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

/**
 * Admin ↔ Owner communication (operasional, assigned-kos only).
 * Reuse tabel `messages` existing. Admin ≠ Owner Management.
 */
class AdminOwnerMessageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $ownerA;

    private User $ownerB;

    private User $tenant;

    private Kos $kosA;

    private Kos $kosB;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->ownerA = User::factory()->create(['role' => 'owner']);
        $this->ownerB = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);

        $this->kosA = Kos::factory()->create(['owner_id' => $this->ownerA->id, 'name' => 'KOS-AMSG-A', 'status' => 'active']);
        $this->kosB = Kos::factory()->create(['owner_id' => $this->ownerB->id, 'name' => 'KOS-AMSG-B', 'status' => 'active']);

        $this->admin->assignedKos()->attach($this->kosA->id);
    }

    // ── A. Admin melihat Owner assigned ──────────────────────

    public function test_admin_sees_owner_of_assigned_kos(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.owner-messages.index'));

        $response->assertOk();
        $response->assertSee($this->ownerA->name);
        $response->assertSee('KOS-AMSG-A');
        $response->assertDontSee($this->ownerB->name);
        $response->assertDontSee('KOS-AMSG-B');
    }

    public function test_admin_cannot_see_unassigned_owner(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.owner-messages.index'));
        $response->assertDontSee($this->ownerB->name);
    }

    // ── B/C. Send + view conversation ────────────────────────

    public function test_admin_can_send_message_to_assigned_owner(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.owner-messages.store', ['kos' => $this->kosA, 'owner' => $this->ownerA]), [
                'message' => 'Pembayaran booking #123 sudah diverifikasi.',
                'category' => 'pembayaran',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'kos_id' => $this->kosA->id,
            'sender_id' => $this->admin->id,
            'receiver_id' => $this->ownerA->id,
            'message' => 'Pembayaran booking #123 sudah diverifikasi.',
            'category' => 'pembayaran',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->ownerA->id,
            'type' => 'message',
        ]);
    }

    public function test_admin_cannot_send_to_unassigned_owner(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.owner-messages.store', ['kos' => $this->kosB, 'owner' => $this->ownerB]), [
                'message' => 'Halo owner B',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('messages', 0);
    }

    // ── D. Owner reply ───────────────────────────────────────

    public function test_owner_can_reply_to_admin(): void
    {
        Message::factory()->create([
            'kos_id' => $this->kosA->id,
            'sender_id' => $this->admin->id,
            'receiver_id' => $this->ownerA->id,
            'message' => 'Mohon konfirmasi booking.',
        ]);

        $response = $this->actingAs($this->ownerA)
            ->post(route('owner.messages.reply', $this->admin), [
                'message' => 'Baik, saya konfirmasi sekarang.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'kos_id' => $this->kosA->id,
            'sender_id' => $this->ownerA->id,
            'receiver_id' => $this->admin->id,
            'message' => 'Baik, saya konfirmasi sekarang.',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->id,
            'type' => 'message',
        ]);
    }

    public function test_admin_conversation_shows_owner_reply(): void
    {
        Message::factory()->create([
            'kos_id' => $this->kosA->id,
            'sender_id' => $this->admin->id,
            'receiver_id' => $this->ownerA->id,
            'message' => 'Status pembayaran booking.',
        ]);
        Message::factory()->create([
            'kos_id' => $this->kosA->id,
            'sender_id' => $this->ownerA->id,
            'receiver_id' => $this->admin->id,
            'message' => 'Siap konfirmasi.',
        ]);

        $response = $this->actingAs($this->admin)->get(
            route('admin.owner-messages.show', ['kos' => $this->kosA, 'owner' => $this->ownerA])
        );

        $response->assertOk();
        $response->assertSee('Status pembayaran booking.');
        $response->assertSee('Siap konfirmasi.');
        $response->assertSee($this->ownerA->name);
    }

    // ── E. Conversation isolation / IDOR ─────────────────────

    public function test_admin_cannot_open_unassigned_owner_conversation(): void
    {
        $response = $this->actingAs($this->admin)->get(
            route('admin.owner-messages.show', ['kos' => $this->kosB, 'owner' => $this->ownerB])
        );
        $response->assertForbidden();
    }

    public function test_admin_cannot_open_conversation_with_wrong_owner(): void
    {
        // kos A milik ownerA — admin mencoba akses dengan ownerB
        $response = $this->actingAs($this->admin)->get(
            route('admin.owner-messages.show', ['kos' => $this->kosA, 'owner' => $this->ownerB])
        );
        $response->assertForbidden();
    }

    public function test_admin_cannot_confirm_wrong_owner_on_kos(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.owner-messages.store', ['kos' => $this->kosA, 'owner' => $this->ownerB]), [
                'message' => 'Salah owner',
            ]);
        $response->assertForbidden();
        $this->assertDatabaseCount('messages', 0);
    }

    // ── F. Tenant isolation ──────────────────────────────────

    public function test_tenant_cannot_access_admin_owner_messages(): void
    {
        $this->actingAs($this->tenant)->get(route('admin.owner-messages.index'))->assertForbidden();
        $this->actingAs($this->tenant)->get(
            route('admin.owner-messages.show', ['kos' => $this->kosA, 'owner' => $this->ownerA])
        )->assertForbidden();
        $this->actingAs($this->tenant)->post(
            route('admin.owner-messages.store', ['kos' => $this->kosA, 'owner' => $this->ownerA]),
            ['message' => 'hack']
        )->assertForbidden();
    }

    public function test_owner_cannot_access_admin_owner_messages_page(): void
    {
        $this->actingAs($this->ownerA)->get(route('admin.owner-messages.index'))->assertForbidden();
    }

    // ── G. Owner isolation ───────────────────────────────────

    public function test_owner_b_cannot_open_owner_a_conversation_via_admin_route(): void
    {
        $this->actingAs($this->ownerB)->get(
            route('admin.owner-messages.show', ['kos' => $this->kosA, 'owner' => $this->ownerA])
        )->assertForbidden();
    }

    // ── H. API ───────────────────────────────────────────────

    public function test_api_admin_assigned_owner_allowed(): void
    {
        $token = $this->admin->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/admin/owner-contacts')
            ->assertOk()
            ->assertJsonFragment(['owner_name' => $this->ownerA->name]);

        $this->withToken($token)->postJson("/api/v1/admin/owner-messages/{$this->kosA->id}/{$this->ownerA->id}", [
            'message' => 'API pesan operasional',
        ])->assertStatus(201);

        $this->assertDatabaseHas('messages', [
            'kos_id' => $this->kosA->id,
            'sender_id' => $this->admin->id,
            'receiver_id' => $this->ownerA->id,
            'message' => 'API pesan operasional',
        ]);
    }

    public function test_api_admin_unassigned_owner_denied(): void
    {
        $token = $this->admin->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson("/api/v1/admin/owner-messages/{$this->kosB->id}/{$this->ownerB->id}", [
            'message' => 'hack',
        ])->assertForbidden();

        $this->withToken($token)->getJson("/api/v1/admin/owner-messages/{$this->kosB->id}/{$this->ownerB->id}")
            ->assertForbidden();
    }

    // ── I. XSS / message security ────────────────────────────

    public function test_message_html_is_escaped_not_executed(): void
    {
        $payload = '<script>alert("xss")</script>';

        $this->actingAs($this->admin)
            ->post(route('admin.owner-messages.store', ['kos' => $this->kosA, 'owner' => $this->ownerA]), [
                'message' => $payload,
            ])->assertRedirect();

        $response = $this->actingAs($this->admin)->get(
            route('admin.owner-messages.show', ['kos' => $this->kosA, 'owner' => $this->ownerA])
        );

        $response->assertOk();
        // Blade escaping: script tag tampil sebagai teks, bukan HTML mentah
        $response->assertDontSee('<script>alert("xss")</script>', false);
        $response->assertSee('alert', false);
    }

    public function test_admin_cannot_edit_owner_account_via_message_routes(): void
    {
        // Tidak ada route update/delete owner di admin owner-messages
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn ($r) => str_contains($r->uri(), 'admin/owner') || str_contains($r->uri(), 'owner-messages'));

        foreach ($routes as $route) {
            $this->assertNotContains($route->methods(), ['PUT', 'PATCH', 'DELETE'], 'Owner account mutation tidak boleh ada di fitur komunikasi.');
        }
    }

    // ── J. Existing chat regression ──────────────────────────

    public function test_tenant_owner_chat_still_works(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kosA->id, 'status' => 'occupied']);
        Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kosA->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->tenant)->post(route('tenant.messages.store'), [
            'message' => 'AC rusak',
            'category' => 'fasilitas',
            'kos_id' => $this->kosA->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'kos_id' => $this->kosA->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => $this->ownerA->id,
            'message' => 'AC rusak',
        ]);
    }

    public function test_tenant_inquiry_still_works(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kosA->id, 'status' => 'available']);
        Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kosA->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->tenant)->post(route('tenant.messages.store'), [
            'message' => 'Tanya fasilitas',
            'category' => 'lainnya',
            'kos_id' => $this->kosA->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->tenant->id,
            'receiver_id' => $this->ownerA->id,
        ]);
    }

    public function test_owner_inbox_shows_admin_conversation(): void
    {
        Message::factory()->create([
            'kos_id' => $this->kosA->id,
            'sender_id' => $this->admin->id,
            'receiver_id' => $this->ownerA->id,
            'message' => 'Admin operasional',
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('owner.messages.index'));
        $response->assertOk();
        $response->assertSee($this->admin->name);
        $response->assertSee('Admin operasional');
    }
}
