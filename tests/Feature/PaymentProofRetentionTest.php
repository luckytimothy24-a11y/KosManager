<?php

namespace Tests\Feature;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentProofRetentionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    public function test_replacing_proof_file_deletes_the_old_file(): void
    {
        $tagihan = Tagihan::factory()->create();

        Storage::disk('private')->put('bukti-pembayaran/old.png', 'old');
        Storage::disk('private')->put('bukti-pembayaran/new.png', 'new');

        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'proof_file' => 'bukti-pembayaran/old.png',
            'verification_status' => Pembayaran::STATUS_PENDING,
        ]);

        $pembayaran->update(['proof_file' => 'bukti-pembayaran/new.png']);

        Storage::disk('private')->assertMissing('bukti-pembayaran/old.png');
        Storage::disk('private')->assertExists('bukti-pembayaran/new.png');
        $this->assertSame('bukti-pembayaran/new.png', $pembayaran->fresh()->proof_file);
    }

    public function test_purge_command_deletes_only_old_rejected_proofs(): void
    {
        Storage::disk('private')->put('bukti-pembayaran/rejected-old.png', 'old');
        Storage::disk('private')->put('bukti-pembayaran/rejected-new.png', 'new');
        Storage::disk('private')->put('bukti-pembayaran/approved-old.png', 'approved');

        $tagihan = Tagihan::factory()->create();

        $oldRejected = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'proof_file' => 'bukti-pembayaran/rejected-old.png',
            'verification_status' => Pembayaran::STATUS_REJECTED,
        ]);
        $oldRejected->forceFill(['created_at' => now()->subDays(200)])->saveQuietly();

        $newRejected = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'proof_file' => 'bukti-pembayaran/rejected-new.png',
            'verification_status' => Pembayaran::STATUS_REJECTED,
        ]);

        $approved = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'proof_file' => 'bukti-pembayaran/approved-old.png',
            'verification_status' => Pembayaran::STATUS_APPROVED,
        ]);
        $approved->forceFill(['created_at' => now()->subDays(200)])->saveQuietly();

        $this->artisan('payments:purge-rejected-proofs', ['--days' => 180])
            ->assertExitCode(0);

        Storage::disk('private')->assertMissing('bukti-pembayaran/rejected-old.png');
        Storage::disk('private')->assertExists('bukti-pembayaran/rejected-new.png');
        Storage::disk('private')->assertExists('bukti-pembayaran/approved-old.png');

        // Record DB tetap ada (audit trail), hanya path yang dikosongkan.
        $this->assertDatabaseHas('pembayarans', ['id' => $oldRejected->id, 'proof_file' => null]);
        $this->assertDatabaseHas('pembayarans', ['id' => $newRejected->id, 'proof_file' => 'bukti-pembayaran/rejected-new.png']);
        $this->assertDatabaseHas('pembayarans', ['id' => $approved->id, 'proof_file' => 'bukti-pembayaran/approved-old.png']);
    }

    public function test_purge_dry_run_does_not_delete_files(): void
    {
        Storage::disk('private')->put('bukti-pembayaran/keep.png', 'data');

        $tagihan = Tagihan::factory()->create();
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'proof_file' => 'bukti-pembayaran/keep.png',
            'verification_status' => Pembayaran::STATUS_REJECTED,
        ]);
        $pembayaran->forceFill(['created_at' => now()->subDays(400)])->saveQuietly();

        $this->artisan('payments:purge-rejected-proofs', ['--days' => 180, '--dry-run' => true])
            ->assertExitCode(0);

        Storage::disk('private')->assertExists('bukti-pembayaran/keep.png');
        $this->assertDatabaseHas('pembayarans', ['id' => $pembayaran->id, 'proof_file' => 'bukti-pembayaran/keep.png']);
    }
}
