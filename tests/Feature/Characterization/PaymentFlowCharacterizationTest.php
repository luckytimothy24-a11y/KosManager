<?php

namespace Tests\Feature\Characterization;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * FASE 0 — KARAKTERISASI TAGIHAN & PEMBAYARAN.
 *
 * Baseline: hanya pembayaran penuh yang diterima. Tidak ada alokasi
 * pembayaran sebagian, tidak ada revisi tagihan, dan bukti ditolak
 * mengembalikan tagihan ke unpaid/overdue.
 */
class PaymentFlowCharacterizationTest extends TestCase
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

        Storage::fake('private');

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'pencari']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied']);
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
            'subtotal' => 1500000,
            'discount' => 0,
            'penalty' => 0,
            'total' => 1500000,
            'status' => 'unpaid',
            'due_date' => now()->addDays(5)->toDateString(),
        ]);
    }

    public function test_full_manual_payment_moves_bill_to_pending_verification_then_paid(): void
    {
        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 1500000,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
        ])->assertRedirect();

        $pembayaran = Pembayaran::where('tagihan_id', $this->tagihan->id)->firstOrFail();
        $this->assertSame('pending', $pembayaran->verification_status);
        $this->assertSame('pending_verification', $this->tagihan->fresh()->status);
        $this->assertNotNull($pembayaran->proof_file);
        Storage::disk('private')->assertExists($pembayaran->proof_file);

        $this->actingAs($this->owner)
            ->post(route('owner.pembayaran.verify', $pembayaran))
            ->assertRedirect(route('owner.pembayaran.index'));

        $this->assertSame('approved', $pembayaran->fresh()->verification_status);
        $this->assertSame('paid', $this->tagihan->fresh()->status);
    }

    public function test_partial_payment_is_rejected_in_baseline(): void
    {
        // karakterisasi, bukan perilaku yang diinginkan.
        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 500000,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertSame(0, Pembayaran::count());
        $this->assertSame('unpaid', $this->tagihan->fresh()->status);
    }

    public function test_duplicate_active_payment_is_blocked(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 1500000,
            'payment_method' => 'qris',
        ])->assertRedirect();

        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 1500000,
            'payment_method' => 'qris',
        ])->assertSessionHasErrors('amount');

        $this->assertSame(1, Pembayaran::where('tagihan_id', $this->tagihan->id)->count());
    }

    public function test_rejected_proof_restores_bill_to_unpaid_and_records_reason(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'pending',
        ]);
        $this->tagihan->update(['status' => 'pending_verification']);

        $this->actingAs($this->owner)
            ->post(route('owner.pembayaran.reject', $pembayaran), [
                'reason' => 'Bukti tidak jelas, mohon unggah ulang.',
            ])
            ->assertRedirect(route('owner.pembayaran.index'));

        $pembayaran->refresh();
        $this->assertSame('rejected', $pembayaran->verification_status);
        $this->assertSame('Bukti tidak jelas, mohon unggah ulang.', $pembayaran->admin_notes);
        $this->assertNull($pembayaran->active_payment_key);
        $this->assertSame('unpaid', $this->tagihan->fresh()->status);
    }

    public function test_rejected_proof_restores_overdue_when_due_date_has_passed(): void
    {
        $this->tagihan->update([
            'status' => 'pending_verification',
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'pending',
        ]);

        $this->actingAs($this->owner)
            ->post(route('owner.pembayaran.reject', $pembayaran), ['reason' => 'Nominal tidak sesuai.'])
            ->assertRedirect();

        $this->assertSame('overdue', $this->tagihan->fresh()->status);
    }

    public function test_receipt_is_only_available_after_payment_is_approved(): void
    {
        $pending = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'pending',
        ]);

        $this->actingAs($this->tenant)
            ->get(route('pembayaran.receipt', $pending))
            ->assertNotFound();
    }

    public function test_payment_method_values_are_limited_to_three_channels(): void
    {
        $this->assertSame(
            ['transfer_bank', 'qris', 'e_wallet'],
            $this->enumValues('pembayarans', 'payment_method')
        );
    }

    /**
     * @return array<int, string>
     */
    private function enumValues(string $table, string $column): array
    {
        $driver = \DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $sql = (string) \DB::table('sqlite_master')
                ->where('type', 'table')
                ->where('name', $table)
                ->value('sql');

            if (preg_match('/"'.preg_quote($column, '/').'"\s+in\s*\(([^)]*)\)/i', $sql, $matches)) {
                preg_match_all("/'([^']+)'/", $matches[1], $values);

                return $values[1];
            }

            return [];
        }

        if ($driver === 'mysql') {
            $row = \DB::selectOne("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
            if (preg_match('/^enum\((.*)\)$/i', (string) ($row->Type ?? ''), $matches)) {
                preg_match_all("/'([^']+)'/", $matches[1], $values);

                return $values[1];
            }

            return [];
        }

        $this->fail("Unsupported database driver: {$driver}");
    }
}
