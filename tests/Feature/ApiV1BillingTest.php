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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiV1BillingTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private User $otherTenant;

    private User $owner;

    private Kos $kos;

    private Kamar $kamar;

    private Penghuni $penghuni;

    private Tagihan $tagihan;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $this->otherTenant = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);

        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied']);

        $this->penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);

        // Penghuni lain milik tenant lain pada kos yang sama (untuk uji isolasi).
        Penghuni::factory()->create([
            'user_id' => $this->otherTenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => Kamar::factory()->create(['kos_id' => $this->kos->id])->id,
            'status' => 'active',
        ]);

        $this->tagihan = Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kamar_id' => $this->kamar->id,
            'kontrak_id' => Kontrak::factory()->create([
                'penghuni_id' => $this->penghuni->id,
                'kos_id' => $this->kos->id,
                'kamar_id' => $this->kamar->id,
                'status' => 'active',
            ])->id,
            'status' => 'unpaid',
            'total' => 1500000,
            'subtotal' => 1500000,
            'discount' => 0,
            'penalty' => 0,
            'due_date' => now()->addDays(7),
        ]);
    }

    private function makeOtherTagihan(): Tagihan
    {
        return Tagihan::factory()->create([
            'penghuni_id' => $this->otherTenant->penghunis()->first()->id,
            'kamar_id' => $this->otherTenant->penghunis()->first()->kamar_id,
            'kontrak_id' => Kontrak::factory()->create([
                'penghuni_id' => $this->otherTenant->penghunis()->first()->id,
                'kos_id' => $this->kos->id,
                'kamar_id' => $this->otherTenant->penghunis()->first()->kamar_id,
                'status' => 'active',
            ])->id,
            'status' => 'unpaid',
            'total' => 1000000,
            'due_date' => now()->addDays(7),
        ]);
    }

    private function validPaymentPayload(): array
    {
        return [
            'amount' => 1500000,
            'payment_method' => 'cash',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 200, 'application/pdf'),
        ];
    }

    // ---------------------------------------------------------------- index

    public function test_index_returns_own_tagihan_contract_shape(): void
    {
        $response = $this->withToken($this->token($this->tenant))->getJson('/api/v1/tagihan');

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'bill_number',
                    'bill_type',
                    'kos_id',
                    'kamar_id',
                    'period_start',
                    'period_end',
                    'subtotal',
                    'discount',
                    'penalty',
                    'total',
                    'due_date',
                    'status',
                    'pembayaran',
                ]],
                'meta' => ['current_page', 'per_page', 'last_page', 'total'],
            ]);

        $this->assertSame($this->tagihan->id, $response->json('data.0.id'));
        $this->assertSame('unpaid', $response->json('data.0.status'));
        $this->assertSame(1500000, (int) $response->json('data.0.total'));
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/tagihan')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_index_requires_tenant_role(): void
    {
        $this->withToken($this->token($this->owner))->getJson('/api/v1/tagihan')
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_index_returns_only_own_tagihan(): void
    {
        $other = $this->makeOtherTagihan();

        $response = $this->withToken($this->token($this->tenant))->getJson('/api/v1/tagihan');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($this->tagihan->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_index_returns_empty_collection_with_200(): void
    {
        $fresh = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('x')]);

        $response = $this->withToken($this->token($fresh))->getJson('/api/v1/tagihan');

        $response->assertOk()
            ->assertJson(['data' => [], 'meta' => ['total' => 0]]);
    }

    public function test_index_defaults_and_caps_per_page(): void
    {
        $default = $this->withToken($this->token($this->tenant))->getJson('/api/v1/tagihan');
        $this->assertSame(15, $default->json('meta.per_page'));

        $capped = $this->withToken($this->token($this->tenant))->getJson('/api/v1/tagihan?per_page=200');
        $this->assertSame(50, $capped->json('meta.per_page'));
    }

    public function test_index_does_not_expose_internal_or_sensitive_fields(): void
    {
        $item = $this->withToken($this->token($this->tenant))->getJson('/api/v1/tagihan')->json('data.0');

        $this->assertArrayNotHasKey('penghuni_id', $item);
        $this->assertArrayNotHasKey('kontrak_id', $item);
        $this->assertArrayNotHasKey('penghuni', $item);
        $this->assertArrayNotHasKey('kontrak', $item);
        $this->assertArrayNotHasKey('kamar', $item);
        $this->assertArrayNotHasKey('created_at', $item);
        $this->assertArrayNotHasKey('updated_at', $item);
        $this->assertArrayNotHasKey('deleted_at', $item);
    }

    // ---------------------------------------------------------------- show

    public function test_show_returns_tagihan_detail(): void
    {
        $response = $this->withToken($this->token($this->tenant))->getJson("/api/v1/tagihan/{$this->tagihan->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'bill_number', 'status', 'total', 'due_date']]);
        $this->assertSame($this->tagihan->bill_number, $response->json('data.bill_number'));
    }

    public function test_show_denies_other_tenant_tagihan(): void
    {
        $other = $this->makeOtherTagihan();

        $this->withToken($this->token($this->tenant))->getJson("/api/v1/tagihan/{$other->id}")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_show_requires_authentication(): void
    {
        $this->getJson("/api/v1/tagihan/{$this->tagihan->id}")
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_show_requires_tenant_role(): void
    {
        $this->withToken($this->token($this->owner))->getJson("/api/v1/tagihan/{$this->tagihan->id}")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_show_returns_404_for_missing_tagihan(): void
    {
        $this->withToken($this->token($this->tenant))->getJson('/api/v1/tagihan/999999')
            ->assertStatus(404)
            ->assertJson(['message' => 'Resource not found.']);
    }

    // ------------------------------------------------- payment store

    public function test_payment_store_submits_cash_payment_and_returns_201(): void
    {
        $response = $this->withToken($this->token($this->tenant))
            ->postJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran", $this->validPaymentPayload());

        $response->assertStatus(201)
            ->assertJson(['message' => 'Pembayaran tunai berhasil dikirim. Menunggu verifikasi pengelola.']);

        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => Pembayaran::STATUS_PENDING,
        ]);
        $this->assertDatabaseHas('tagihans', ['id' => $this->tagihan->id, 'status' => Tagihan::STATUS_PAYMENT_PENDING]);

        $payment = Pembayaran::where('tagihan_id', $this->tagihan->id)->first();
        Storage::disk('local')->assertExists($payment->proof_file);
    }

    public function test_payment_store_accepts_cash_without_proof(): void
    {
        $response = $this->withToken($this->token($this->tenant))->postJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran", [
            'amount' => 1500000,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('pembayarans', ['tagihan_id' => $this->tagihan->id, 'payment_method' => 'cash']);
    }

    public function test_payment_store_requires_authentication(): void
    {
        $this->postJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran", $this->validPaymentPayload())
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_payment_store_requires_tenant_role(): void
    {
        $this->withToken($this->token($this->owner))
            ->postJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran", $this->validPaymentPayload())
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_payment_store_denies_other_tenant_tagihan(): void
    {
        $other = $this->makeOtherTagihan();

        $this->withToken($this->token($this->tenant))
            ->postJson("/api/v1/tagihan/{$other->id}/pembayaran", [
                'amount' => 1000000,
                'payment_method' => 'cash',
            ])
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
        $this->assertDatabaseMissing('pembayarans', ['tagihan_id' => $other->id]);
    }

    public function test_payment_store_validates_amount_required(): void
    {
        $this->withToken($this->token($this->tenant))
            ->postJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran", ['payment_method' => 'cash'])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['amount']]);
    }

    public function test_payment_store_rejects_wrong_amount(): void
    {
        $this->withToken($this->token($this->tenant))
            ->postJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran", [
                'amount' => 500000,
                'payment_method' => 'cash',
            ])
            ->assertStatus(422)
            ->assertJson(['message' => 'The given data was invalid.'])
            ->assertJsonStructure(['errors' => ['amount']]);
    }

    public function test_payment_store_rejects_invalid_method(): void
    {
        $this->withToken($this->token($this->tenant))
            ->postJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran", [
                'amount' => 1500000,
                'payment_method' => 'cheque',
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['payment_method']]);
    }

    public function test_payment_store_rejects_online_methods(): void
    {
        foreach (['transfer_bank', 'e_wallet'] as $method) {
            $this->withToken($this->token($this->tenant))
                ->postJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran", [
                    'amount' => 1500000,
                    'payment_method' => $method,
                    'proof_file' => UploadedFile::fake()->create('bukti.pdf', 200, 'application/pdf'),
                ])
                ->assertStatus(422)
                ->assertJsonStructure(['errors' => ['payment_method']]);
        }
    }

    public function test_payment_store_rejects_invalid_file_type(): void
    {
        $this->withToken($this->token($this->tenant))
            ->postJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran", [
                'amount' => 1500000,
                'payment_method' => 'cash',
                'proof_file' => UploadedFile::fake()->create('bukti.txt', 200, 'text/plain'),
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['proof_file']]);
    }

    public function test_payment_store_rejects_oversized_file(): void
    {
        $this->withToken($this->token($this->tenant))
            ->postJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran", [
                'amount' => 1500000,
                'payment_method' => 'cash',
                'proof_file' => UploadedFile::fake()->create('bukti.pdf', 6000, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['proof_file']]);
    }

    public function test_payment_store_rejects_terminal_tagihan(): void
    {
        $this->tagihan->update(['status' => Tagihan::STATUS_PAID]);

        $this->withToken($this->token($this->tenant))
            ->postJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran", $this->validPaymentPayload())
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['amount']]);
        $this->assertDatabaseMissing('pembayarans', ['tagihan_id' => $this->tagihan->id]);
    }

    public function test_payment_store_prevents_duplicate_active_payment(): void
    {
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => Pembayaran::STATUS_PENDING,
        ]);
        $this->tagihan->update(['status' => Tagihan::STATUS_PAYMENT_PENDING]);

        $this->withToken($this->token($this->tenant))
            ->postJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran", $this->validPaymentPayload())
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['amount']]);

        $this->assertSame(1, Pembayaran::where('tagihan_id', $this->tagihan->id)->count());
    }

    // ------------------------------------------------- payment show

    public function test_payment_show_returns_payment_contract_shape(): void
    {
        $payment = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'payment_method' => 'transfer_bank',
            'verification_status' => Pembayaran::STATUS_PENDING,
            'proof_file' => 'bukti-pembayaran/seed.txt',
        ]);

        $response = $this->withToken($this->token($this->tenant))
            ->getJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran");

        $response->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'payment_number',
                'tagihan_id',
                'amount',
                'payment_date',
                'payment_method',
                'verification_status',
                'paid_at',
                'admin_notes',
                'gateway_provider',
                'proof_available',
            ]]);
        $this->assertSame($payment->id, $response->json('data.id'));
    }

    public function test_payment_show_denies_other_tenant_tagihan(): void
    {
        $other = $this->makeOtherTagihan();

        $this->withToken($this->token($this->tenant))->getJson("/api/v1/tagihan/{$other->id}/pembayaran")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_payment_show_requires_authentication(): void
    {
        $this->getJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran")
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_payment_show_requires_tenant_role(): void
    {
        $this->withToken($this->token($this->owner))->getJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_payment_show_returns_404_when_no_payment_yet(): void
    {
        $this->withToken($this->token($this->tenant))
            ->getJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran")
            ->assertStatus(404)
            ->assertJson(['message' => 'Resource not found.']);
    }

    public function test_payment_show_does_not_expose_internal_or_sensitive_fields(): void
    {
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => Pembayaran::STATUS_PENDING,
            'proof_file' => 'bukti-pembayaran/seed.txt',
        ]);

        $data = $this->withToken($this->token($this->tenant))
            ->getJson("/api/v1/tagihan/{$this->tagihan->id}/pembayaran")
            ->json('data');

        $this->assertArrayNotHasKey('proof_file', $data);
        $this->assertArrayNotHasKey('penghuni_id', $data);
        $this->assertArrayNotHasKey('verified_by', $data);
        $this->assertArrayNotHasKey('verifier', $data);
        $this->assertArrayNotHasKey('created_at', $data);
        $this->assertArrayNotHasKey('updated_at', $data);
        $this->assertArrayNotHasKey('deleted_at', $data);
        $this->assertArrayNotHasKey('gateway_reference', $data);
        $this->assertArrayNotHasKey('gateway_instructions', $data);
    }
}
