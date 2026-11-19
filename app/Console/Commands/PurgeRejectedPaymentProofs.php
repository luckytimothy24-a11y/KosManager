<?php

namespace App\Console\Commands;

use App\Models\Pembayaran;
use App\Services\AuditLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeRejectedPaymentProofs extends Command
{
    protected $signature = 'payments:purge-rejected-proofs
        {--days=180 : Hapus file bukti pembayaran rejected lebih lama dari N hari}
        {--dry-run : Tampilkan jumlah yang akan diproses tanpa menghapus}';

    protected $description = 'Hapus file bukti bayar pembayaran rejected yang sudah lama (record DB tetap disimpan)';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $query = Pembayaran::where('verification_status', Pembayaran::STATUS_REJECTED)
            ->whereNotNull('proof_file')
            ->where('created_at', '<', $cutoff);

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('Tidak ada bukti pembayaran rejected yang perlu dipurge.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("{$total} bukti pembayaran rejected lebih lama dari {$days} hari akan dipurge (dry-run).");

            return self::SUCCESS;
        }

        $deletedFiles = 0;
        $failedFiles = 0;

        $query->chunkById(200, function ($payments) use (&$deletedFiles, &$failedFiles) {
            foreach ($payments as $payment) {
                $path = $payment->proof_file;

                try {
                    if ($path && Storage::disk('private')->exists($path)) {
                        Storage::disk('private')->delete($path);
                        $deletedFiles++;
                    }

                    // Record tetap disimpan sebagai audit trail; path dikosongkan
                    // agar tidak menunjuk file yang sudah tidak ada.
                    $payment->forceFill(['proof_file' => null])->saveQuietly();
                } catch (\Throwable $e) {
                    $failedFiles++;
                    \Log::warning('Failed to purge rejected payment proof: '.$e->getMessage(), [
                        'pembayaran_id' => $payment->id,
                    ]);
                }
            }
        });

        AuditLogService::create(
            'Pembayaran',
            "Purge bukti pembayaran rejected > {$days} hari: {$deletedFiles} file dihapus, {$failedFiles} gagal",
            ['days' => $days, 'deleted' => $deletedFiles, 'failed' => $failedFiles]
        );

        $this->info("Purge selesai: {$deletedFiles} file dihapus, {$failedFiles} gagal, {$total} record diproses.");

        return $failedFiles > 0 ? self::FAILURE : self::SUCCESS;
    }
}
