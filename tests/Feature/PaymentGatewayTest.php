<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PaymentGateway\PaymentGatewayContract;
use App\Services\PaymentGateway\PaymentGatewayManager;
use App\Services\PaymentGateway\SandboxGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    private Penghuni $penghuni;

    private Kontrak $kontrak;

    private Tagihan $tagihan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $this->penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $this->kontrak = Kontrak::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $this->tagihan = Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
        ]);
    }

    /** Sign payload callback seperti gateway. */
    private function sign(array $payload): string
    {
        return hash_hmac('sha256', json_encode($payload), config('payment-gateway.signature_key'));
    }

    // ---------------------------------------------------------------- F1: driver

    public function test_sandbox_gateway_driver_is_resolved_by_manager(): void
    {
        $driver = PaymentGatewayManager::driver();

        $this->assertInstanceOf(PaymentGatewayContract::class, $driver);
        $this->assertInstanceOf(SandboxGateway::class, $driver);
        $this->assertSame('sandbox', $driver->id());
        $this->assertNotEmpty($driver->label());
        $this->assertSame('e_wallet', $driver->paymentMethod());
    }

    public function test_fake_driver_resolution_throws(): void
    {
        config(['payment-gateway.default' => 'midtrans']);

        $this->expectException(\InvalidArgumentException::class);
        PaymentGatewayManager::driver();
    }

    public function test_sandbox_creates_charge_with_reference(): void
    {
        $ce = PaymentGatewayManager::driver()->createCharge([
            'payment_number' => 'PY-ABC',
            'amount' => 1500000,
        ]);

        $this->assertArrayHasKey('reference', $ce);
        $this->assertStringStartsWith('VA-', $ce['reference']);
        $this->assertNotEmpty($ce['instructions']);
        $this->assertStringContainsString('1.500.000', $ce['instructions']);
    }

    public function test_signature_verification(): void
    {
        $payload = ['gateway_reference' => 'VA-ABC', 'status' => 'success', 'amount' => 1500000];
        $badPayload = array_merge($payload, ['amount' => 999]);

        $this->assertTrue(PaymentGatewayManager::driver()->verifySignature($payload, $this->sign($payload)));
        $this->assertFalse(PaymentGatewayManager::driver()->verifySignature($payload, 'wrong-signature'));
        $this->assertFalse(PaymentGatewayManager::driver()->verifySignature($badPayload, $this->sign($payload)));
    }

    // ---------------------------------------------------------------- F3: tenant initiates gateway payment

    public function test_tenant_can_create_gateway_payment(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.gateway'), [
            'tagihan_id' => $this->tagihan->id,
        ]);

        $response->assertRedirect();

        $payment = Pembayaran::where('tagihan_id', $this->tagihan->id)->first();

        $this->assertNotNull($payment);
        $this->assertTrue($payment->isFromGateway());
        $this->assertSame('sandbox', $payment->gateway_provider);
        $this->assertNotNull($payment->gateway_reference);
        $this->assertNotNull($payment->gateway_instructions);
        $this->assertSame(Pembayaran::STATUS_PENDING, $payment->verification_status);
        $this->assertTrue($payment->tagihan->fresh()->status === Tagihan::STATUS_PAYMENT_PENDING);
    }

    public function test_tenant_cannot_create_gateway_payment_on_non_payable_tagihan(): void
    {
        $this->tagihan->update(['status' => Tagihan::STATUS_PAID]);

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.gateway'), [
            'tagihan_id' => $this->tagihan->id,
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseMissing('pembayarans', ['tagihan_id' => $this->tagihan->id]);
    }

    public function test_other_tenant_cannot_create_gateway_payment(): void
    {
        $other = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($other)->post(route('tenant.pembayaran.gateway'), [
            'tagihan_id' => $this->tagihan->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('pembayarans', ['tagihan_id' => $this->tagihan->id]);
    }

    public function test_gateway_payment_blocks_duplicate(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.gateway'), [
            'tagihan_id' => $this->tagihan->id,
        ]);

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.gateway'), [
            'tagihan_id' => $this->tagihan->id,
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertSame(1, Pembayaran::where('tagihan_id', $this->tagihan->id)->count());
    }

    // ---------------------------------------------------------------- F4: webhook auto-verification

    public function test_webhook_auto_verifies_payment(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.gateway'), [
            'tagihan_id' => $this->tagihan->id,
        ]);
        $payment = Pembayaran::where('tagihan_id', $this->tagihan->id)->firstOrFail();

        $payload = [
            'gateway_reference' => $payment->gateway_reference,
            'status' => 'success',
            'amount' => (float) $payment->amount,
        ];

        $response = $this->postJson(route('webhook.payment-gateway'), $payload, [
            'X-Gateway-Signature' => $this->sign($payload),
        ]);

        $response->assertOk();
        $this->assertSame(Pembayaran::STATUS_APPROVED, $payment->fresh()->verification_status);
        $this->assertTrue($payment->fresh()->tagihan->fresh()->status === Tagihan::STATUS_PAID);
        $this->assertNotNull($payment->fresh()->verified_at);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.gateway'), [
            'tagihan_id' => $this->tagihan->id,
        ]);
        $payment = Pembayaran::where('tagihan_id', $this->tagihan->id)->firstOrFail();

        $payload = ['gateway_reference' => $payment->gateway_reference, 'status' => 'success', 'amount' => 1];

        $response = $this->postJson(route('webhook.payment-gateway'), $payload, [
            'X-Gateway-Signature' => 'nope',
        ]);

        $response->assertForbidden();
        $this->assertSame(Pembayaran::STATUS_PENDING, $payment->fresh()->verification_status);
    }

    public function test_webhook_ignores_unknown_reference(): void
    {
        $payload = ['gateway_reference' => 'VA-NOPE', 'status' => 'success', 'amount' => 1];

        $response = $this->postJson(route('webhook.payment-gateway'), $payload, [
            'X-Gateway-Signature' => $this->sign($payload),
        ]);

        $response->assertNotFound();
    }

    public function test_webhook_rejects_amount_mismatch(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.gateway'), [
            'tagihan_id' => $this->tagihan->id,
        ]);
        $payment = Pembayaran::where('tagihan_id', $this->tagihan->id)->firstOrFail();

        $payload = [
            'gateway_reference' => $payment->gateway_reference,
            'status' => 'success',
            'amount' => (float) $payment->amount + 1,
        ];

        $response = $this->postJson(route('webhook.payment-gateway'), $payload, [
            'X-Gateway-Signature' => $this->sign($payload),
        ]);

        $response->assertStatus(422);
        $this->assertSame(Pembayaran::STATUS_PENDING, $payment->fresh()->verification_status);
    }

    public function test_webhook_is_idempotent(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.gateway'), [
            'tagihan_id' => $this->tagihan->id,
        ]);
        $payment = Pembayaran::where('tagihan_id', $this->tagihan->id)->firstOrFail();

        $payload = [
            'gateway_reference' => $payment->gateway_reference,
            'status' => 'success',
            'amount' => (float) $payment->amount,
        ];
        $headers = ['X-Gateway-Signature' => $this->sign($payload)];

        $this->postJson(route('webhook.payment-gateway'), $payload, $headers)->assertOk();
        $this->assertSame(2, \DB::table('audit_logs')->count());
        $second = $this->postJson(route('webhook.payment-gateway'), $payload, $headers);

        $second->assertOk();
        $this->assertSame(Pembayaran::STATUS_APPROVED, $payment->fresh()->verification_status);
        $this->assertSame(2, \DB::table('audit_logs')->count(), 'Webhook kedua tidak boleh menambah audit log.');
    }

    public function test_webhook_passes_through_for_non_success_status(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.gateway'), [
            'tagihan_id' => $this->tagihan->id,
        ]);
        $payment = Pembayaran::where('tagihan_id', $this->tagihan->id)->firstOrFail();

        $payload = [
            'gateway_reference' => $payment->gateway_reference,
            'status' => 'pending',
            'amount' => (float) $payment->amount,
        ];

        $response = $this->postJson(route('webhook.payment-gateway'), $payload, [
            'X-Gateway-Signature' => $this->sign($payload),
        ]);

        $response->assertOk();
        $this->assertSame(Pembayaran::STATUS_PENDING, $payment->fresh()->verification_status);
    }

    // ---------------------------------------------------------------- F5: views

    public function test_tenant_tagihan_show_offers_gateway_payment(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee('Bayar Online');
        $response->assertSee('Buat Pembayaran Online');
        $response->assertSee(route('tenant.pembayaran.gateway'), false);
    }

    public function test_tenant_pembayaran_show_shows_gateway_instructions(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.gateway'), [
            'tagihan_id' => $this->tagihan->id,
        ]);
        $payment = Pembayaran::where('tagihan_id', $this->tagihan->id)->firstOrFail();

        $response = $this->actingAs($this->tenant)->get(route('tenant.pembayaran.show', $payment));

        $response->assertOk();
        $response->assertSee('Instruksi Pembayaran Online');
        $response->assertSee($payment->gateway_reference, false);
        $response->assertSee('memverifikasi otomatis', false);
    }

    public function test_owner_pembayaran_show_marks_gateway_source(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.gateway'), [
            'tagihan_id' => $this->tagihan->id,
        ]);
        $payment = Pembayaran::where('tagihan_id', $this->tagihan->id)->firstOrFail();

        $response = $this->actingAs($this->owner)
            ->get(route('owner.pembayaran.show', $payment));

        $response->assertOk();
        $response->assertSee('Pembayaran Online');
        $response->assertSee($payment->gateway_reference, false);
    }

    public function test_owner_cannot_manually_verify_gateway_payment_already_approved(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.gateway'), [
            'tagihan_id' => $this->tagihan->id,
        ]);
        $payment = Pembayaran::where('tagihan_id', $this->tagihan->id)->firstOrFail();

        $payload = [
            'gateway_reference' => $payment->gateway_reference,
            'status' => 'success',
            'amount' => (float) $payment->amount,
        ];
        $this->postJson(route('webhook.payment-gateway'), $payload, [
            'X-Gateway-Signature' => $this->sign($payload),
        ])->assertOk();

        $response = $this->actingAs($this->owner)
            ->post(route('owner.pembayaran.verify', $payment));

        $response->assertStatus(400);
    }
}
