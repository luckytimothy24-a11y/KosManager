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
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
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

    /** Buat payment gateway legacy (data historis / simulasi webhook). */
    private function createGatewayPayment(): Pembayaran
    {
        $reference = 'VA-TEST-'.strtoupper(Str::random(6));

        $payment = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => $this->tagihan->total,
            'payment_method' => 'e_wallet',
            'verification_status' => Pembayaran::STATUS_PENDING,
            'gateway_provider' => 'sandbox',
            'gateway_reference' => $reference,
            'gateway_instructions' => 'Bayar virtual account '.$reference.' sejumlah Rp 1.500.000.',
            'gateway_expires_at' => now()->addHours(24),
        ]);

        $this->tagihan->update(['status' => Tagihan::STATUS_PAYMENT_PENDING]);

        return $payment;
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

    // ---------------------------------------------------------------- F3: tenant payment flow (cash only)

    public function test_gateway_payment_creation_route_is_removed(): void
    {
        $this->assertFalse(Route::has('tenant.pembayaran.gateway'));
    }

    public function test_tenant_submits_cash_payment_through_manual_store(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => $this->tagihan->total,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();

        $payment = Pembayaran::where('tagihan_id', $this->tagihan->id)->first();

        $this->assertNotNull($payment);
        $this->assertSame('cash', $payment->payment_method);
        $this->assertFalse($payment->isFromGateway());
        $this->assertNull($payment->gateway_reference);
        $this->assertSame(Pembayaran::STATUS_PENDING, $payment->verification_status);
        $this->assertSame(Tagihan::STATUS_PAYMENT_PENDING, $payment->tagihan->fresh()->status);
    }

    // ---------------------------------------------------------------- F4: webhook auto-verification (legacy gateway)

    public function test_webhook_auto_verifies_legacy_gateway_payment(): void
    {
        $payment = $this->createGatewayPayment();

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
        $payment = $this->createGatewayPayment();

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
        $payment = $this->createGatewayPayment();

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
        $payment = $this->createGatewayPayment();

        $payload = [
            'gateway_reference' => $payment->gateway_reference,
            'status' => 'success',
            'amount' => (float) $payment->amount,
        ];
        $headers = ['X-Gateway-Signature' => $this->sign($payload)];

        $this->postJson(route('webhook.payment-gateway'), $payload, $headers)->assertOk();
        $this->assertSame(1, \DB::table('audit_logs')->count());
        $second = $this->postJson(route('webhook.payment-gateway'), $payload, $headers);

        $second->assertOk();
        $this->assertSame(Pembayaran::STATUS_APPROVED, $payment->fresh()->verification_status);
        $this->assertSame(1, \DB::table('audit_logs')->count(), 'Webhook kedua tidak boleh menambah audit log.');
    }

    public function test_webhook_passes_through_for_non_success_status(): void
    {
        $payment = $this->createGatewayPayment();

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

    public function test_tenant_tagihan_show_does_not_offer_gateway_payment(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertDontSee('Bayar Online');
        $response->assertDontSee('Buat Pembayaran Online');
        $response->assertDontSee('tenant.pembayaran.gateway', false);
        $response->assertSee('Pembayaran Tunai');
    }

    public function test_tenant_pembayaran_show_shows_legacy_gateway_instructions(): void
    {
        $payment = $this->createGatewayPayment();

        $response = $this->actingAs($this->tenant)->get(route('tenant.pembayaran.show', $payment));

        $response->assertOk();
        $response->assertSee('Instruksi Pembayaran Online');
        $response->assertSee($payment->gateway_reference, false);
        $response->assertSee('memverifikasi otomatis', false);
    }

    public function test_owner_pembayaran_show_marks_legacy_gateway_source(): void
    {
        $payment = $this->createGatewayPayment();

        $response = $this->actingAs($this->owner)
            ->get(route('owner.pembayaran.show', $payment));

        $response->assertOk();
        $response->assertSee('Pembayaran Online');
        $response->assertSee($payment->gateway_reference, false);
    }

    public function test_owner_cannot_manually_verify_gateway_payment_already_approved(): void
    {
        $payment = $this->createGatewayPayment();

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
