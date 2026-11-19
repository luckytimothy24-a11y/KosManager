<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IdentityEncryptionTest extends TestCase
{
    use RefreshDatabase;

    private const NIK = '3273010101900001';

    private function makePenghuni(User $owner): Penghuni
    {
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $tenant = User::factory()->create(['role' => 'pencari']);

        return Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'identity_number' => self::NIK,
        ]);
    }

    public function test_identity_number_is_encrypted_at_rest_with_hash(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $penghuni = $this->makePenghuni($owner);

        $raw = DB::table('penghunis')->where('id', $penghuni->id)->first();

        $this->assertNotSame(self::NIK, $raw->identity_number_encrypted);
        $this->assertSame(self::NIK, Crypt::decryptString($raw->identity_number_encrypted));
        $this->assertSame(hash('sha256', self::NIK), $raw->identity_number_hash);
        $this->assertFalse(property_exists($raw, 'identity_number'));
    }

    public function test_accessor_returns_plaintext_identity_number(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $penghuni = $this->makePenghuni($owner);

        $this->assertSame(self::NIK, $penghuni->fresh()->identity_number);
    }

    public function test_identity_number_is_hidden_from_serialization(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $penghuni = $this->makePenghuni($owner);

        $array = $penghuni->fresh()->toArray();

        $this->assertArrayNotHasKey('identity_number_encrypted', $array);
        $this->assertArrayNotHasKey('identity_number_hash', $array);
    }

    public function test_user_identity_number_is_also_encrypted(): void
    {
        $user = User::factory()->create([
            'role' => 'pencari',
            'identity_number' => self::NIK,
        ]);

        $raw = DB::table('users')->where('id', $user->id)->first();

        $this->assertNotSame(self::NIK, $raw->identity_number_encrypted);
        $this->assertSame(hash('sha256', self::NIK), $raw->identity_number_hash);
        $this->assertSame(self::NIK, $user->fresh()->identity_number);
    }

    public function test_identity_hash_helper_normalizes_input(): void
    {
        $this->assertSame(hash('sha256', self::NIK), Penghuni::identityHash(' '.self::NIK.' '));
        $this->assertNull(Penghuni::identityHash(null));
        $this->assertNull(Penghuni::identityHash(''));
    }

    public function test_masking_shows_only_last_four_digits(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $penghuni = $this->makePenghuni($owner);

        $this->assertSame('************0001', $penghuni->maskedIdentityNumber());
    }

    public function test_owner_can_search_penghuni_by_exact_nik(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $penghuni = $this->makePenghuni($owner);

        $other = User::factory()->create(['role' => 'pencari']);
        $otherKos = Kos::factory()->create(['owner_id' => $owner->id]);
        Penghuni::factory()->create([
            'user_id' => $other->id,
            'kos_id' => $otherKos->id,
            'kamar_id' => Kamar::factory()->create(['kos_id' => $otherKos->id])->id,
            'identity_number' => '3273010101909999',
        ]);

        $response = $this->actingAs($owner)->get(route('owner.penghuni.index', ['search' => self::NIK]));

        $response->assertOk();
        $response->assertSee($penghuni->user->name);
        $response->assertDontSee($other->name);
    }

    public function test_owner_can_reveal_identity_and_audit_log_is_written(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $penghuni = $this->makePenghuni($owner);

        $response = $this->actingAs($owner)
            ->getJson(route('owner.penghuni.identity', $penghuni));

        $response->assertOk()->assertJson(['identity_number' => self::NIK]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $owner->id,
            'module' => 'Penghuni',
        ]);

        $log = DB::table('audit_logs')->where('module', 'Penghuni')->latest('id')->first();
        $this->assertStringContainsString('Membuka NIK', $log->description);
    }

    public function test_other_owner_cannot_reveal_identity(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $intruder = User::factory()->create(['role' => 'owner']);
        $penghuni = $this->makePenghuni($owner);

        $this->actingAs($intruder)
            ->getJson(route('owner.penghuni.identity', $penghuni))
            ->assertForbidden();
    }

    public function test_show_page_masks_identity_number(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $penghuni = $this->makePenghuni($owner);

        $response = $this->actingAs($owner)->get(route('owner.penghuni.show', $penghuni));

        $response->assertOk();
        $response->assertSee('************0001');
        $response->assertDontSee(self::NIK);
    }
}
