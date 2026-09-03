<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1OwnerPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $otherOwner;

    private User $tenantA;

    private User $tenantB;

    private User $admin;

    private User $superAdmin;

    private Tagihan $tagihan;

    private Tagihan $otherTagihan;

    private Kontrak $kontrak;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->otherOwner = User::factory()->create(['role' => 'owner']);
        $this->tenantA = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $this->tenantB = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);

        // Kos milik owner (A)
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $room = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenantA->id,
            'kos_id' => $kos->id,
            'kamar_id' => $room->id,
            'status' => 'active',
        ]);
        $this->kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $room->id,
            'status' => 'active',
        ]);
        $this->tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $room->id,
            'status' => 'unpaid',
        ]);

        // Kos milik owner lain (B)
        $otherKos = Kos::factory()->create(['owner_id' => $this->otherOwner->id]);
        $otherRoom = Kamar::factory()->create(['kos_id' => $otherKos->id]);
        $otherPenghuni = Penghuni::factory()->create([
            'user_id' => $this->tenantB->id,
            'kos_id' => $otherKos->id,
            'kamar_id' => $otherRoom->id,
            'status' => 'active',
        ]);
        $otherKontrak = Kontrak::factory()->create([
            'penghuni_id' => $otherPenghuni->id,
            'kos_id' => $otherKos->id,
            'kamar_id' => $otherRoom->id,
            'status' => 'active',
        ]);
        $this->otherTagihan = Tagihan::factory()->create([
            'penghuni_id' => $otherPenghuni->id,
            'kontrak_id' => $otherKontrak->id,
            'kamar_id' => $otherRoom->id,
            'status' => 'unpaid',
        ]);
    }

    private function payment(Tagihan $tagihan, array $overrides = []): Pembayaran
    {
        return Pembayaran::factory()->create(array_merge([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $tagihan->penghuni_id,
            'verification_status' => 'pending',
        ], $overrides));
    }

    private function uri(Tagihan $tagihan): string
    {
        return "/api/v1/owner/tagihan/{$tagihan->id}/pembayaran";
    }

    // ------------------------------------------------------------ authentication

    public function test_owner_payment_route_requires_authentication(): void
    {
        $this->getJson($this->uri($this->tagihan))->assertStatus(401);
    }

    public function test_owner_payment_route_rejects_non_owner_roles(): void
    {
        foreach ([
            'tenant' => $this->tenantA,
            'admin' => $this->admin,
            'super_admin' => $this->superAdmin,
        ] as $label => $user) {
            $this->withToken($this->token($user))
                ->getJson($this->uri($this->tagihan))
                ->assertStatus(403);
        }
    }

    // ------------------------------------------------------------- own bill payments

    public function test_returns_payments_of_own_bill(): void
    {
        $this->payment($this->tagihan, ['payment_number' => 'PY-OWN']);

        $response = $this->withToken($this->token($this->owner))
            ->getJson($this->uri($this->tagihan));

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id', 'payment_number', 'tagihan_id', 'amount', 'payment_date',
                    'payment_method', 'verification_status', 'paid_at', 'admin_notes',
                    'gateway_provider', 'proof_available',
                ]],
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.payment_number', 'PY-OWN')
            ->assertJsonPath('data.0.tagihan_id', $this->tagihan->id);
    }

    public function test_returns_empty_when_bill_has_no_payments(): void
    {
        $response = $this->withToken($this->token($this->owner))
            ->getJson($this->uri($this->tagihan));

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_returns_paginated_payments(): void
    {
        $this->payment($this->tagihan);
        $this->payment($this->tagihan);
        $this->payment($this->tagihan);

        $response = $this->withToken($this->token($this->owner))
            ->getJson($this->uri($this->tagihan).'?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2);
    }

    // ------------------------------------------------------------ foreign bill (403)

    public function test_owner_a_cannot_view_payments_of_owner_b_bill(): void
    {
        $this->payment($this->otherTagihan, ['payment_number' => 'PY-B']);

        $this->withToken($this->token($this->owner))
            ->getJson($this->uri($this->otherTagihan))
            ->assertStatus(403);
    }

    public function test_owner_b_cannot_view_payments_of_owner_a_bill(): void
    {
        $this->payment($this->tagihan, ['payment_number' => 'PY-A']);

        $this->withToken($this->token($this->otherOwner))
            ->getJson($this->uri($this->tagihan))
            ->assertStatus(403);
    }

    // ------------------------------------------------------------------- 404

    public function test_returns_404_when_bill_does_not_exist(): void
    {
        $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/owner/tagihan/99999/pembayaran')
            ->assertStatus(404);
    }

    // ------------------------------------------------------------- data leakage

    public function test_response_does_not_leak_payment_proof_path_or_foreign_payment(): void
    {
        $foreignPayment = $this->payment($this->otherTagihan, [
            'payment_number' => 'PY-FOREIGN',
            'proof_file' => 'bukti-pembayaran/secret.pdf',
        ]);
        $ownPayment = $this->payment($this->tagihan, [
            'payment_number' => 'PY-OWN',
            'proof_file' => 'bukti-pembayaran/own.pdf',
        ]);

        $response = $this->withToken($this->token($this->owner))
            ->getJson($this->uri($this->tagihan));

        $response->assertOk()
            ->assertJsonFragment(['id' => $ownPayment->id])
            ->assertJsonMissing(['id' => $foreignPayment->id])
            ->assertJsonMissing(['proof_file' => 'bukti-pembayaran/own.pdf'])
            ->assertJsonMissing(['payment_number' => 'PY-FOREIGN'])
            // field proof only reports boolean availability, not the storage path
            ->assertJsonPath('data.0.proof_available', true);
    }
}
