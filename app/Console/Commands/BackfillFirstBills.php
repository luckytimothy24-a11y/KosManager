<?php

namespace App\Console\Commands;

use App\Models\Kontrak;
use App\Models\Tagihan;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BackfillFirstBills extends Command
{
    protected $signature = 'tagihan:backfill-first-bills {--dry-run : Tampilkan audit tanpa membuat tagihan} {--limit= : Batasi jumlah kontrak}';

    protected $description = 'Backfill tagihan pertama untuk kontrak aktif yang belum memiliki tagihan.';

    public function handle(BillingService $billingService): int
    {
        $query = Kontrak::with(['penghuni.user', 'kos'])
            ->where('status', 'active')
            ->whereDoesntHave('tagihans');

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $kontraks = $query->orderBy('id')->get();

        if ($kontraks->isEmpty()) {
            $this->info('Tidak ada kontrak aktif tanpa tagihan.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        $this->info('Audit kontrak tanpa tagihan pertama:');

        $rows = [];
        foreach ($kontraks as $kontrak) {
            $period = $billingService->calculatePeriod($kontrak, (int) ($kontrak->kos->billing_day ?? 1), Carbon::parse($kontrak->start_date));

            $rows[] = [
                'ID' => $kontrak->id,
                'Kontrak' => $kontrak->contract_number,
                'Kos' => $kontrak->kos->name ?? '-',
                'Penghuni' => $kontrak->penghuni->user->name ?? '-',
                'Periode' => $period ? $period['period_start'].' s/d '.$period['period_end'] : '-',
                'Harga' => number_format((float) $kontrak->rental_price, 0, ',', '.'),
                'DP' => number_format((float) ($kontrak->down_payment_amount ?? 0), 0, ',', '.'),
                'Status' => $dryRun ? 'DRY-RUN' : 'AKAN DIBUAT',
            ];
        }

        $this->table(array_keys($rows[0]), $rows);

        if ($dryRun) {
            $this->info('Mode dry-run: tidak ada tagihan yang dibuat.');

            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;

        foreach ($kontraks as $kontrak) {
            if (Tagihan::where('kontrak_id', $kontrak->id)->exists()) {
                $this->warn("Kontrak #{$kontrak->id} sudah memiliki tagihan, skip.");
                $skipped++;

                continue;
            }

            $result = $billingService->generateFirstBill($kontrak);

            if ($result === 'generated') {
                $this->info("Kontrak #{$kontrak->id}: tagihan dibuat.");
                $created++;
            } else {
                $this->warn("Kontrak #{$kontrak->id}: tidak dibuat ({$result}).");
                $skipped++;
            }
        }

        $this->info("Selesai. Dibuat: {$created}, Skip: {$skipped}.");

        return self::SUCCESS;
    }
}
