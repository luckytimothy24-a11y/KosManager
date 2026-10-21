<?php

namespace Tests\Feature;

use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Services\PaymentGateway\PaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function payment_gateway_webhook_valid_signature_is_verified(): void
    {
        $this->withoutExceptionHandling();

        // Buat pembayaran terlebih dahulu
        $penghuni = Penghuni::factory()->create();
        $tagihan = Tagihan::factory()->create(['penghuni_id' => $penghuni->id]);

        $pembayaran = Pembayaran::factory()->create([
            'penghuni_id' => $penghuni->id,
            'tagihan_id' => $tagihan->id,
            'payment_method' => 'cash',
            'amount' => $tagihan->total,
            'gateway_reference' => 'TEST-REF-001',
            'verification_status' => Pembayaran::STATUS_PENDING,
        ]);

        // Simulasi payload dari gateway
        $payload = [
            'gateway_reference' => 'TEST-REF-001',
            'status' => 'success',
            'amount' => $pembayaran->amount,
        ];

        // Dapatkan signature yang valid
        $gateway = PaymentGatewayManager::driver();
        $signature = $gateway->generateSignature($payload);

        $response = $this->postJson('/webhooks/payment-gateway', $payload, [
            'X-Gateway-Signature' => $signature,
        ]);

        $response->assertJson(['message' => 'Pembayaran berhasil diverifikasi.']);
        $response->assertStatus(200);

        // Pastikan pembayaran status berubah menjadi approved
        $this->assertDatabaseHas('pembayarans', [
            'id' => $pembayaran->id,
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'verified_at' => null, // di-set oleh webhook ke 0/null
        ]);
    }

    /** @test */
    public function payment_gateway_webhook_invalid_signature_is_rejected(): void
    {
        $this->withoutExceptionHandling();

        $pembayaran = Pembayaran::factory()->create([
            'verification_status' => Pembayaran::STATUS_PENDING,
        ]);

        $response = $this->postJson('/webhooks/payment-gateway', [], [
            'X-Gateway-Signature' => 'invalid-signature',
        ]);

        $response->assertJson(['message' => 'Signature tidak valid.']);
        $response->assertStatus(403);

        // Pastikan pembayaran tidak berubah status
        $this->assertDatabaseHas('pembayarans', [
            'id' => $pembayaran->id,
            'verification_status' => Pembayaran::STATUS_PENDING,
        ]);
    }

    /** @test */
    public function payment_gateway_webhook_unknown_reference_is_rejected(): void
    {
        $this->withoutExceptionHandling();

        $response = $this->postJson('/webhooks/payment-gateway', [
            'gateway_reference' => 'NONEXISTENT-REF',
            'status' => 'success',
        ], [
            'X-Gateway-Signature' => 'invalid-signature',
        ]);

        $response->assertJson(['message' => 'Pembayaran tidak ditemukan.']);
        $response->assertStatus(404);
    }

    /** @test */
    public function payment_gateway_webhook_unknown_status_is_ignored(): void
    {
        $this->withoutExceptionHandling();

        $pembayaran = Pembayaran::factory()->create([
            'verification_status' => Pembayaran::STATUS_APPROVED,
        ]);

        $payload = [
            'gateway_reference' => $pembayaran->gateway_reference,
            'status' => 'pending', // status bukan success
        ];

        $gateway = PaymentGatewayManager::driver();
        $signature = $gateway->generateSignature($payload);

        $response = $this->postJson('/webhooks/payment-gateway', $payload, [
            'X-Gateway-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pembayarans', [
            'id' => $pembayaran->id,
            'verification_status' => Pembayaran::STATUS_APPROVED,
        ]);
    }
}
