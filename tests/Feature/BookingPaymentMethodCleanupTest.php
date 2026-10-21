<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PaymentService;
use App\Support\PaymentLabels;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Booking payment methods: hanya cash + transfer_bank.
 * Backend menolak e_wallet (web session error + API 422), bukan hanya UI.
 */
class BookingPaymentMethodCleanupTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        Storage::fake('public');

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'booked',
            'monthly_price' => 1500000,
        ]);
    }

    private function makeBooking(string $status = 'pending', float $price = 1500000.0): Booking
    {
        return Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => $status,
            'price' => $price,
            'start_date' => today()->addDays(3),
            'end_date' => today()->addMonths(2),
            'rental_type' => 'monthly',
        ]);
    }

    // ── UI ─────────────────────────────────────────────────────

    public function test_booking_payment_form_only_shows_transfer_bank_and_cash(): void
    {
        $booking = $this->makeBooking();

        $html = $this->actingAs($this->tenant)
            ->get(route('tenant.booking.show', $booking))
            ->assertOk()
            ->assertSee('name="payment_method" value="transfer_bank"', false)
            ->assertSee('name="payment_method" value="cash"', false)
            ->assertDontSee('value="e_wallet"', false)
            ->assertDontSee('E-Wallet / QRIS', false)
            ->assertDontSee('<select id="payment_method"', false)
            ->getContent();

        $this->assertStringContainsString('role="radiogroup"', $html);
        $this->assertStringContainsString('wajib unggah bukti transfer', strtolower($html));
        $this->assertStringContainsString('opsional untuk metode tunai', strtolower($html));
    }

    public function test_historical_e_wallet_label_still_renders(): void
    {
        $this->assertSame('E-Wallet / QRIS', PaymentLabels::paymentMethod('e_wallet'));
        $this->assertSame('Transfer Bank', PaymentLabels::paymentMethod('transfer_bank'));
        $this->assertSame('Tunai', PaymentLabels::paymentMethod('cash'));
    }

    // ── Web validation ────────────────────────────────────────

    public function test_web_e_wallet_is_rejected_with_session_error_and_no_row(): void
    {
        $booking = $this->makeBooking();

        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.payment.store', $booking), [
            'payment_method' => 'e_wallet',
            'proof_file' => UploadedFile::fake()->create('bukti_ewallet.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('payment_method');
        $this->assertDatabaseMissing('pembayarans', ['booking_id' => $booking->id]);
    }

    public function test_web_transfer_bank_without_proof_is_rejected(): void
    {
        $booking = $this->makeBooking();

        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.payment.store', $booking), [
            'payment_method' => 'transfer_bank',
        ]);

        $response->assertSessionHasErrors('proof_file');
        $this->assertDatabaseMissing('pembayarans', ['booking_id' => $booking->id]);
    }

    public function test_web_cash_without_proof_is_accepted(): void
    {
        $booking = $this->makeBooking('pending', 1500000.0);

        $this->actingAs($this->tenant)
            ->post(route('tenant.booking.payment.store', $booking), ['payment_method' => 'cash'])
            ->assertRedirect();

        $payment = Pembayaran::where('booking_id', $booking->id)->firstOrFail();
        $this->assertSame('cash', $payment->payment_method);
        $this->assertSame('pending', $payment->verification_status);
        $this->assertEquals(1500000.0, (float) $payment->amount);
        $this->assertNull($payment->proof_file);
    }

    public function test_web_transfer_bank_with_proof_is_accepted(): void
    {
        $booking = $this->makeBooking();

        $this->actingAs($this->tenant)->post(route('tenant.booking.payment.store', $booking), [
            'payment_method' => 'transfer_bank',
            'proof_file' => UploadedFile::fake()->create('bukti_transfer.pdf', 50, 'application/pdf'),
        ])->assertRedirect();

        $payment = Pembayaran::where('booking_id', $booking->id)->firstOrFail();
        $this->assertSame('transfer_bank', $payment->payment_method);
        $this->assertSame('pending', $payment->verification_status);
        $this->assertNotNull($payment->proof_file);
    }

    // ── API validation (HTTP 422) ─────────────────────────────

    public function test_api_e_wallet_returns_422_with_payment_method_error(): void
    {
        $booking = $this->makeBooking();

        $response = $this->actingAs($this->tenant)->postJson(
            '/api/v1/bookings/'.$booking->id.'/payment',
            ['payment_method' => 'e_wallet']
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('payment_method');
        $this->assertDatabaseMissing('pembayarans', ['booking_id' => $booking->id]);
    }

    public function test_api_transfer_bank_without_proof_returns_422(): void
    {
        $booking = $this->makeBooking();

        $response = $this->actingAs($this->tenant)->postJson(
            '/api/v1/bookings/'.$booking->id.'/payment',
            ['payment_method' => 'transfer_bank']
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('proof_file');
        $this->assertDatabaseMissing('pembayarans', ['booking_id' => $booking->id]);
    }

    public function test_api_cash_without_proof_is_accepted(): void
    {
        $booking = $this->makeBooking('pending', 1500000.0);

        $response = $this->actingAs($this->tenant)->postJson(
            '/api/v1/bookings/'.$booking->id.'/payment',
            ['payment_method' => 'cash']
        );

        $response->assertStatus(201);
        $this->assertDatabaseHas('pembayarans', [
            'booking_id' => $booking->id,
            'payment_method' => 'cash',
            'verification_status' => 'pending',
        ]);
        $payment = Pembayaran::where('booking_id', $booking->id)->firstOrFail();
        $this->assertEquals(1500000.0, (float) $payment->amount);
    }

    public function test_api_transfer_bank_with_proof_is_accepted(): void
    {
        $booking = $this->makeBooking();

        $response = $this->actingAs($this->tenant)->postJson(
            '/api/v1/bookings/'.$booking->id.'/payment',
            [
                'payment_method' => 'transfer_bank',
                'proof_file' => UploadedFile::fake()->create('bukti_api.pdf', 20, 'application/pdf'),
            ]
        );

        $response->assertStatus(201);
        $this->assertDatabaseHas('pembayarans', [
            'booking_id' => $booking->id,
            'payment_method' => 'transfer_bank',
        ]);
    }

    // ── Service defense-in-depth ──────────────────────────────

    public function test_payment_service_rejects_e_wallet_directly(): void
    {
        $booking = $this->makeBooking();
        $service = app(PaymentService::class);

        $result = $service->createForBooking($booking, $this->tenant, 'e_wallet', null, null);

        $this->assertFalse($result['ok'] ?? false);
        $this->assertSame('invalid_method', $result['error'] ?? null);
        $this->assertDatabaseMissing('pembayarans', ['booking_id' => $booking->id]);
    }

    public function test_payment_service_rejects_transfer_without_proof(): void
    {
        $booking = $this->makeBooking();
        $service = app(PaymentService::class);

        $result = $service->createForBooking($booking, $this->tenant, 'transfer_bank', null, null);

        $this->assertFalse($result['ok'] ?? false);
        $this->assertSame('proof_required', $result['error'] ?? null);
    }

    public function test_payment_service_accepts_cash_without_proof(): void
    {
        $booking = $this->makeBooking();
        $service = app(PaymentService::class);

        $result = $service->createForBooking($booking, $this->tenant, 'cash', null, null);

        $this->assertTrue($result['ok'] ?? false);
        $this->assertSame('cash', $result['pembayaran']->payment_method);
    }

    // ── Factory / seeder hygiene ──────────────────────────────

    public function test_pembayaran_factory_does_not_generate_e_wallet(): void
    {
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => Penghuni::factory()->create()->id,
            'kontrak_id' => Kontrak::factory()->create()->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
            'total' => 500000,
        ]);

        $methods = collect(range(1, 20))->map(fn () => Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $tagihan->penghuni_id,
        ])->payment_method)->unique()->values();

        $this->assertTrue($methods->every(fn ($m) => in_array($m, ['cash', 'transfer_bank'], true)));
        $this->assertFalse($methods->contains('e_wallet'));
    }
}
