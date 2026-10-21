<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\OwnerBankAccount;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Wiring rekening Owner ke flow pembayaran Tenant (Option A — minimal wiring).
 *
 * Resolusi rekening SELALU server-side:
 *   tagihan → kamar → kos → owner_id → OwnerBankAccount
 *   booking → kos → owner_id → OwnerBankAccount
 * TIDAK pernah dari request (owner_id / bank_account_id).
 *
 * KosManager tidak memegang dana tenant. Transfer langsung Tenant → rekening Owner.
 */
class OwnerBankAccountTenantDisplayTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private User $owner;

    private User $ownerB;

    private User $ownerC;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');

        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->ownerB = User::factory()->create(['role' => 'owner']);
        $this->ownerC = User::factory()->create(['role' => 'owner']);
    }

    /**
     * Bangun rantai Kos → Kamar → Penghuni → Kontrak → Tagihan untuk tenant.
     */
    private function makeChain(User $owner, array $kosAttrs = []): array
    {
        $kos = Kos::factory()->create(array_merge([
            'owner_id' => $owner->id,
            'status' => 'active',
        ], $kosAttrs));

        $kamar = Kamar::factory()->create([
            'kos_id' => $kos->id,
            'status' => 'available',
            'monthly_price' => 800000,
        ]);

        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
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
            'status' => 'unpaid',
            'total' => 800000,
            'subtotal' => 800000,
            'discount' => 0,
            'penalty' => 0,
        ]);

        return compact('kos', 'kamar', 'penghuni', 'kontrak', 'tagihan');
    }

    private function makeBooking(Kos $kos, Kamar $kamar, float $price = 1500000.0): Booking
    {
        return Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
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

    // ── Tenant tagihan show ──────────────────────────────────

    public function test_tenant_tagihan_show_displays_owner_bank_account(): void
    {
        $this->saveBankAccount($this->owner);
        $chain = $this->makeChain($this->owner);

        $this->actingAs($this->tenant)
            ->get(route('tenant.tagihan.show', $chain['tagihan']))
            ->assertOk()
            ->assertSee('Bayar ke Pemilik Kos')
            ->assertSee('BCA')
            ->assertSee('001234567890') // nomor LENGKAP — dibutuhkan transfer
            ->assertSee('Lucky Kos')
            ->assertSee('Transfer langsung ke rekening pemilik kos')
            ->assertSee('KosManager tidak menerima atau menyimpan dana pembayaran');
    }

    public function test_tenant_tagihan_show_falls_back_to_payment_info(): void
    {
        // ownerB: TANPA bank account, TAPI punya kos.payment_info legacy
        $chain = $this->makeChain($this->ownerB, [
            'payment_info' => 'Transfer ke BCA 999888777 a.n. Kos Legacy',
        ]);

        $this->actingAs($this->tenant)
            ->get(route('tenant.tagihan.show', $chain['tagihan']))
            ->assertOk()
            ->assertSee('Instruksi Pembayaran')
            ->assertSee('Transfer ke BCA 999888777 a.n. Kos Legacy')
            ->assertDontSee('001234567890');
    }

    public function test_tenant_tagihan_show_generic_fallback(): void
    {
        // ownerC: TANPA bank account, TANPA payment_info
        $chain = $this->makeChain($this->ownerC);

        $this->actingAs($this->tenant)
            ->get(route('tenant.tagihan.show', $chain['tagihan']))
            ->assertOk()
            ->assertSee('Hubungi pemilik kos untuk informasi rekening tujuan')
            ->assertSee('Bayar ke Pemilik Kos')
            ->assertDontSee('001234567890');
    }

    public function test_tenant_payment_page_shows_full_number_not_masked(): void
    {
        $this->saveBankAccount($this->owner);
        $chain = $this->makeChain($this->owner);

        $this->actingAs($this->tenant)
            ->get(route('tenant.tagihan.show', $chain['tagihan']))
            ->assertOk()
            ->assertSee('001234567890') // nomor lengkap di halaman pembayaran
            ->assertDontSee('********7890'); // BUKAN masked — masking hanya untuk konteks owner
    }

    // ── Tenant tagihan pay (multi-bill) ──────────────────────

    public function test_tenant_tagihan_pay_displays_owner_bank_account(): void
    {
        $this->saveBankAccount($this->owner);
        $this->makeChain($this->owner);

        $this->actingAs($this->tenant)
            ->get(route('tenant.tagihan.pay'))
            ->assertOk()
            ->assertSee('Bayar ke Pemilik Kos')
            ->assertSee('001234567890')
            ->assertSee('KosManager tidak menerima atau menyimpan dana pembayaran');
    }

    // ── Tenant booking show ──────────────────────────────────

    public function test_tenant_booking_show_displays_owner_bank_account(): void
    {
        $this->saveBankAccount($this->owner);
        $chain = $this->makeChain($this->owner);
        $booking = $this->makeBooking($chain['kos'], $chain['kamar']);

        $this->actingAs($this->tenant)
            ->get(route('tenant.booking.show', $booking))
            ->assertOk()
            ->assertSee('Bayar ke Pemilik Kos')
            ->assertSee('BCA')
            ->assertSee('001234567890')
            ->assertSee('Lucky Kos')
            ->assertSee('KosManager tidak menerima atau menyimpan dana pembayaran');
    }

    public function test_tenant_booking_show_fallback_when_no_bank(): void
    {
        $chain = $this->makeChain($this->ownerB, [
            'payment_info' => 'Transfer ke BCA 111222333 a.n. Booking Legacy',
        ]);
        $booking = $this->makeBooking($chain['kos'], $chain['kamar']);

        $this->actingAs($this->tenant)
            ->get(route('tenant.booking.show', $booking))
            ->assertOk()
            ->assertSee('Instruksi Pembayaran')
            ->assertSee('Transfer ke BCA 111222333 a.n. Booking Legacy')
            ->assertDontSee('001234567890');
    }

    // ── Server-side resolution ───────────────────────────────

    public function test_bank_resolved_from_kos_owner_not_other_owner(): void
    {
        // Dua owner dengan rekening BERBEDA — tagihan hanya di kos owner A
        $this->saveBankAccount($this->owner, [
            'bank_name' => 'BCA',
            'account_number' => '001111111111',
            'account_holder_name' => 'Owner A',
        ]);
        $this->saveBankAccount($this->ownerB, [
            'bank_name' => 'MANDIRI',
            'account_number' => '002222222222',
            'account_holder_name' => 'Owner B',
        ]);

        $chain = $this->makeChain($this->owner);

        $this->actingAs($this->tenant)
            ->get(route('tenant.tagihan.show', $chain['tagihan']))
            ->assertOk()
            ->assertSee('BCA')
            ->assertSee('001111111111')
            ->assertSee('Owner A')
            ->assertDontSee('MANDIRI')
            ->assertDontSee('002222222222')
            ->assertDontSee('Owner B');
    }

    public function test_disclaimer_text_on_tenant_payment_page(): void
    {
        $this->saveBankAccount($this->owner);
        $chain = $this->makeChain($this->owner);

        $this->actingAs($this->tenant)
            ->get(route('tenant.tagihan.show', $chain['tagihan']))
            ->assertOk()
            ->assertSee('KosManager tidak menerima atau menyimpan dana pembayaran');
    }

    // ── Owner panel UX ───────────────────────────────────────

    public function test_owner_sidebar_contains_rekening_pembayaran(): void
    {
        $this->actingAs($this->owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rekening Pembayaran')
            ->assertSee('/owner/bank-account');
    }

    public function test_owner_dashboard_shows_missing_bank_alert(): void
    {
        $this->actingAs($this->ownerB) // tanpa rekening
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rekening Pembayaran Belum Diatur')
            ->assertSee('Tambahkan rekening agar Tenant dapat mengetahui tujuan pembayaran.')
            ->assertSee('Tambah Rekening')
            ->assertSee('/owner/bank-account');
    }

    public function test_owner_dashboard_shows_active_bank_masked(): void
    {
        $this->saveBankAccount($this->owner);

        $this->actingAs($this->owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rekening Pembayaran Aktif')
            ->assertSee('BCA')
            ->assertSee('********7890') // masked
            ->assertSee('Lucky Kos')
            ->assertSee('Kelola Rekening')
            ->assertDontSee('001234567890'); // nomor lengkap TIDAK bocor di dashboard
    }

    // ── Owner management tetap berfungsi (regression smoke) ──

    public function test_owner_can_still_manage_bank_account(): void
    {
        $account = $this->saveBankAccount($this->owner);

        $this->assertSame('BCA', $account->bank_name);
        $this->assertSame('001234567890', $account->account_number); // leading zero aman
        $this->assertSame('Lucky Kos', $account->account_holder_name);

        // Update in-place tetap bekerja
        $this->actingAs($this->owner)->put(route('owner.bank-account.update'), [
            'bank_name' => 'MANDIRI',
            'account_number' => '009999999999',
            'account_holder_name' => 'Owner Updated',
        ])->assertRedirect();

        $account->refresh();
        $this->assertSame('MANDIRI', $account->bank_name);
        $this->assertSame('009999999999', $account->account_number);
        $this->assertSame(1, OwnerBankAccount::where('owner_id', $this->owner->id)->count());
    }

    public function test_tenant_cannot_update_owner_bank_account(): void
    {
        $this->saveBankAccount($this->owner);

        $this->actingAs($this->tenant)->put(route('owner.bank-account.update'), [
            'bank_name' => 'HACKED',
            'account_number' => '000000000000',
            'account_holder_name' => 'Hacker',
        ])->assertForbidden();

        $account = OwnerBankAccount::where('owner_id', $this->owner->id)->firstOrFail();
        $this->assertSame('BCA', $account->bank_name);
        $this->assertSame('001234567890', $account->account_number);
    }
}
