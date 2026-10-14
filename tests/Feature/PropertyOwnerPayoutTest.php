<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\OwnerBankAccount;
use App\Models\Payout;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Payout/Settlement ke Pemilik Kos Asli.
 *
 * Owner = pengelola/perantara (bukan pemilik legal properti).
 * Transfer manual di luar aplikasi; KosManager hanya mencatat.
 * platform_fee MVP = 0, tidak editable Owner. Nominal server-side.
 */
class PropertyOwnerPayoutTest extends TestCase
{
    use RefreshDatabase;

    private const PERIOD_START = '2026-09-01';

    private const PERIOD_END = '2026-09-30';

    private User $owner;
    private User $ownerB;
    private User $superAdmin;
    private User $tenant;
    private Kos $kos;
    private Kos $kosB;
    private PropertyOwner $propertyOwner;
    private Penghuni $penghuni;
    private Kontrak $kontrak;
    private Tagihan $tagihan;
    private Kamar $kamar;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->ownerB = User::factory()->create(['role' => 'owner']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);

        $this->kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
            'name' => 'Kos Gejayan Owner A',
        ]);
        $this->kosB = Kos::factory()->create([
            'owner_id' => $this->ownerB->id,
            'status' => 'active',
            'name' => 'Kos Milik Owner B',
        ]);

        $this->propertyOwner = PropertyOwner::factory()->create(['name' => 'Budi Santoso']);
        PropertyOwnerBankAccount::factory()->create([
            'property_owner_id' => $this->propertyOwner->id,
            'bank_name' => 'BCA',
            'account_number' => '001234567890',
            'account_holder_name' => 'Budi Santoso',
        ]);
        $this->kos->update(['property_owner_id' => $this->propertyOwner->id]);
        ManagementAgreement::factory()->create([
            'kos_id' => $this->kos->id,
            'property_owner_id' => $this->propertyOwner->id,
            'commission_type' => 'percent',
            'commission_value' => 10,
            'effective_from' => '2026-01-01',
            'effective_to' => null,
        ]);

        // Rantai income: penghuni → kontrak → tagihan + booking
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'monthly_price' => 1500000,
        ]);
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
            'total' => 1500000,
            'subtotal' => 1500000,
            'discount' => 0,
            'penalty' => 0,
        ]);
        $this->booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending',
            'price' => 2000000,
            'start_date' => today()->addDays(3),
            'end_date' => today()->addMonths(2),
            'rental_type' => 'monthly',
        ]);
    }

    private function seedApprovedIncome(): void
    {
        // Tagihan payment approved — 1.500.000 (dalam periode)
        Pembayaran::factory()->create([
            'payment_number' => 'PY-TEST-TAGIHAN-001',
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'payment_date' => '2026-09-15',
            'verification_status' => 'approved',
        ]);

        // Booking payment approved — 2.000.000 (penghuni_id NULL, dalam periode)
        Pembayaran::factory()->create([
            'payment_number' => 'PY-TEST-BOOKING-001',
            'tagihan_id' => null,
            'booking_id' => $this->booking->id,
            'penghuni_id' => null,
            'amount' => 2000000,
            'payment_date' => '2026-09-20',
            'verification_status' => 'approved',
        ]);

        // Pending di periode — TIDAK boleh dihitung
        Pembayaran::factory()->create([
            'payment_number' => 'PY-TEST-PENDING-001',
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 999999,
            'payment_date' => '2026-09-25',
            'verification_status' => 'pending',
        ]);

        // Approved di LUAR periode — TIDAK boleh dihitung
        Pembayaran::factory()->create([
            'payment_number' => 'PY-TEST-OUTSIDE-001',
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 5000000,
            'payment_date' => '2026-08-10',
            'verification_status' => 'approved',
        ]);
    }

    private function createPayoutViaHttp(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->owner)->post(route('owner.payouts.store'), array_merge([
            'kos_id' => $this->kos->id,
            'period_start' => self::PERIOD_START,
            'period_end' => self::PERIOD_END,
        ], $overrides));
    }

    private function transferViaHttp(Payout $payout, array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->owner)->post(route('owner.payouts.transfer', $payout), array_merge([
            'proof_file' => UploadedFile::fake()->create('bukti-transfer.pdf', 200, 'application/pdf'),
            'transferred_at' => '2026-10-05',
            'notes' => 'Transfer via BCA mobile',
        ], $overrides));
    }

    // ── Authorization ────────────────────────────────────────

    public function test_owner_can_view_own_payouts_index(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();

        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();

        $this->actingAs($this->owner)
            ->get(route('owner.payouts.index'))
            ->assertOk()
            ->assertSee($payout->payout_number)
            ->assertSee('Kos Gejayan Owner A')
            ->assertSee('Budi Santoso');
    }

    public function test_owner_cannot_view_other_owners_payout(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();

        $this->actingAs($this->ownerB)
            ->get(route('owner.payouts.show', $payout))
            ->assertForbidden();
    }

    public function test_owner_can_create_payout_for_own_kos(): void
    {
        $this->seedApprovedIncome();

        $this->createPayoutViaHttp()->assertRedirect();

        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();
        $this->assertSame(Payout::STATUS_PENDING, $payout->status);
        $this->assertSame((int) $this->owner->id, (int) $payout->owner_id);
        $this->assertSame((int) $this->propertyOwner->id, (int) $payout->property_owner_id);
        $this->assertStringStartsWith('PO-', $payout->payout_number);
        $this->assertSame(3500000.0, (float) $payout->gross_amount);
        $this->assertSame(350000.0, (float) $payout->commission_total);
        $this->assertSame(0.0, (float) $payout->platform_fee);
        $this->assertSame(3150000.0, (float) $payout->amount);
    }

    public function test_owner_cannot_create_payout_for_other_owners_kos(): void
    {
        // kosB punya property owner + income agar satu-satunya penolakan = scope
        $poB = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $poB->id]);
        $this->kosB->update(['property_owner_id' => $poB->id]);
        ManagementAgreement::factory()->create([
            'kos_id' => $this->kosB->id,
            'property_owner_id' => $poB->id,
            'commission_type' => 'percent',
            'commission_value' => 10,
            'effective_from' => '2026-01-01',
        ]);

        $kamarB = Kamar::factory()->create(['kos_id' => $this->kosB->id, 'status' => 'available']);
        $penghuniB = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kosB->id,
            'kamar_id' => $kamarB->id,
            'status' => 'active',
        ]);
        $kontrakB = Kontrak::factory()->create([
            'penghuni_id' => $penghuniB->id,
            'kos_id' => $this->kosB->id,
            'kamar_id' => $kamarB->id,
            'status' => 'active',
        ]);
        $tagihanB = Tagihan::factory()->create([
            'penghuni_id' => $penghuniB->id,
            'kontrak_id' => $kontrakB->id,
            'kamar_id' => $kamarB->id,
            'status' => 'unpaid',
            'total' => 1000000,
        ]);
        Pembayaran::factory()->create([
            'payment_number' => 'PY-TEST-KOSB-001',
            'tagihan_id' => $tagihanB->id,
            'penghuni_id' => $penghuniB->id,
            'amount' => 1000000,
            'payment_date' => '2026-09-10',
            'verification_status' => 'approved',
        ]);

        $this->actingAs($this->owner)->post(route('owner.payouts.store'), [
            'kos_id' => $this->kosB->id,
            'period_start' => self::PERIOD_START,
            'period_end' => self::PERIOD_END,
        ])->assertRedirect();

        // Tidak ada payout sama sekali untuk kos owner lain
        $this->assertDatabaseMissing('payouts', ['kos_id' => $this->kosB->id]);
        $this->assertSame(0, Payout::count());
    }

    public function test_tenant_cannot_access_payouts(): void
    {
        $this->actingAs($this->tenant)
            ->get(route('owner.payouts.index'))
            ->assertForbidden();

        $this->actingAs($this->tenant)
            ->post(route('owner.payouts.store'), [
                'kos_id' => $this->kos->id,
                'period_start' => self::PERIOD_START,
                'period_end' => self::PERIOD_END,
            ])
            ->assertForbidden();
    }

    public function test_owner_cannot_verify_own_payout(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();
        $this->transferViaHttp($payout)->assertRedirect();

        $this->actingAs($this->owner)
            ->post(route('owner.payouts.verify', $payout))
            ->assertForbidden();

        $this->assertSame(Payout::STATUS_TRANSFERRED, $payout->fresh()->status);
    }

    public function test_super_admin_can_verify_payout(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();
        $this->transferViaHttp($payout)->assertRedirect();

        $this->actingAs($this->superAdmin)
            ->post(route('owner.payouts.verify', $payout))
            ->assertRedirect();

        $payout->refresh();
        $this->assertSame(Payout::STATUS_VERIFIED, $payout->status);
        $this->assertSame((int) $this->superAdmin->id, (int) $payout->verified_by);
        $this->assertNotNull($payout->verified_at);
    }

    public function test_owner_cannot_reject_payout(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();
        $this->transferViaHttp($payout)->assertRedirect();

        $this->actingAs($this->owner)
            ->post(route('owner.payouts.reject', $payout), ['reason' => 'Salah hitung'])
            ->assertForbidden();

        $this->assertSame(Payout::STATUS_TRANSFERRED, $payout->fresh()->status);
    }

    // ── Validation ───────────────────────────────────────────

    public function test_period_end_must_be_after_or_equal_period_start(): void
    {
        $this->actingAs($this->owner)->post(route('owner.payouts.store'), [
            'kos_id' => $this->kos->id,
            'period_start' => '2026-09-30',
            'period_end' => '2026-09-01',
        ])->assertSessionHasErrors('period_end');
    }

    public function test_create_payout_fails_when_kos_has_no_property_owner(): void
    {
        $kosNoPo = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
            'name' => 'Kos Tanpa Pemilik',
        ]);

        $this->actingAs($this->owner)->post(route('owner.payouts.store'), [
            'kos_id' => $kosNoPo->id,
            'period_start' => self::PERIOD_START,
            'period_end' => self::PERIOD_END,
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('payouts', ['kos_id' => $kosNoPo->id]);
    }

    public function test_create_payout_fails_when_no_eligible_income(): void
    {
        $po = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $po->id]);
        $kosEmpty = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
            'property_owner_id' => $po->id,
            'name' => 'Kos Tanpa Income',
        ]);

        $this->actingAs($this->owner)->post(route('owner.payouts.store'), [
            'kos_id' => $kosEmpty->id,
            'period_start' => self::PERIOD_START,
            'period_end' => self::PERIOD_END,
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('payouts', ['kos_id' => $kosEmpty->id]);
    }

    public function test_create_payout_fails_when_property_owner_has_no_bank_account(): void
    {
        $poNoBank = PropertyOwner::factory()->create();
        $kosNoBank = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
            'property_owner_id' => $poNoBank->id,
            'name' => 'Kos Rekening Kosong',
        ]);

        $this->actingAs($this->owner)->post(route('owner.payouts.store'), [
            'kos_id' => $kosNoBank->id,
            'period_start' => self::PERIOD_START,
            'period_end' => self::PERIOD_END,
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('payouts', ['kos_id' => $kosNoBank->id]);
    }

    public function test_amount_and_fee_cannot_be_manipulated_via_request(): void
    {
        $this->seedApprovedIncome();

        $this->createPayoutViaHttp([
            'amount' => 999999999,
            'gross_amount' => 1,
            'platform_fee' => 500000,
            'property_owner_id' => 99999,
            'owner_id' => $this->ownerB->id,
            'status' => 'verified',
            'payout_number' => 'PO-HACKED',
        ])->assertRedirect();

        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();

        $this->assertSame(3500000.0, (float) $payout->gross_amount);
        $this->assertSame(350000.0, (float) $payout->commission_total);
        $this->assertSame(0.0, (float) $payout->platform_fee);
        $this->assertSame(3150000.0, (float) $payout->amount);
        $this->assertSame((int) $this->owner->id, (int) $payout->owner_id);
        $this->assertSame((int) $this->propertyOwner->id, (int) $payout->property_owner_id);
        $this->assertSame(Payout::STATUS_PENDING, $payout->status);
        $this->assertNotSame('PO-HACKED', $payout->payout_number);
    }

    public function test_transfer_requires_proof_file(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();

        $this->transferViaHttp($payout, ['proof_file' => null])->assertSessionHas('error');

        $this->assertSame(Payout::STATUS_PENDING, $payout->fresh()->status);
    }

    public function test_proof_file_validates_mime_and_size(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();

        // MIME tidak valid
        $this->transferViaHttp($payout, [
            'proof_file' => UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream'),
        ])->assertSessionHasErrors('proof_file');

        // Terlalu besar (> 5MB)
        $this->transferViaHttp($payout, [
            'proof_file' => UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf'),
        ])->assertSessionHasErrors('proof_file');

        $this->assertSame(Payout::STATUS_PENDING, $payout->fresh()->status);
    }

    public function test_transfer_requires_transferred_at(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();

        $this->transferViaHttp($payout, ['transferred_at' => null])
            ->assertSessionHasErrors('transferred_at');
    }

    // ── Duplicate ────────────────────────────────────────────

    public function test_duplicate_payout_same_kos_and_period_is_rejected(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();

        // Payout kedua — kos + periode sama → ditolak
        $this->createPayoutViaHttp()->assertSessionHas('error');

        $this->assertSame(1, Payout::where('kos_id', $this->kos->id)->count());
    }

    public function test_concurrent_style_double_create_second_fails(): void
    {
        $this->seedApprovedIncome();

        $this->createPayoutViaHttp()->assertRedirect();
        $this->createPayoutViaHttp()->assertSessionHas('error');
        $this->createPayoutViaHttp()->assertSessionHas('error');

        $this->assertSame(1, Payout::where('kos_id', $this->kos->id)->count());
    }

    // ── Status workflow ──────────────────────────────────────

    public function test_pending_to_transferred_via_owner_upload(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();

        $this->transferViaHttp($payout)->assertRedirect();

        $payout->refresh();
        $this->assertSame(Payout::STATUS_TRANSFERRED, $payout->status);
        $this->assertNotNull($payout->transferred_at);
        $this->assertNotNull($payout->proof_path);
        $this->assertTrue(Storage::disk('private')->exists($payout->proof_path));
    }

    public function test_transferred_to_rejected_via_super_admin_with_reason(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();
        $this->transferViaHttp($payout)->assertRedirect();

        $this->actingAs($this->superAdmin)
            ->post(route('owner.payouts.reject', $payout), ['reason' => 'Bukti transfer tidak jelas'])
            ->assertRedirect();

        $payout->refresh();
        $this->assertSame(Payout::STATUS_REJECTED, $payout->status);
        $this->assertSame('Bukti transfer tidak jelas', $payout->notes);
        $this->assertSame((int) $this->superAdmin->id, (int) $payout->verified_by);
    }

    public function test_rejected_resubmit_updates_same_record_not_new_payout(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();
        $this->transferViaHttp($payout)->assertRedirect();

        $this->actingAs($this->superAdmin)
            ->post(route('owner.payouts.reject', $payout), ['reason' => 'Salah nominal'])
            ->assertRedirect();

        $originalId = $payout->id;
        $originalNumber = $payout->payout_number;

        // Owner resubmit — record yang SAMA
        $this->transferViaHttp($payout->fresh(), [
            'proof_file' => UploadedFile::fake()->create('bukti-baru.pdf', 150, 'application/pdf'),
            'transferred_at' => '2026-10-06',
            'notes' => 'Bukti diperbaiki',
        ])->assertRedirect();

        $payout->refresh();
        $this->assertSame($originalId, $payout->id);
        $this->assertSame($originalNumber, $payout->payout_number);
        $this->assertSame(Payout::STATUS_TRANSFERRED, $payout->status);
        $this->assertSame(1, Payout::where('kos_id', $this->kos->id)->count());
        $this->assertNull($payout->verified_at);
        $this->assertNull($payout->verified_by);
    }

    public function test_invalid_transition_verify_pending_directly_is_rejected(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->post(route('owner.payouts.verify', $payout))
            ->assertSessionHas('error');

        $this->assertSame(Payout::STATUS_PENDING, $payout->fresh()->status);
    }

    public function test_verified_payout_is_terminal_cannot_transfer_again(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();
        $this->transferViaHttp($payout)->assertRedirect();

        $this->actingAs($this->superAdmin)
            ->post(route('owner.payouts.verify', $payout))
            ->assertRedirect();

        $this->transferViaHttp($payout->fresh())->assertSessionHas('error');

        $this->assertSame(Payout::STATUS_VERIFIED, $payout->fresh()->status);
    }

    // ── Security ─────────────────────────────────────────────

    public function test_proof_download_authorization(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();
        $this->transferViaHttp($payout)->assertRedirect();
        $payout->refresh();
        $this->assertNotNull($payout->proof_path);
        Storage::disk('private')->put($payout->proof_path, 'proof-content');

        // Guest → redirect login (flush auth karena actingAs persist antar-request)
        $this->app['auth']->forgetGuards();
        $this->get(route('owner.payouts.proof', $payout))->assertRedirect(route('login'));

        // Tenant → 403 (middleware role)
        $this->actingAs($this->tenant)
            ->get(route('owner.payouts.proof', $payout))
            ->assertForbidden();

        // Owner lain → 403 (policy)
        $this->actingAs($this->ownerB)
            ->get(route('owner.payouts.proof', $payout))
            ->assertForbidden();

        // Owner kos → 200
        $this->actingAs($this->owner)
            ->get(route('owner.payouts.proof', $payout))
            ->assertOk();

        // Super admin → 200
        $this->actingAs($this->superAdmin)
            ->get(route('owner.payouts.proof', $payout))
            ->assertOk();
    }

    public function test_tenant_cannot_access_other_owners_payout_detail(): void
    {
        $this->seedApprovedIncome();
        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();

        $this->actingAs($this->tenant)
            ->get(route('owner.payouts.show', $payout))
            ->assertForbidden();
    }

    public function test_owner_a_cannot_set_property_owner_on_owner_b_kos(): void
    {
        $this->actingAs($this->owner)->post(route('owner.payouts.set-property-owner', $this->kosB), [
            'name' => 'Hacker Owner',
            'bank_name' => 'HACK',
            'account_number' => '000000000000',
            'account_holder_name' => 'Hacker',
        ])->assertRedirect();

        $this->assertNull($this->kosB->fresh()->property_owner_id);
        $this->assertSame(0, PropertyOwner::where('name', 'Hacker Owner')->count());
    }

    public function test_owner_can_set_property_owner_for_own_kos_in_one_transaction(): void
    {
        $kosNoPo = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
            'name' => 'Kos Baru Diatur',
        ]);

        $this->actingAs($this->owner)->post(route('owner.payouts.set-property-owner', $kosNoPo), [
            'name' => 'Siti Aminah',
            'phone' => '081234567890',
            'email' => 'siti@example.com',
            'bank_name' => 'MANDIRI',
            'account_number' => '009876543210',
            'account_holder_name' => 'Siti Aminah',
        ])->assertRedirect(route('owner.payouts.index'));

        $kosNoPo->refresh();
        $this->assertNotNull($kosNoPo->property_owner_id);

        $po = PropertyOwner::findOrFail($kosNoPo->property_owner_id);
        $this->assertSame('Siti Aminah', $po->name);

        $bank = PropertyOwnerBankAccount::where('property_owner_id', $po->id)->firstOrFail();
        $this->assertSame('MANDIRI', $bank->bank_name);
        $this->assertSame('009876543210', $bank->account_number); // leading zero aman
        $this->assertSame('Siti Aminah', $bank->account_holder_name);
    }

    public function test_gross_calculation_counts_only_approved_in_period_both_chains(): void
    {
        $this->seedApprovedIncome();

        $this->createPayoutViaHttp()->assertRedirect();
        $payout = Payout::where('kos_id', $this->kos->id)->firstOrFail();

        $this->assertSame(3500000.0, (float) $payout->gross_amount);
        $this->assertSame(350000.0, (float) $payout->commission_total);
        $this->assertSame(3150000.0, (float) $payout->amount);
    }

    public function test_index_dropdown_is_scoped_to_owner_own_kos(): void
    {
        $this->seedApprovedIncome();

        $this->actingAs($this->owner)
            ->get(route('owner.payouts.index'))
            ->assertOk()
            ->assertSee('Kos Gejayan Owner A')
            ->assertDontSee('Kos Milik Owner B');
    }

    public function test_owner_bank_account_and_distribution_features_remain_untouched(): void
    {
        // Smoke: fitur existing owner_bank_accounts tetap berfungsi
        $this->actingAs($this->owner)->put(route('owner.bank-account.update'), [
            'bank_name' => 'BCA',
            'account_number' => '00111222333',
            'account_holder_name' => 'Owner A',
        ])->assertRedirect();

        $account = OwnerBankAccount::where('owner_id', $this->owner->id)->firstOrFail();
        $this->assertSame('00111222333', $account->account_number);

        // Smoke: distribution route tetap ada
        $this->actingAs($this->owner)
            ->get(route('owner.distribution.index'))
            ->assertOk();
    }
}
