<?php

namespace Tests\Feature;

use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GapPriorityTest extends TestCase
{
    use RefreshDatabase;

    private function makeStay(User $owner): array
    {
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active',
        ]);

        return [$tenant, $kos, $kamar, $penghuni];
    }

    // ========== GAP1 EDIT INQUIRY ==========

    public function test_seeker_can_edit_own_unreplied_message(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos] = $this->makeStay($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);
        $msg = Message::factory()->create([
            'kos_id' => $kos->id, 'sender_id' => $seeker->id, 'receiver_id' => $owner->id,
            'message' => 'Typo', 'category' => 'kamar',
        ]);

        $response = $this->actingAs($seeker)->put(route('tenant.inquiries.update', $msg), [
            'message' => 'Typo diperbaiki.',
        ]);

        $response->assertRedirect();
        $this->assertSame('Typo diperbaiki.', $msg->fresh()->message);
    }

    public function test_cannot_edit_after_owner_reply(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos] = $this->makeStay($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);
        $msg = Message::factory()->create([
            'kos_id' => $kos->id, 'sender_id' => $seeker->id, 'receiver_id' => $owner->id, 'message' => 'Lama',
        ]);
        Message::factory()->create([
            'kos_id' => $kos->id, 'sender_id' => $owner->id, 'receiver_id' => $seeker->id, 'message' => 'Balasan.',
        ]);

        $this->actingAs($seeker)->put(route('tenant.inquiries.update', $msg), ['message' => 'Ubah'])
            ->assertStatus(422);
    }

    public function test_other_user_cannot_edit(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos] = $this->makeStay($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);
        $msg = Message::factory()->create([
            'kos_id' => $kos->id, 'sender_id' => $seeker->id, 'receiver_id' => $owner->id, 'message' => 'X',
        ]);

        $other = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($other)->put(route('tenant.inquiries.update', $msg), ['message' => 'Y'])
            ->assertForbidden();
    }

    public function test_api_edit_inquiry_is_scoped(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos] = $this->makeStay($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);
        $msg = Message::factory()->create([
            'kos_id' => $kos->id, 'sender_id' => $seeker->id, 'receiver_id' => $owner->id, 'message' => 'T',
        ]);
        Sanctum::actingAs($seeker);

        $this->putJson(route('api.v1.inquiries.update', $msg), ['message' => 'T ubah.'])->assertOk();
        $this->assertSame('T ubah.', $msg->fresh()->message);
    }

    // ========== GAP2 BULK APPROVE CHECKOUT ==========

    public function test_owner_can_bulk_approve_pending_checkouts(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$t1, $k, $kamar, $p1] = $this->makeStay($owner);
        [$t2, , , $p2] = $this->makeStay($owner);
        $co1 = CheckOut::create(['penghuni_id' => $p1->id, 'kamar_id' => $kamar->id, 'request_date' => now(), 'status' => 'pending']);
        $co2 = CheckOut::create(['penghuni_id' => $p2->id, 'kamar_id' => $p2->kamar_id, 'request_date' => now(), 'status' => 'pending']);

        $this->actingAs($owner)->post(route('owner.checkout.bulk-approve'), ['ids' => [$co1->id, $co2->id]])
            ->assertRedirect();

        $this->assertSame('approved', $co1->fresh()->status);
        $this->assertSame('approved', $co2->fresh()->status);
    }

    public function test_bulk_approve_skips_unpaid_and_out_of_scope(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$t1, $k, $kamar, $p1] = $this->makeStay($owner);
        $co1 = CheckOut::create(['penghuni_id' => $p1->id, 'kamar_id' => $kamar->id, 'request_date' => now(), 'status' => 'pending']);
        Tagihan::factory()->create(['penghuni_id' => $p1->id, 'kamar_id' => $kamar->id, 'status' => 'overdue', 'total' => 500000, 'subtotal' => 500000]);

        $ownerB = User::factory()->create(['role' => 'owner']);
        [$t2, , , $p2] = $this->makeStay($ownerB);
        $co2 = CheckOut::create(['penghuni_id' => $p2->id, 'kamar_id' => $p2->kamar_id, 'request_date' => now(), 'status' => 'pending']);

        $response = $this->actingAs($owner)->post(route('owner.checkout.bulk-approve'), ['ids' => [$co1->id, $co2->id]]);
        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $this->assertSame('pending', $co1->fresh()->status);
        $this->assertSame('pending', $co2->fresh()->status);
    }

    public function test_tenant_cannot_bulk_approve(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, , , $p1] = $this->makeStay($owner);
        $co = CheckOut::create(['penghuni_id' => $p1->id, 'kamar_id' => $p1->kamar_id, 'request_date' => now(), 'status' => 'pending']);

        $this->actingAs($tenant)->post(route('owner.checkout.bulk-approve'), ['ids' => [$co->id]])
            ->assertForbidden();
    }

    public function test_api_bulk_approve_checkout(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$t1, $k, $kamar, $p1] = $this->makeStay($owner);
        $co = CheckOut::create(['penghuni_id' => $p1->id, 'kamar_id' => $kamar->id, 'request_date' => now(), 'status' => 'pending']);
        Sanctum::actingAs($owner);

        $this->postJson(route('api.v1.owner.checkout.bulk-approve'), ['ids' => [$co->id]])
            ->assertOk()
            ->assertJsonPath('approved', 1);

        $this->assertSame('approved', $co->fresh()->status);
    }

    // ========== GAP3 API PROFILE ==========

    public function test_any_role_can_update_profile_via_api(): void
    {
        foreach (['tenant', 'owner', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role, 'phone' => 'lama']);
            Sanctum::actingAs($user);

            $this->putJson(route('api.v1.me.update'), ['name' => "New {$role}", 'phone' => '08111111'])
                ->assertOk()
                ->assertJsonPath('data.name', "New {$role}");

            $this->assertSame('08111111', $user->fresh()->phone);
        }
    }

    public function test_tenant_profile_ignores_identity_number(): void
    {
        $user = User::factory()->create([
            'role' => 'tenant',
            'identity_number' => '3201010101010001',
        ]);
        Sanctum::actingAs($user);

        $this->putJson(route('api.v1.me.update'), ['identity_number' => str_repeat('1', 51)])
            ->assertOk();

        $this->assertSame('3201010101010001', $user->fresh()->identity_number);
    }

    public function test_profile_update_rejects_invalid_data(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        Sanctum::actingAs($user);

        $this->putJson(route('api.v1.me.update'), ['phone' => str_repeat('1', 21)])
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone');
    }

    public function test_guest_cannot_update_profile(): void
    {
        $this->putJson(route('api.v1.me.update'), ['name' => 'X'])
            ->assertUnauthorized();
    }
}
