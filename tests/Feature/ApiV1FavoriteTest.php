<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1FavoriteTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'tenant',
            'password' => Hash::make('secret123'),
        ], $overrides));
    }

    private function makeActiveKos(): Kos
    {
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 850000]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 1200000]);

        return $kos;
    }

    // ---------------------------------------------------------------- index

    public function test_favorites_index_returns_locked_contract_shape(): void
    {
        $user = $this->makeUser();
        $kos = $this->makeActiveKos();
        $favorite = Favorite::factory()->create(['user_id' => $user->id, 'kos_id' => $kos->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/favorites');

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'kos' => [
                        'id',
                        'name',
                        'slug',
                        'address',
                        'city',
                        'photo',
                        'price_min',
                        'price_max',
                        'is_available',
                    ],
                    'created_at',
                ]],
                'meta' => ['current_page', 'per_page', 'last_page', 'total'],
            ]);

        $item = $response->json('data.0');
        $this->assertSame($favorite->id, $item['id']);
        $this->assertSame($kos->id, $item['kos']['id']);
        $this->assertSame(850000, (int) $item['kos']['price_min']);
        $this->assertSame(1200000, (int) $item['kos']['price_max']);
        $this->assertTrue($item['kos']['is_available']);
        $this->assertSame($favorite->created_at->toISOString(), $item['created_at']);
    }

    public function test_favorites_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/favorites')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_favorites_index_requires_tenant_role(): void
    {
        $owner = $this->makeUser(['role' => 'owner']);
        $token = $owner->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/favorites')
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_favorites_index_returns_only_own_favorites(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $kosOwn = $this->makeActiveKos();
        $kosOther = $this->makeActiveKos();

        Favorite::factory()->create(['user_id' => $user->id, 'kos_id' => $kosOwn->id]);
        Favorite::factory()->create(['user_id' => $other->id, 'kos_id' => $kosOther->id]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/favorites');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame($kosOwn->id, $response->json('data.0.kos.id'));
    }

    public function test_favorites_index_ignores_user_id_query(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $kos = $this->makeActiveKos();

        Favorite::factory()->create(['user_id' => $user->id, 'kos_id' => $kos->id]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/favorites?user_id='.$other->id);

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame($user->id, Favorite::query()->where('kos_id', $kos->id)->value('user_id'));
    }

    public function test_favorites_index_returns_empty_collection_with_200(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/favorites');

        $response->assertOk()
            ->assertJson([
                'data' => [],
                'meta' => ['current_page' => 1, 'per_page' => 15, 'last_page' => 1, 'total' => 0],
            ]);
    }

    public function test_favorites_index_nested_kos_uses_public_fields_only(): void
    {
        $user = $this->makeUser();
        $kos = $this->makeActiveKos();
        Favorite::factory()->create(['user_id' => $user->id, 'kos_id' => $kos->id]);

        $token = $user->createToken('test')->plainTextToken;

        $item = $this->withToken($token)->getJson('/api/v1/favorites')->json('data.0.kos');

        $this->assertArrayNotHasKey('owner_id', $item);
        $this->assertArrayNotHasKey('description', $item);
        $this->assertArrayNotHasKey('phone', $item);
        $this->assertArrayNotHasKey('rules', $item);
        $this->assertArrayNotHasKey('payment_info', $item);
        $this->assertArrayNotHasKey('status', $item);
        $this->assertArrayNotHasKey('created_at', $item);
        $this->assertArrayNotHasKey('available_rooms', $item);
    }

    public function test_favorites_index_caps_per_page_at_50(): void
    {
        $user = $this->makeUser();
        $kos = $this->makeActiveKos();
        Favorite::factory()->create(['user_id' => $user->id, 'kos_id' => $kos->id]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/favorites?per_page=200');

        $response->assertOk();
        $this->assertSame(50, $response->json('meta.per_page'));
    }

    // ---------------------------------------------------------------- toggle

    public function test_toggle_adds_favorite_when_absent(): void
    {
        $user = $this->makeUser();
        $kos = $this->makeActiveKos();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/v1/favorites/{$kos->id}/toggle");

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJson([
                'data' => ['kos_id' => $kos->id, 'is_favorited' => true],
                'message' => 'Kos ditambahkan ke favorit.',
            ]);

        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'kos_id' => $kos->id]);
    }

    public function test_toggle_removes_favorite_when_exists(): void
    {
        $user = $this->makeUser();
        $kos = $this->makeActiveKos();
        Favorite::factory()->create(['user_id' => $user->id, 'kos_id' => $kos->id]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/v1/favorites/{$kos->id}/toggle");

        $response->assertOk()
            ->assertJson([
                'data' => ['kos_id' => $kos->id, 'is_favorited' => false],
                'message' => 'Kos dihapus dari favorit.',
            ]);

        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'kos_id' => $kos->id]);
    }

    public function test_repeated_toggle_does_not_create_duplicate_favorites(): void
    {
        $user = $this->makeUser();
        $kos = $this->makeActiveKos();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson("/api/v1/favorites/{$kos->id}/toggle")->assertOk();
        $this->withToken($token)->postJson("/api/v1/favorites/{$kos->id}/toggle")->assertOk();

        $this->assertSame(0, Favorite::where('user_id', $user->id)->where('kos_id', $kos->id)->count());
    }

    public function test_toggle_requires_authentication(): void
    {
        $kos = $this->makeActiveKos();

        $this->postJson("/api/v1/favorites/{$kos->id}/toggle")
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_toggle_requires_tenant_role(): void
    {
        $owner = $this->makeUser(['role' => 'owner']);
        $kos = $this->makeActiveKos();
        $token = $owner->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson("/api/v1/favorites/{$kos->id}/toggle")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_toggle_returns_404_for_unknown_kos(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/favorites/999999/toggle')
            ->assertStatus(404)
            ->assertJson(['message' => 'Resource not found.']);
    }
}
