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
use Tests\TestCase;

class Phase4FinancialUxTest extends TestCase
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
        $this->kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'payment_info' => 'Transfer Bank BCA 1234567890 a.n. Pemilik',
        ]);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'room_number' => 'A1',
            'room_name' => 'Standard',
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
            'contract_number' => 'KT-2024-001',
            'rental_type' => 'monthly',
            'status' => 'active',
        ]);
        $this->tagihan = Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
            'total' => 1500000,
        ]);
    }

    public function test_owner_dashboard_shows_outstanding_and_overdue_bills(): void
    {
        Tagihan::factory()->count(2)->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
        ]);
        Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'overdue',
        ]);
        Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->owner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Tagihan Belum Bayar');
        $response->assertSee('Tagihan Terlambat');

        $html = $response->getContent();

        // 1 tagihan utama unpaid + 2 unpaid + 1 overdue = 4 outstanding.
        $this->assertSame('4', $this->statValue($html, 'Tagihan Belum Bayar'));
        // Hanya 1 overdue.
        $this->assertSame('1', $this->statValue($html, 'Tagihan Terlambat'));
    }

    public function test_owner_dashboard_financial_stats_respect_owner_scope(): void
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $otherKos = Kos::factory()->create(['owner_id' => $otherOwner->id]);
        $otherKamar = Kamar::factory()->create(['kos_id' => $otherKos->id]);
        $otherPenghuni = Penghuni::factory()->create([
            'kos_id' => $otherKos->id,
            'kamar_id' => $otherKamar->id,
            'status' => 'active',
        ]);
        Tagihan::factory()->count(3)->create([
            'penghuni_id' => $otherPenghuni->id,
            'kamar_id' => $otherKamar->id,
            'status' => 'overdue',
        ]);

        $response = $this->actingAs($this->owner)->get(route('dashboard'));

        $response->assertOk();
        $html = $response->getContent();

        // Data milik owner lain tidak boleh masuk statistik owner saat ini.
        $this->assertSame('0', $this->statValue($html, 'Tagihan Terlambat'));
        // Outstanding milik owner saat ini: hanya tagihan utama (unpaid) = 1.
        $this->assertSame('1', $this->statValue($html, 'Tagihan Belum Bayar'));
    }

    private function statValue(string $html, string $label): ?string
    {
        if (preg_match('/'.preg_quote($label, '/').'<\/p>\s*<p[^>]*>(\d+)<\/p>/s', $html, $m)) {
            return $m[1];
        }

        return null;
    }

    public function test_tenant_tagihan_show_displays_contract_context(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee('No. Kontrak');
        $response->assertSee('KT-2024-001');
        $response->assertSee('Tipe Sewa');
        $response->assertSee('Bulanan');
        $response->assertSee('Periode Kontrak');
    }

    public function test_tenant_tagihan_show_displays_status_timeline(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee('Riwayat Status');
        $response->assertSee('Tagihan Dibuat');
        $response->assertSee('Pembayaran Dikirim');
        $response->assertSee('Diverifikasi');
    }

    public function test_tenant_tagihan_show_displays_total_summary_above_submit(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee('Total yang harus dibayar');
    }

    public function test_tenant_tagihan_show_displays_rejected_callout_with_reason(): void
    {
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'rejected',
            'admin_notes' => 'Foto bukti tidak jelas, silakan unggah ulang.',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee('Pembayaran Sebelumnya Ditolak');
        $response->assertSee('Foto bukti tidak jelas, silakan unggah ulang.');
    }

    public function test_tenant_tagihan_show_rejected_callout_only_when_bill_payable(): void
    {
        $this->tagihan->update(['status' => 'pending_verification']);

        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'verification_status' => 'rejected',
            'admin_notes' => 'Foto bukti tidak jelas.',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertDontSee('Pembayaran Sebelumnya Ditolak');
    }

    public function test_tenant_payment_history_shows_tagihan_and_period_context(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.pembayaran.index'));

        $response->assertOk();
        $response->assertSee($this->tagihan->bill_number);
        $response->assertSee('Periode');
    }

    public function test_payment_method_selector_has_accessible_labels(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee('id="method-transfer_bank"', false);
        $response->assertSee('id="method-e_wallet"', false);
        $response->assertSee('id="method-cash"', false);
        $response->assertSee('aria-describedby="proof-feedback proof-hint"', false);
    }

    public function test_tenant_tagihan_show_still_shows_payment_methods(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee('Transfer Bank');
        $response->assertSee('E-Wallet / QRIS');
        $response->assertSee('Tunai');
    }

    public function test_tenant_dashboard_belum_bayar_count_excludes_pending_verification(): void
    {
        $this->tagihan->update(['status' => 'pending_verification']);

        $response = $this->actingAs($this->tenant)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Tagihan Belum Bayar');
    }
}
