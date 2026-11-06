<?php

namespace App\Console\Commands;

use App\Models\Kos;
use App\Models\Payout;
use App\Services\NotificationService;
use App\Services\PropertyOwnerPayoutService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateMonthlyPayouts extends Command
{
    /**
     * Command signature
     */
    protected $signature = 'payout:create-monthly
                            {--dry-run : Tampilkan apa yang akan dibuat tanpa save}
                            {--kos-id= : Optional, specific kos_id untuk testing}';

    /**
     * Command description
     */
    protected $description = 'Create monthly payout untuk semua kos dengan approved payments. Jalankan di hari pertama bulan pada jam 2:00 AM.';

    /**
     * Execute the command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting monthly payout creation...');

        $isDryRun = (bool) $this->option('dry-run');
        $specificKosId = $this->option('kos-id');

        if ($isDryRun) {
            $this->warn('DRY');
        }

        // Tentukan periode: bulan lalu
        $today = now();
        $periodEnd = $today->clone()->subMonth()->endOfMonth()->toDateString();
        $periodStart = $today->clone()->subMonth()->startOfMonth()->toDateString();

        $this->info("📅 Processing period: {$periodStart} to {$periodEnd}");

        // Query kos dengan property_owner yang sudah ditetapkan
        $query = Kos::query()
            ->whereNotNull('property_owner_id')
            ->has('propertyOwner.bankAccount');

        if ($specificKosId) {
            $query->whereKey($specificKosId);
        }

        $kosCount = $query->count();
        $this->info("📍 Found {$kosCount} kos with property owner configured");

        if ($kosCount === 0) {
            $this->warn('⚠️  No kos found. Exiting.');

            return 0;
        }

        $successCount = 0;
        $skipCount = 0;
        $errorCount = 0;
        $createdPayouts = [];

        $kosList = $query->get();
        $bar = $this->output->createProgressBar(count($kosList));
        $bar->setMessage('🔄 Processing kos');

        foreach ($kosList as $kos) {
            $bar->advance();
            try {
                // Cek apakah payout untuk periode ini sudah ada
                $existing = Payout::where('kos_id', $kos->id)
                    ->whereDate('period_start', $periodStart)
                    ->whereDate('period_end', $periodEnd)
                    ->exists();

                if ($existing) {
                    $this->line(" <fg=yellow>⊘</> Kos {$kos->id} ({$kos->name}) - Payout sudah ada untuk periode ini");
                    $skipCount++;

                    continue;
                }

                // Cek apakah ada approved payments di periode ini
                $paymentCount = app(PropertyOwnerPayoutService::class)
                    ->resolveGross($kos, $periodStart, $periodEnd);

                if ($paymentCount <= 0) {
                    $this->line(" <fg=gray>○</> Kos {$kos->id} ({$kos->name}) - No approved payments found");
                    $skipCount++;

                    continue;
                }

                if ($isDryRun) {
                    $this->line(" <fg=cyan>✓</> DRY: Would create payout for Kos {$kos->id} ({$kos->name}) with gross {$paymentCount}");
                    $successCount++;

                    continue;
                }

                // Create payout via service
                $result = app(PropertyOwnerPayoutService::class)->createForKos(
                    auth()->user() ?? $kos->owner, // Fallback to kos owner if no auth
                    [
                        'kos_id' => $kos->id,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                    ]
                );

                if ($result['ok'] ?? false) {
                    $payout = $result['payout'];
                    $this->line(" <fg=green>✓</> Created payout {$payout->payout_number} for Kos {$kos->id}");

                    // Notify owner
                    try {
                        $owner = $kos->owner;
                        if ($owner) {
                            app(NotificationService::class)->create(
                                $owner->id,
                                'payout_created',
                                'Payout Bulanan Siap Diproses',
                                "Payout untuk {$kos->name} periode {$periodStart} s/d {$periodEnd} telah dibuat. Silakan review dan transfer sesuai jumlah yang tertera.",
                                ['payout_id' => $payout->id]
                            );
                        }
                    } catch (\Exception $e) {
                        Log::warning("Failed to send notification for payout {$payout->id}", ['error' => $e->getMessage()]);
                    }

                    $createdPayouts[] = [
                        'kos_id' => $kos->id,
                        'kos_name' => $kos->name,
                        'payout_id' => $payout->id,
                        'payout_number' => $payout->payout_number,
                        'gross_amount' => $payout->gross_amount,
                        'amount' => $payout->amount,
                    ];

                    $successCount++;
                } else {
                    $error = $result['error'] ?? 'unknown';
                    $this->line(" <fg=red>✗</> Kos {$kos->id} - {$error}");
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $this->line(" <fg=red>✗</> Kos {$kos->id} - Exception: {$e->getMessage()}");
                Log::error('Payout creation failed', [
                    'kos_id' => $kos->id,
                    'error' => $e->getMessage(),
                ]);
                $errorCount++;
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('📊 Summary:');
        $this->line("  ✓ Created: {$successCount}");
        $this->line("  ⊘ Skipped: {$skipCount}");
        $this->line("  ✗ Errors:  {$errorCount}");

        if (! empty($createdPayouts)) {
            $this->newLine();
            $this->info('📋 Created Payouts:');
            $this->table(
                ['Kos ID', 'Kos Name', 'Payout Number', 'Gross', 'Net Amount'],
                array_map(fn ($p) => [
                    $p['kos_id'],
                    $p['kos_name'],
                    $p['payout_number'],
                    'Rp '.number_format($p['gross_amount'], 0, ',', '.'),
                    'Rp '.number_format($p['amount'], 0, ',', '.'),
                ], $createdPayouts)
            );
        }

        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN completed - no changes saved');
        }

        return $errorCount === 0 ? 0 : 1;
    }
}
