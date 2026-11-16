<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\EscrowService;
use App\Services\PaymentGateway\PaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group escrow
 *
 * Fitur escrow/wallet/payout TIDAK DIAKTIFKAN tanpa bukti custody platform.
 * Test ini dikelompokkan ke group 'escrow' agar bisa di-exclude saat quality gate:
 *   php artisan test --exclude-group=escrow
 */
class KosHubPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $pencari;
    private Kos $kos;
    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->pencari = User::factory()->create(['role' => 'pencari']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available', 'monthly_price' => 1500000]);
    }

    /** @test */
    public function payment_flow_booking_to_escrow(): void
    {
        // 1. Create booking (MENUNGGU_BAYAR / pending)
        $booking = Booking::factory()->create([
            'user_id' => $this->pencari->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => Booking::STATUS_PENDING,
            'price' => 1500000,
        ]);

        $this->assertEquals(Booking::STATUS_PENDING, $booking->status);

        // 2. Create payment (pending)
        $pembayaran = Pembayaran::factory()->create([
            'booking_id' => $booking->id,
            'penghuni_id' => null,
            'tagihan_id' => null,
            'payment_method' => 'qris',
            'amount' => 1500000, // sesuai nominal server (harga booking)
            'gateway_reference' => 'TEST-BOOKING-001',
            'gateway_provider' => 'sandbox',
            'verification_status' => Pembayaran::STATUS_PENDING,
        ]);

        // 3. Webhook: verify signature + process
        $gateway = PaymentGatewayManager::driver();
        $payload = [
            'gateway_reference' => 'TEST-BOOKING-001',
            'status' => 'success',
            'amount' => $pembayaran->amount,
        ];
        $signature = $gateway->generateSignature($payload);

        $response = $this->postJson('/webhooks/payment-gateway', $payload, [
            'X-Gateway-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Pembayaran berhasil diverifikasi.']);

        // 4. Verify payment is approved
        $pembayaran->refresh();
        $this->assertEquals(Pembayaran::STATUS_APPROVED, $pembayaran->verification_status);

        // 5. Verify escrow entry created (pending)
        $escrow = WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'pending')
            ->first();

        $this->assertNotNull($escrow);
        $this->assertEquals(1500000, $escrow->amount);
    }

    /** @test */
    public function webhook_idempotency(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->pencari->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => Booking::STATUS_PENDING,
            'price' => 1500000,
        ]);

        $pembayaran = Pembayaran::factory()->create([
            'booking_id' => $booking->id,
            'payment_method' => 'qris',
            'amount' => 1500000,
            'gateway_reference' => 'IDEMPOTENT-001',
            'gateway_provider' => 'sandbox',
            'verification_status' => Pembayaran::STATUS_PENDING,
        ]);

        $gateway = PaymentGatewayManager::driver();
        $payload = [
            'gateway_reference' => 'IDEMPOTENT-001',
            'status' => 'success',
            'amount' => $pembayaran->amount,
        ];
        $signature = $gateway->generateSignature($payload);

        // First webhook
        $this->postJson('/webhooks/payment-gateway', $payload, [
            'X-Gateway-Signature' => $signature,
        ])->assertStatus(200);

        // Second webhook (idempotent)
        $response = $this->postJson('/webhooks/payment-gateway', $payload, [
            'X-Gateway-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Pembayaran sudah diproses sebelumnya.']);
    }

    /** @test */
    public function webhook_invalid_signature_rejected(): void
    {
        $response = $this->postJson('/webhooks/payment-gateway', [
            'gateway_reference' => 'TEST-001',
            'status' => 'success',
        ], [
            'X-Gateway-Signature' => 'invalid-signature',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Signature tidak valid.']);
    }

    /** @test */
    public function escrow_settlement_calculates_commission(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->pencari->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => Booking::STATUS_APPROVED,
        ]);

        // Create escrow entry
        $escrow = WalletTransaction::create([
            'owner_id' => $this->owner->id,
            'booking_id' => $booking->id,
            'type' => 'escrow_in',
            'amount' => 1510000,
            'description' => 'Escrow test',
            'status' => 'pending',
        ]);

        // Settle
        $escrowService = app(EscrowService::class);
        $result = $escrowService->settleEscrow($booking);

        $this->assertTrue($result['ok']);
        $this->assertEquals(1510000, $result['gross']);
        $this->assertEquals(75500, $result['commission']); // 5% of 1,510,000
        $this->assertEquals(1434500, $result['owner_amount']);

        // Verify completed escrow entry (gross owner entitlement; komisi dicatat terpisah)
        $completed = WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'completed')
            ->first();

        $this->assertNotNull($completed);
        $this->assertEquals(1510000, $completed->amount);

        // Verify commission entry
        $commission = WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'commission')
            ->first();

        $this->assertNotNull($commission);
        $this->assertEquals(-75500, $commission->amount);

        // Saldo owner = gross - komisi, bukan net - komisi.
        $this->assertEquals(1434500, $escrowService->getBalance($this->owner->id));
    }

    /** @test */
    public function owner_balance_calculation(): void
    {
        WalletTransaction::create([
            'owner_id' => $this->owner->id,
            'type' => 'escrow_in',
            'amount' => 1000000,
            'status' => 'completed',
        ]);

        WalletTransaction::create([
            'owner_id' => $this->owner->id,
            'type' => 'commission',
            'amount' => -50000,
            'status' => 'completed',
        ]);

        WalletTransaction::create([
            'owner_id' => $this->owner->id,
            'type' => 'escrow_in',
            'amount' => 500000,
            'status' => 'pending', // not counted
        ]);

        $escrowService = app(EscrowService::class);
        $balance = $escrowService->getBalance($this->owner->id);

        $this->assertEquals(950000, $balance);
    }

    /** @test */
    public function wallet_transaction_is_append_only(): void
    {
        $tx = WalletTransaction::create([
            'owner_id' => $this->owner->id,
            'type' => 'escrow_in',
            'amount' => 100000,
            'status' => 'completed',
        ]);

        $this->expectException(\RuntimeException::class);
        $tx->update(['amount' => 200000]);
    }

    /** @test */
    public function wallet_transaction_cannot_be_deleted(): void
    {
        $tx = WalletTransaction::create([
            'owner_id' => $this->owner->id,
            'type' => 'escrow_in',
            'amount' => 100000,
            'status' => 'completed',
        ]);

        $this->expectException(\RuntimeException::class);
        $tx->delete();
    }

    /** @test */
    public function role_access_pencari_cannot_access_owner_routes(): void
    {
        $response = $this->actingAs($this->pencari)->get('/owner/kos');
        $this->assertTrue(in_array($response->getStatusCode(), [403, 302]));
    }

    /** @test */
    public function role_access_owner_cannot_access_admin_routes(): void
    {
        $response = $this->actingAs($this->owner)->get('/admin/kos');
        $this->assertTrue(in_array($response->getStatusCode(), [403, 302]));
    }

    /** @test */
    public function role_access_pencari_cannot_access_superadmin_routes(): void
    {
        $response = $this->actingAs($this->pencari)->get('/super-admin/users');
        $this->assertTrue(in_array($response->getStatusCode(), [403, 302]));
    }
}
