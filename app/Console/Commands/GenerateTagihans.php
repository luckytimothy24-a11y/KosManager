<?php

namespace App\Console\Commands;

use App\Models\Kontrak;
use App\Models\Kos;
use App\Services\BillingService;
use Illuminate\Console\Command;

class GenerateTagihans extends Command
{
    protected $signature = 'tagihan:auto-generate {--kos= : ID kos spesifik} {--dry-run : Hanya tampilkan, jangan buat tagihan}';

    protected $description = 'Buat tagihan otomatis untuk kontrak aktif berdasarkan pengaturan billing_day kos';

    public function handle(BillingService $billingService): int
    {
        $query = Kos::where('auto_billing_enabled', true)->where('status', 'active');

        if ($kosId = $this->option('kos')) {
            $query->where('id', $kosId);
        }

        $kosList = $query->get();

        if ($kosList->isEmpty()) {
            $this->info('Tidak ada kos dengan auto_billing_enabled aktif.');

            return self::SUCCESS;
        }

        $dryRun = $this->option('dry-run');
        $totalGenerated = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        foreach ($kosList as $kos) {
            $this->newLine();
            $this->line("<info>Kos: {$kos->name}</info> (billing_day: {$kos->billing_day}, due_days: {$kos->due_days})");

            if ($dryRun) {
                $this->line('  [DRY-RUN] Tidak membuat tagihan.');
                $this->displayDryRun($kos);

                continue;
            }

            $stats = $billingService->generateForKos($kos);
            $totalGenerated += $stats['generated'];
            $totalSkipped += $stats['skipped'];
            $totalErrors += $stats['errors'];

            $this->line("  Generated: {$stats['generated']}, Skipped: {$stats['skipped']}, Errors: {$stats['errors']}");
        }

        $this->newLine();
        $this->info("Selesai. Total: Generated={$totalGenerated}, Skipped={$totalSkipped}, Errors={$totalErrors}");

        return self::SUCCESS;
    }

    private function displayDryRun(Kos $kos): void
    {
        $billingService = app(BillingService::class);

        $kontraks = Kontrak::with(['penghuni.user', 'kamar'])
            ->where('kos_id', $kos->id)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();

        foreach ($kontraks as $kontrak) {
            $period = $billingService->calculatePeriod($kontrak, $kos->billing_day, now());
            $alreadyBilled = $billingService->alreadyBilled($kontrak, $period['period_start'], $period['period_end']);
            $status = $alreadyBilled ? '<comment>SKIP (sudah ada)</comment>' : '<info>AKAN DIBUAT</info>';
            $tenantName = $kontrak->penghuni->user->name ?? 'N/A';
            $this->line("  Kontrak #{$kontrak->id} ({$tenantName}) [{$period['period_start']} s/d {$period['period_end']}] → {$status}");
        }
    }
}
