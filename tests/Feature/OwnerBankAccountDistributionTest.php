<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\OwnerBankAccount;
use App\Models\PaymentDistribution;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PaymentDistributionService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Owner bank account + distribusi pembayaran booking.
 *
 * Payment verification ≠ booking confirmation ≠ owner payout.
 */
class OwnerBankAccountDistributionTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'booked',
            'monthly_price' => 1500000,
        ]);
    }

    private function makeBooking(float $price = 1500000.0): Booking
    {
        return Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending',
            'price' => $price,
            'start_date' => today()->addDays(3),
            'end_date' => today()->addMonths(2),
            'rental_type' => 'monthly',
        ]);
    }

    private function saveBankAccount(User $owner, array $attrs = []): OwnerBankAccount
    {
        $this->actingAs($owner)->put(route('owner.bank-account.update'), array_merge([
            'bank_name' => 'BCA',
            'account_number' => '001234567890',
            'account_holder_name' => 'Lucky Kos',
        ], $attrs))->assertRedirect();

        return OwnerBankAccount::where('owner_id', $owner->id)->firstOrFail();
    }

    private function payAndApprove(Booking $booking): Pembayaran
    {
        $pay = app(PaymentService::class)->createForBooking($booking, $this->tenant, 'qris');
        $this->assertTrue($pay['ok'], $pay['message'] ?? 'pay failed');

        $this->actingAs($this->owner)->post(route('owner.pembayaran.verify', $pay['pembayaran']));

        return $pay['pembayaran']->fresh();
    }

    // ── Owner Bank Account ────────────────────────────────────

    public function test_owner_can_save_bank_account(): void
    {
        $this->saveBankAccount($this->owner);

        $account = OwnerBankAccount::where('owner_id', $this->owner->id)->firstOrFail();
        $this->assertSame('BCA', $account->bank_name);
        $this->assertSame('001234567890', $account->account_number); // leading zero aman
        $this->assertSame('Lucky Kos', $account->account_holder_name);
    }

    public function test_owner_can_update_bank_account(): void
    {
        $this->saveBankAccount($this->owner);

        $this->actingAs($this->owner)->put(route('owner.bank-account.update'), [
            'bank_name' => 'Mandiri',
            'account_number' => '9876543210',
            'account_holder_name' => 'Kos Baru',
        ])->assertRedirect();

        $account = OwnerBankAccount::where('owner_id', $this->owner->id)->firstOrFail();
        $this->assertSame('Mandiri', $account->bank_name);
        $this->assertSame(1, OwnerBankAccount::count());
    }

    public function test_tenant_cannot_update_owner_bank_account(): void
    {
        $this->saveBankAccount($this->owner);

        $this->actingAs($this->tenant)
            ->put(route('owner.bank-account.update'), [
                'bank_name' => 'HACK',
                'account_number' => '999',
                'account_holder_name' => 'Hacker',
            ])->assertForbidden();

        $account = OwnerBankAccount::where('owner_id', $this->owner->id)->firstOrFail();
        $this->assertSame('BCA', $account->bank_name);
        $this->assertSame('001234567890', $account->account_number);
    }

    public function test_bank_account_requires_required_fields(): void
    {
        $this->actingAs($this->owner)->put(route('owner.bank-account.update'), [
            'bank_name' => '',
            'account_number' => '',
            'account_holder_name' => '',
        ])->assertSessionHasErrors(['bank_name', 'account_number', 'account_holder_name']);
    }

    // ── Distribution after payment approved ───────────────────

    public function test_payment_approved_creates_distribution_for_owner(): void
    {
        $this->saveBankAccount($this->owner);
        $booking = $this->makeBooking(1500000.0);

        $payment = $this->payAndApprove($booking);

        $this->assertSame('approved', $payment->verification_status);
        // Booking TETAP pending
        $this->assertEquals('pending', $booking->fresh()->status);

        $distribution = PaymentDistribution::where('pembayaran_id', $payment->id)->firstOrFail();
        $this->assertSame($this->owner->id, $distribution->owner_id);
        $this->assertEquals(1500000.0, (float) $distribution->gross_amount);
        $this->assertEquals(0.0, (float) $distribution->platform_fee); // tanpa fee rule existing
        $this->assertEquals(1500000.0, (float) $distribution->owner_amount);
        $this->assertSame('pending', $distribution->status);
        $this->assertNull($distribution->processed_at);
    }

    public function test_distribution_owner_from_booking_not_request(): void
    {
        $ownerB = User::factory()->create(['role' => 'owner']);
        $kosB = Kos::factory()->create(['owner_id' => $ownerB->id, 'status' => 'active']);
        $kamarB = Kamar::factory()->create(['kos_id' => $kosB->id, 'status' => 'booked', 'monthly_price' => 900000]);

        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kosB->id,
            'kamar_id' => $kamarB->id,
            'status' => 'pending',
            'price' => 900000,
            'start_date' => today()->addDays(3),
            'end_date' => today()->addMonths(2),
        ]);

        $pay = app(PaymentService::class)->createForBooking($booking, $this->tenant, 'qris');
        // Verify oleh owner B (pemilik kos) — owner A tidak punya scope
        $this->actingAs($ownerB)->post(route('owner.pembayaran.verify', $pay['pembayaran']));

        // Distribution owner = owner B (relasi booking → kos → owner), bukan request
        $distribution = PaymentDistribution::where('pembayaran_id', $pay['pembayaran']->id)->firstOrFail();
        $this->assertSame($ownerB->id, $distribution->owner_id);
        $this->assertEquals(900000.0, (float) $distribution->owner_amount);
    }

    public function test_double_distribution_is_blocked(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->payAndApprove($booking);

        // Panggil service lagi (idempotent)
        $result = app(PaymentDistributionService::class)->recordFromApprovedPayment($payment);

        $this->assertTrue($result['ok']);
        $this->assertSame(1, PaymentDistribution::where('pembayaran_id', $payment->id)->count());
    }

    // ── Payment ≠ Payout ──────────────────────────────────────

    public function test_payment_approved_does_not_auto_process_payout(): void
    {
        $this->saveBankAccount($this->owner);
        $booking = $this->makeBooking();
        $payment = $this->payAndApprove($booking);

        $distribution = PaymentDistribution::where('pembayaran_id', $payment->id)->firstOrFail();

        // Payment approved ≠ distribution processed
        $this->assertSame('pending', $distribution->status);
        $this->assertNull($distribution->processed_at);
        // Booking tetap pending
        $this->assertEquals('pending', $booking->fresh()->status);
    }

    public function test_owner_cannot_mark_processed_before_booking_confirmed(): void
    {
        $this->saveBankAccount($this->owner);
        $booking = $this->makeBooking();
        $payment = $this->payAndApprove($booking);
        $distribution = PaymentDistribution::where('pembayaran_id', $payment->id)->firstOrFail();

        $this->actingAs($this->owner)
            ->post(route('owner.distribution.mark-processed', $distribution))
            ->assertSessionHas('error');

        $this->assertSame('pending', $distribution->fresh()->status);
    }

    public function test_booking_confirm_marks_distribution_ready(): void
    {
        $this->saveBankAccount($this->owner);
        $booking = $this->makeBooking();
        $payment = $this->payAndApprove($booking);
        $distribution = PaymentDistribution::where('pembayaran_id', $payment->id)->firstOrFail();

        $this->actingAs($this->owner)->post(route('owner.booking.approve', $booking));

        $this->assertEquals('approved', $booking->fresh()->status);
        $this->assertSame('ready', $distribution->fresh()->status);
        // Belum processed
        $this->assertNull($distribution->fresh()->processed_at);
    }

    public function test_owner_can_mark_processed_after_ready(): void
    {
        $this->saveBankAccount($this->owner);
        $booking = $this->makeBooking();
        $payment = $this->payAndApprove($booking);
        $this->actingAs($this->owner)->post(route('owner.booking.approve', $booking));

        $distribution = PaymentDistribution::where('pembayaran_id', $payment->id)->firstOrFail();

        $this->actingAs($this->owner)
            ->post(route('owner.distribution.mark-processed', $distribution))
            ->assertSessionHas('success');

        $fresh = $distribution->fresh();
        $this->assertSame('processed', $fresh->status);
        $this->assertNotNull($fresh->processed_at);
    }

    // ── Authorization / IDOR ──────────────────────────────────

    public function test_owner_b_cannot_mark_owner_a_distribution_processed(): void
    {
        $this->saveBankAccount($this->owner);
        $booking = $this->makeBooking();
        $payment = $this->payAndApprove($booking);
        $this->actingAs($this->owner)->post(route('owner.booking.approve', $booking));

        $distribution = PaymentDistribution::where('pembayaran_id', $payment->id)->firstOrFail();

        $ownerB = User::factory()->create(['role' => 'owner']);
        $this->actingAs($ownerB)
            ->post(route('owner.distribution.mark-processed', $distribution))
            ->assertForbidden();

        $this->assertSame('ready', $distribution->fresh()->status);
    }

    public function test_tenant_cannot_view_or_process_distribution(): void
    {
        $this->saveBankAccount($this->owner);
        $booking = $this->makeBooking();
        $payment = $this->payAndApprove($booking);
        $distribution = PaymentDistribution::where('pembayaran_id', $payment->id)->firstOrFail();

        $this->actingAs($this->tenant)
            ->post(route('owner.distribution.mark-processed', $distribution))
            ->assertForbidden();

        $this->actingAs($this->tenant)
            ->get(route('owner.distribution.index'))
            ->assertForbidden();
    }

    public function test_amount_comes_from_server_not_manipulable(): void
    {
        $booking = $this->makeBooking(1200000.0);

        $pay = app(PaymentService::class)->createForBooking(
            $booking,
            $this->tenant,
            'qris',
            null,
            null
        );
        // Tidak ada amount di request — server pakai booking.price
        $this->assertTrue($pay['ok']);
        $this->assertEquals(1200000.0, (float) $pay['pembayaran']->amount);

        $this->actingAs($this->owner)->post(route('owner.pembayaran.verify', $pay['pembayaran']));
        $distribution = PaymentDistribution::where('pembayaran_id', $pay['pembayaran']->id)->firstOrFail();
        $this->assertEquals(1200000.0, (float) $distribution->owner_amount);
        $this->assertEquals(0.0, (float) $distribution->platform_fee);
    }

    // ── Legacy payment tagihan tetap jalan ────────────────────

    public function test_legacy_tagihan_payment_does_not_create_booking_distribution(): void
    {
        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
        ]);

        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $tagihan->id,
            'amount' => $tagihan->total,
            'payment_method' => 'qris',
            'proof_file' => $proof,
        ])->assertRedirect();

        $pembayaran = Pembayaran::where('tagihan_id', $tagihan->id)->firstOrFail();
        $this->actingAs($this->owner)->post(route('owner.pembayaran.verify', $pembayaran));

        $this->assertSame('approved', $pembayaran->fresh()->verification_status);
        // Tagihan payment tidak membuat booking distribution
        $this->assertSame(0, PaymentDistribution::count());
    }

    // ── Cancellation compatibility ────────────────────────────

    public function test_cancellation_request_does_not_touch_distribution_status(): void
    {
        $this->saveBankAccount($this->owner);
        $booking = $this->makeBooking();
        $payment = $this->payAndApprove($booking);
        $distribution = PaymentDistribution::where('pembayaran_id', $payment->id)->firstOrFail();

        $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking), ['reason' => 'x']);

        $this->assertSame('pending', $distribution->fresh()->status);
        $this->assertEquals('pending', $booking->fresh()->status);
        $this->assertSame('approved', $payment->fresh()->verification_status);
    }

    public function test_api_owner_distributions_list(): void
    {
        $this->saveBankAccount($this->owner);
        $booking = $this->makeBooking();
        $payment = $this->payAndApprove($booking);

        $token = $this->owner->createToken('test')->plainTextToken;
        $response = $this->withToken($token)
            ->getJson('/api/v1/owner/distributions')
            ->assertOk()
            ->assertJsonPath('data.0.status', 'pending');

        $data = $response->json('data.0');
        $this->assertEquals(1500000.0, (float) $data['owner_amount']);
        $this->assertEquals(0.0, (float) $data['platform_fee']);
        $this->assertSame($this->owner->id, PaymentDistribution::where('pembayaran_id', PaymentDistribution::where('status', 'pending')->value('pembayaran_id'))->value('owner_id') ?? $this->owner->id);
        $this->assertSame(1, PaymentDistribution::where('owner_id', $this->owner->id)->count());
    }

    public function test_api_tenant_cannot_list_distributions(): void
    {
        $token = $this->tenant->createToken('test')->plainTextToken;
        $this->withToken($token)
            ->getJson('/api/v1/owner/distributions')
            ->assertForbidden();
    }
}
