<?php

namespace Tests\Feature\Characterization;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\PaymentDistribution;
use App\Models\Payout;
use App\Models\PayoutDisbursement;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PaymentDistributionService;
use App\Services\PaymentService;
use App\Services\PropertyOwnerPayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * FASE 0 — KARAKTERISASI ALUR DANA 2 TAHAP.
 *
 * OVERLAP: feature/alur-dana-2-tahap. Test di kelas ini sengaja ditandai
 * group `alur-dana-2-tahap` agar mudah dijalankan/di-review terpisah saat
 * branch tersebut di-merge.
 *
 * Dua lapis dana yang ada di baseline:
 * 1. PaymentDistribution: pembayaran booking -> hak role Owner.
 * 2. Payout + PayoutDisbursement: kewajiban ke Pemilik Kos Asli,
 *    boleh dicicil (partial disbursement) lalu diverifikasi.
 */
#[Group('alur-dana-2-tahap')]
class FundsFlowCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_distribution_is_not_processed_before_booking_approval(): void
    {
        Storage::fake('private');

        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'booked', 'monthly_price' => 1500000]);
        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'pending',
            'price' => 1500000,
            'down_payment_amount' => null,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'rental_type' => 'monthly',
        ]);

        $pay = app(PaymentService::class)->createForBooking($booking, $tenant, 'qris');
        $this->assertTrue($pay['ok'], $pay['message'] ?? 'payment failed');
        $pembayaran = $pay['pembayaran'];

        $this->actingAs($owner)
            ->post(route('owner.pembayaran.verify', $pembayaran))
            ->assertRedirect();

        $distribution = PaymentDistribution::where('pembayaran_id', $pembayaran->id)->firstOrFail();
        $this->assertSame('pending', $distribution->status);
        $this->assertNull($distribution->processed_at);
        $this->assertSame('pending', $booking->fresh()->status);

        // Payment verified != booking approved != distribution processed.
        $result = app(PaymentDistributionService::class)->markProcessed($distribution, $owner);
        $this->assertFalse($result['ok']);
        $this->assertSame('not_ready', $result['error']);

        $this->actingAs($owner)
            ->post(route('owner.booking.approve', $booking))
            ->assertRedirect();

        $this->assertSame('approved', $booking->fresh()->status);
        $this->assertSame('ready', $distribution->fresh()->status);

        $result = app(PaymentDistributionService::class)->markProcessed($distribution->fresh(), $owner);
        $this->assertTrue($result['ok']);
        $this->assertSame('processed', $distribution->fresh()->status);
        $this->assertNotNull($distribution->fresh()->processed_at);
    }

    public function test_payout_can_be_paid_in_partial_disbursements_and_verified_when_complete(): void
    {
        Storage::fake('private');

        $owner = User::factory()->create(['role' => 'owner']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenant = User::factory()->create(['role' => 'pencari']);
        $propertyOwner = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $propertyOwner->id]);

        $kos = Kos::factory()->create([
            'owner_id' => $owner->id,
            'status' => 'active',
            'property_owner_id' => $propertyOwner->id,
        ]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $kamar->id,
            'subtotal' => 3000000,
            'penalty' => 0,
            'total' => 3000000,
        ]);

        ManagementAgreement::factory()->create([
            'kos_id' => $kos->id,
            'property_owner_id' => $propertyOwner->id,
            'commission_type' => 'percent',
            'commission_value' => 10,
            'effective_from' => '2026-01-01',
            'effective_to' => null,
        ]);

        Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => 3000000,
            'rent_amount' => 3000000,
            'penalty_amount' => 0,
            'payment_date' => '2026-09-10',
            'verification_status' => 'approved',
        ]);

        $service = app(PropertyOwnerPayoutService::class);
        $created = $service->createForKos($owner, [
            'kos_id' => $kos->id,
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
        ]);
        $this->assertTrue($created['ok'], json_encode($created));

        /** @var Payout $payout */
        $payout = $created['payout'];
        $this->assertEquals(2700000, (float) $payout->amount);

        $first = $service->recordDisbursement($owner, $payout, [
            'amount' => 1700000,
            'idempotency_key' => 'fase0-partial-1',
            'proof_file' => UploadedFile::fake()->create('bukti-1.pdf', 100, 'application/pdf'),
            'transferred_at' => '2026-09-20',
        ]);
        $second = $service->recordDisbursement($owner, $payout, [
            'amount' => 1000000,
            'idempotency_key' => 'fase0-partial-2',
            'proof_file' => UploadedFile::fake()->create('bukti-2.pdf', 100, 'application/pdf'),
            'transferred_at' => '2026-09-21',
        ]);

        $this->assertTrue($first['ok'], json_encode($first));
        $this->assertTrue($second['ok'], json_encode($second));
        $this->assertSame('pending', $payout->fresh()->status);

        $this->assertTrue($service->verifyDisbursement($superAdmin, $first['disbursement'])['ok']);
        $this->assertNotSame('verified', $payout->fresh()->status);

        $this->assertTrue($service->verifyDisbursement($superAdmin, $second['disbursement']->fresh())['ok']);
        $this->assertSame('verified', $payout->fresh()->status);
        $this->assertSame(2, PayoutDisbursement::where('payout_id', $payout->id)->count());
    }
}
