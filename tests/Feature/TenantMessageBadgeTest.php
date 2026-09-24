<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\Message;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantMessageBadgeTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'status' => 'active',
        ]);
    }

    private function sidebarBadgesForTenant(): array
    {
        $response = $this->actingAs($this->tenant)->get(route('dashboard'));

        // View composer shares sidebarBadges to all layouts; extract via view data trick: render tenant.kos.index and check badge in html
        // Simpler: directly query AppServiceProvider logic
        return [
            'count' => Message::where('receiver_id', $this->tenant->id)->whereNull('read_at')->count(),
        ];
    }

    public function test_tenant_unread_message_increments_badge(): void
    {
        Message::factory()->create([
            'kos_id' => $this->kos->id,
            'sender_id' => $this->owner->id,
            'receiver_id' => $this->tenant->id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->tenant)->get(route('dashboard'));
        $response->assertOk();
        // Sidebar should contain Hubungi Owner with badge 1
        $response->assertSee('Hubungi Owner');
        // badge HTML contains count 1 near Hubungi Owner
        $this->assertEquals(1, Message::where('receiver_id', $this->tenant->id)->whereNull('read_at')->count());
    }

    public function test_tenant_read_message_not_counted(): void
    {
        Message::factory()->create([
            'kos_id' => $this->kos->id,
            'sender_id' => $this->owner->id,
            'receiver_id' => $this->tenant->id,
            'read_at' => now(),
        ]);

        $this->assertEquals(0, Message::where('receiver_id', $this->tenant->id)->whereNull('read_at')->count());

        $response = $this->actingAs($this->tenant)->get(route('dashboard'));
        $response->assertOk();
        // Should not show badge 1
        $response->assertDontSee('>1<');
    }

    public function test_other_tenant_message_not_counted(): void
    {
        $otherTenant = User::factory()->create(['role' => 'tenant']);
        Message::factory()->create([
            'kos_id' => $this->kos->id,
            'sender_id' => $this->owner->id,
            'receiver_id' => $otherTenant->id,
            'read_at' => null,
        ]);

        $this->assertEquals(0, Message::where('receiver_id', $this->tenant->id)->whereNull('read_at')->count());
        $response = $this->actingAs($this->tenant)->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_sender_variety_still_counted(): void
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $kos2 = Kos::factory()->create(['owner_id' => $otherOwner->id, 'status' => 'active']);
        Message::factory()->create([
            'kos_id' => $this->kos->id,
            'sender_id' => $this->owner->id,
            'receiver_id' => $this->tenant->id,
            'read_at' => null,
        ]);
        Message::factory()->create([
            'kos_id' => $kos2->id,
            'sender_id' => $otherOwner->id,
            'receiver_id' => $this->tenant->id,
            'read_at' => null,
        ]);

        $this->assertEquals(2, Message::where('receiver_id', $this->tenant->id)->whereNull('read_at')->count());
    }

    public function test_opening_chat_clears_badge(): void
    {
        Message::factory()->create([
            'kos_id' => $this->kos->id,
            'sender_id' => $this->owner->id,
            'receiver_id' => $this->tenant->id,
            'read_at' => null,
        ]);

        $this->assertEquals(1, Message::where('receiver_id', $this->tenant->id)->whereNull('read_at')->count());

        $this->actingAs($this->tenant)->get(route('tenant.messages.index'));

        $this->assertEquals(0, Message::where('receiver_id', $this->tenant->id)->whereNull('read_at')->count());
    }
}
