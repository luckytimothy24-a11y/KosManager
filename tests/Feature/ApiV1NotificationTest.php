<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private User $otherTenant;

    private User $owner;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $this->otherTenant = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $this->owner = User::factory()->create(['role' => 'owner']);
    }

    private function makeNotification(User $user, array $overrides = []): Notification
    {
        return Notification::factory()->create(array_merge([
            'user_id' => $user->id,
            'type' => 'booking',
            'title' => 'Booking Disetujui',
            'message' => 'Booking Anda disetujui oleh pengelola.',
            'is_read' => false,
            'data' => ['booking_id' => 123, 'unique_key' => 'booking-approved:123'],
        ], $overrides));
    }

    // ---------------------------------------------------------------- index

    public function test_index_returns_own_notifications_contract_shape(): void
    {
        $this->makeNotification($this->tenant);

        $response = $this->withToken($this->token($this->tenant))->getJson('/api/v1/notifications');

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'type',
                    'title',
                    'message',
                    'is_read',
                    'created_at',
                    'updated_at',
                ]],
                'meta' => ['current_page', 'per_page', 'last_page', 'total'],
            ]);

        $item = $response->json('data.0');
        $this->assertSame('Booking Disetujui', $item['title']);
        $this->assertFalse($item['is_read']);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/notifications')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_index_requires_tenant_role(): void
    {
        $this->withToken($this->token($this->owner))->getJson('/api/v1/notifications')
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_index_returns_only_own_notifications(): void
    {
        $own = $this->makeNotification($this->tenant);
        $other = $this->makeNotification($this->otherTenant);

        $response = $this->withToken($this->token($this->tenant))->getJson('/api/v1/notifications');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($own->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_index_returns_empty_collection_with_200(): void
    {
        $fresh = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('x')]);

        $response = $this->withToken($this->token($fresh))->getJson('/api/v1/notifications');

        $response->assertOk()
            ->assertJson(['data' => [], 'meta' => ['total' => 0]]);
    }

    public function test_index_defaults_and_caps_per_page(): void
    {
        $this->makeNotification($this->tenant);

        $default = $this->withToken($this->token($this->tenant))->getJson('/api/v1/notifications');
        $this->assertSame(15, $default->json('meta.per_page'));

        $capped = $this->withToken($this->token($this->tenant))->getJson('/api/v1/notifications?per_page=200');
        $this->assertSame(50, $capped->json('meta.per_page'));
    }

    public function test_index_uses_latest_ordering(): void
    {
        $older = $this->makeNotification($this->tenant, ['title' => 'Lama', 'created_at' => now()->subDays(2)]);
        $newer = $this->makeNotification($this->tenant, ['title' => 'Baru', 'created_at' => now()]);

        $response = $this->withToken($this->token($this->tenant))->getJson('/api/v1/notifications');

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertSame([$newer->id, $older->id], $ids);
    }

    public function test_index_does_not_expose_data_payload_or_user_id(): void
    {
        $this->makeNotification($this->tenant, ['data' => ['booking_id' => 123, 'unique_key' => 'booking-approved:123']]);

        $item = $this->withToken($this->token($this->tenant))->getJson('/api/v1/notifications')->json('data.0');

        $this->assertArrayNotHasKey('data', $item);
        $this->assertArrayNotHasKey('user_id', $item);
    }

    // ---------------------------------------------------------------- show

    public function test_show_returns_notification_detail(): void
    {
        $notification = $this->makeNotification($this->tenant);

        $response = $this->withToken($this->token($this->tenant))
            ->getJson("/api/v1/notifications/{$notification->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'type',
                'title',
                'message',
                'is_read',
                'created_at',
                'updated_at',
            ]]);
        $this->assertSame($notification->id, $response->json('data.id'));
    }

    public function test_show_denies_other_tenant_notification(): void
    {
        $other = $this->makeNotification($this->otherTenant);

        $this->withToken($this->token($this->tenant))->getJson("/api/v1/notifications/{$other->id}")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_show_requires_authentication(): void
    {
        $notification = $this->makeNotification($this->tenant);

        $this->getJson("/api/v1/notifications/{$notification->id}")
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_show_requires_tenant_role(): void
    {
        $notification = $this->makeNotification($this->tenant);

        $this->withToken($this->token($this->owner))->getJson("/api/v1/notifications/{$notification->id}")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_show_returns_404_for_missing_notification(): void
    {
        $this->withToken($this->token($this->tenant))->getJson('/api/v1/notifications/999999')
            ->assertStatus(404)
            ->assertJson(['message' => 'Resource not found.']);
    }

    public function test_show_does_not_change_read_state(): void
    {
        $notification = $this->makeNotification($this->tenant, ['is_read' => false]);

        $this->withToken($this->token($this->tenant))->getJson("/api/v1/notifications/{$notification->id}");

        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'is_read' => 0]);
    }

    public function test_show_does_not_expose_internal_payload(): void
    {
        $notification = $this->makeNotification($this->tenant, ['data' => ['booking_id' => 123]]);

        $data = $this->withToken($this->token($this->tenant))
            ->getJson("/api/v1/notifications/{$notification->id}")
            ->json('data');

        $this->assertArrayNotHasKey('data', $data);
        $this->assertArrayNotHasKey('user_id', $data);
    }

    // -------------------------------------------------------- mark read

    public function test_read_marks_unread_as_read(): void
    {
        $notification = $this->makeNotification($this->tenant, ['is_read' => false]);

        $response = $this->withToken($this->token($this->tenant))
            ->postJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJson(['data' => ['id' => $notification->id, 'is_read' => true]]);
        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'is_read' => 1]);
    }

    public function test_read_is_idempotent_for_already_read(): void
    {
        $notification = $this->makeNotification($this->tenant, ['is_read' => true]);

        $response = $this->withToken($this->token($this->tenant))
            ->postJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJson(['data' => ['id' => $notification->id, 'is_read' => true]]);
        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'is_read' => 1]);
    }

    public function test_read_denies_other_tenant_notification(): void
    {
        $other = $this->makeNotification($this->otherTenant, ['is_read' => false]);

        $this->withToken($this->token($this->tenant))->postJson("/api/v1/notifications/{$other->id}/read")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);

        $this->assertDatabaseHas('notifications', ['id' => $other->id, 'is_read' => 0]);
    }

    public function test_read_requires_authentication(): void
    {
        $notification = $this->makeNotification($this->tenant);

        $this->postJson("/api/v1/notifications/{$notification->id}/read")
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_read_requires_tenant_role(): void
    {
        $notification = $this->makeNotification($this->tenant);

        $this->withToken($this->token($this->owner))->postJson("/api/v1/notifications/{$notification->id}/read")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_read_returns_404_for_missing_notification(): void
    {
        $this->withToken($this->token($this->tenant))->postJson('/api/v1/notifications/999999/read')
            ->assertStatus(404)
            ->assertJson(['message' => 'Resource not found.']);
    }
}
