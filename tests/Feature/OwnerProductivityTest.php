<?php

namespace Tests\Feature;

use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OwnerProductivityTest extends TestCase
{
    use RefreshDatabase;

    private function makeStay(User $owner): array
    {
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        return [$tenant, $kos, $kamar, $penghuni];
    }

    private function makePendingPayment(User $owner, float $total = 1000000): array
    {
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
            'total' => $total,
            'subtotal' => $total,
        ]);
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => $total,
            'verification_status' => 'pending',
        ]);

        return [$pembayaran, $tagihan];
    }

    // ========== O2 BULK VERIFY ==========

    public function test_owner_can_bulk_verify_pending_payments(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$p1] = $this->makePendingPayment($owner);
        [$p2] = $this->makePendingPayment($owner, 750000);

        $this->actingAs($owner)->post(route('owner.pembayaran.bulk-verify'), [
            'ids' => [$p1->id, $p2->id],
        ])->assertRedirect(route('owner.pembayaran.index'));

        $this->assertSame('approved', $p1->fresh()->verification_status);
        $this->assertSame('approved', $p2->fresh()->verification_status);
        $this->assertSame('paid', $p1->fresh()->tagihan->status);
    }

    public function test_bulk_verify_skips_invalid_items_per_item(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$valid] = $this->makePendingPayment($owner);

        $ownerB = User::factory()->create(['role' => 'owner']);
        [$foreign] = $this->makePendingPayment($ownerB);

        [$mismatch, $tagihanMismatch] = $this->makePendingPayment($owner, 1000000);
        $mismatch->update(['amount' => 500000]);

        $already = $this->makePendingPayment($owner)[0];
        $already->update(['verification_status' => 'approved']);

        $response = $this->actingAs($owner)->post(route('owner.pembayaran.bulk-verify'), [
            'ids' => [$valid->id, $foreign->id, $mismatch->id, $already->id],
        ]);

        $response->assertRedirect(route('owner.pembayaran.index'));
        $response->assertSessionHas('warning');
        $this->assertSame('approved', $valid->fresh()->verification_status);
        $this->assertSame('pending', $foreign->fresh()->verification_status);
        $this->assertSame('pending', $mismatch->fresh()->verification_status);
    }

    public function test_bulk_verify_validates_and_caps_ids(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->post(route('owner.pembayaran.bulk-verify'), ['ids' => []])
            ->assertSessionHasErrors('ids');
        $this->actingAs($owner)->post(route('owner.pembayaran.bulk-verify'), [
            'ids' => range(1, 51),
        ])->assertSessionHasErrors('ids');
    }

    public function test_tenant_cannot_hit_bulk_verify(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$p] = $this->makePendingPayment($owner);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($tenant)->post(route('owner.pembayaran.bulk-verify'), [
            'ids' => [$p->id],
        ])->assertForbidden();
    }

    // ========== O4 API WRITE ==========

    public function test_api_owner_verify_and_reject_payment(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$p1] = $this->makePendingPayment($owner);
        [$p2] = $this->makePendingPayment($owner);
        Sanctum::actingAs($owner);

        $this->postJson(route('api.v1.owner.pembayaran.verify', $p1))->assertOk();
        $this->assertSame('approved', $p1->fresh()->verification_status);

        $this->postJson(route('api.v1.owner.pembayaran.reject', $p2), ['reason' => 'Bukti tidak jelas.'])
            ->assertOk();
        $this->assertSame('rejected', $p2->fresh()->verification_status);

        // Nominal mismatch ditolak.
        [$p3] = $this->makePendingPayment($owner);
        $p3->update(['amount' => 100]);
        $this->postJson(route('api.v1.owner.pembayaran.verify', $p3))->assertStatus(400);
    }

    public function test_api_owner_cannot_verify_foreign_payment(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);
        [$foreign] = $this->makePendingPayment($ownerB);
        Sanctum::actingAs($owner);

        $this->postJson(route('api.v1.owner.pembayaran.verify', $foreign))->assertForbidden();
    }

    public function test_api_owner_cancel_and_reply(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos, $kamar, $penghuni] = $this->makeStay($owner);
        $checkOut = CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $penghuni->kamar_id,
            'request_date' => now(),
            'status' => 'pending',
        ]);
        Message::factory()->create([
            'kos_id' => $kos->id,
            'sender_id' => $tenant->id,
            'receiver_id' => $owner->id,
            'message' => 'Halo.',
        ]);
        Sanctum::actingAs($owner);

        $this->postJson(route('api.v1.owner.checkout.cancel', $checkOut))->assertOk();
        $this->assertSame('cancelled', $checkOut->fresh()->status);

        $this->postJson(route('api.v1.owner.messages.reply', $tenant), ['message' => 'Halo juga.'])
            ->assertCreated();

        // Tenant lain / unrelated ditolak.
        $stranger = User::factory()->create(['role' => 'tenant']);
        $this->postJson(route('api.v1.owner.messages.reply', $stranger), ['message' => 'x'])
            ->assertForbidden();

        // Tenant tidak boleh memakai endpoint owner.
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $penghuni->kamar_id,
            'status' => 'unpaid',
            'total' => 1000000,
            'subtotal' => 1000000,
        ]);
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 1000000,
            'verification_status' => 'pending',
        ]);
        Sanctum::actingAs($tenant);
        $this->postJson(route('api.v1.owner.pembayaran.verify', $pembayaran))->assertForbidden();
    }

    // ========== O1 POLLING ==========

    public function test_owner_latest_endpoint_reports_new_messages(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos] = $this->makeStay($owner);
        $m1 = Message::factory()->create([
            'kos_id' => $kos->id,
            'sender_id' => $tenant->id,
            'receiver_id' => $owner->id,
            'message' => 'Satu.',
        ]);

        $response = $this->actingAs($owner)->getJson(route('owner.messages.latest', $tenant));
        $response->assertOk()->assertJsonPath('data.latest_id', $m1->id);

        $m2 = Message::factory()->create([
            'kos_id' => $kos->id,
            'sender_id' => $tenant->id,
            'receiver_id' => $owner->id,
            'message' => 'Dua.',
        ]);

        $this->actingAs($owner)->getJson(route('owner.messages.latest', [$tenant, 'since' => $m1->id]))
            ->assertOk()
            ->assertJsonPath('data.new_count', 1)
            ->assertJsonPath('data.latest_id', $m2->id);
    }

    public function test_latest_endpoints_enforce_scope(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$tenant, $kos] = $this->makeStay($owner);
        $ownerB = User::factory()->create(['role' => 'owner']);

        // Owner lain tidak ter-link → 403.
        $this->actingAs($ownerB)->getJson(route('owner.messages.latest', $tenant))->assertForbidden();

        // Tenant tanpa hunian → 403.
        $loner = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($loner)->getJson(route('tenant.messages.latest'))->assertForbidden();

        // Halaman thread memuat skrip polling + pil.
        Message::factory()->create([
            'kos_id' => $kos->id,
            'sender_id' => $tenant->id,
            'receiver_id' => $owner->id,
            'message' => 'Halo.',
        ]);
        $this->actingAs($owner)->get(route('owner.messages.show', $tenant))
            ->assertOk()
            ->assertSee('chatPoll', escape: false);
    }
}
