<?php

namespace App\Services;

use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingService
{
    /**
     * Generate tagihan untuk semua kontrak aktif di satu kos.
     *
     * @return array{generated: int, skipped: int, errors: int}
     */
    public function generateForKos(Kos $kos, ?Carbon $billingDate = null): array
    {
        $billingDate ??= Carbon::today();
        $billingDay = min($kos->billing_day, $billingDate->daysInMonth);
        $dueDays = $kos->due_days;

        $kontraks = Kontrak::with(['penghuni.user', 'penghuni.kos.owner', 'kamar'])
            ->where('kos_id', $kos->id)
            ->where('status', 'active')
            ->where('start_date', '<=', $billingDate)
            ->where('end_date', '>=', $billingDate)
            ->get();

        $stats = ['generated' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($kontraks as $kontrak) {
            try {
                $result = $this->generateForKontrak($kontrak, $billingDay, $dueDays, $billingDate);
                $stats[$result]++;
            } catch (\Throwable $e) {
                \Log::warning("Auto-billing: gagal generate untuk kontrak {$kontrak->id}: ".$e->getMessage());
                $stats['errors']++;
            }
        }

        return $stats;
    }

    /**
     * Generate tagihan untuk satu kontrak.
     *
     * @return 'generated'|'skipped'
     */
    public function generateForKontrak(Kontrak $kontrak, int $billingDay, int $dueDays, ?Carbon $billingDate = null): string
    {
        $billingDate ??= Carbon::today();

        $period = $this->calculatePeriod($kontrak, $billingDay, $billingDate);

        if ($this->alreadyBilled($kontrak, $period['period_start'], $period['period_end'])) {
            return 'skipped';
        }

        $dueDate = Carbon::parse($period['period_end'])->addDays($dueDays)->toDateString();

        if ($kontrak->isShared()) {
            $this->generateShared($kontrak, $period, $dueDate);
        } else {
            $this->generatePersonal($kontrak, $period, $dueDate);
        }

        return 'generated';
    }

    /**
     * Hitung period billing berdasarkan billing_day dan rental_type kontrak.
     */
    public function calculatePeriod(Kontrak $kontrak, int $billingDay, Carbon $billingDate): array
    {
        $rentalType = $kontrak->rental_type;

        if ($rentalType === 'daily') {
            $periodStart = $billingDate->copy()->startOfDay();
            $periodEnd = $billingDate->copy()->endOfDay();

            return [
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ];
        }

        if ($rentalType === 'weekly') {
            $periodStart = $billingDate->copy()->startOfWeek();
            $periodEnd = $billingDate->copy()->endOfWeek();

            return [
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ];
        }

        // monthly
        $month = $billingDate->month;
        $year = $billingDate->year;

        $periodStart = Carbon::createFromDate($year, $month, min($billingDay, Carbon::createFromDate($year, $month, 1)->daysInMonth))->startOfDay();
        $nextMonth = $periodStart->copy()->addMonthNoOverflow();
        $periodEnd = $nextMonth->copy()->subDay()->endOfDay();

        // Pastikan tidak melewati end_date kontrak
        if ($kontrak->end_date && $periodEnd->greaterThan($kontrak->end_date)) {
            $periodEnd = Carbon::parse($kontrak->end_date)->endOfDay();
        }

        return [
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
        ];
    }

    /**
     * Cek apakah tagihan sudah ada untuk kombinasi kontrak + periode.
     */
    public function alreadyBilled(Kontrak $kontrak, string $periodStart, string $periodEnd): bool
    {
        return Tagihan::where('kontrak_id', $kontrak->id)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->exists();
    }

    /**
     * Buat tagihan personal (satu kontrak = satu tagihan).
     */
    protected function generatePersonal(Kontrak $kontrak, array $period, string $dueDate): void
    {
        $periodStart = Carbon::parse($period['period_start']);
        $periodEnd = Carbon::parse($period['period_end']);

        $units = $this->calculateUnits($kontrak->rental_type, $periodStart, $periodEnd);
        $subtotal = round((float) $kontrak->rental_price * $units, 2);
        $total = $subtotal;

        DB::transaction(function () use ($kontrak, $period, $dueDate, $subtotal, $total) {
            $locked = Kontrak::whereKey($kontrak->id)->lockForUpdate()->first();

            if (! $locked || $locked->status !== 'active') {
                return;
            }

            $billNumber = 'TB-'.strtoupper(Str::random(12));

            try {
                $tagihan = Tagihan::create([
                    'bill_number' => $billNumber,
                    'penghuni_id' => $kontrak->penghuni_id,
                    'kontrak_id' => $kontrak->id,
                    'kamar_id' => $kontrak->kamar_id,
                    'bill_type' => $this->resolveBillType($kontrak->rental_type),
                    'period_start' => $period['period_start'],
                    'period_end' => $period['period_end'],
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'penalty' => 0,
                    'total' => $total,
                    'due_date' => $dueDate,
                    'status' => Tagihan::STATUS_UNPAID,
                    'active_billing_key' => Tagihan::billingKey($kontrak->id, $period['period_start'], $period['period_end']),
                ]);

                NotificationService::billCreated(
                    $kontrak->penghuni->user_id,
                    $billNumber,
                    $dueDate
                );

                if ($kontrak->penghuni?->kos?->owner) {
                    NotificationService::billCreatedOwner(
                        $kontrak->penghuni->kos->owner->id,
                        $kontrak->penghuni->user->name,
                        $billNumber,
                        number_format($total, 0, ',', '.'),
                        $dueDate
                    );
                }

                AuditLogService::create(
                    'Tagihan',
                    "Auto-billing: tagihan {$billNumber} dibuat untuk {$kontrak->penghuni->user->name}",
                    ['kontrak_id' => $kontrak->id, 'auto' => true]
                );
            } catch (QueryException $e) {
                // Unique constraint violation = duplikat, skip
                \Log::info("Auto-billing: duplikat kontrak {$kontrak->id}, skip.");
            }
        });
    }

    /**
     * Buat tagihan shared (satu kamar = dibagi ke semua penghuni).
     */
    protected function generateShared(Kontrak $kontrak, array $period, string $dueDate): void
    {
        $kamar = $kontrak->kamar;
        $sharedService = app(SharedBillingService::class);

        $created = $sharedService->createSharedTagihans(
            $kamar,
            $this->resolveBillType($kontrak->rental_type),
            $period['period_start'],
            $period['period_end'],
            $dueDate,
        );

        foreach ($created as $tagihan) {
            try {
                $penghuni = $tagihan->penghuni()->with(['user', 'kos.owner'])->first();
                if ($penghuni && $penghuni->user) {
                    NotificationService::billCreated(
                        $penghuni->user_id,
                        $tagihan->bill_number,
                        $dueDate
                    );

                    if ($penghuni->kos?->owner) {
                        NotificationService::billCreatedOwner(
                            $penghuni->kos->owner->id,
                            $penghuni->user->name,
                            $tagihan->bill_number,
                            number_format($tagihan->total, 0, ',', '.'),
                            $dueDate
                        );
                    }

                    AuditLogService::create(
                        'Tagihan',
                        "Auto-billing (shared): tagihan {$tagihan->bill_number} dibuat",
                        ['kontrak_id' => $tagihan->kontrak_id, 'auto' => true]
                    );
                }
            } catch (\Throwable $e) {
                \Log::warning("Auto-billing shared notification failed for tagihan {$tagihan->id}: ".$e->getMessage());
            }
        }
    }

    /**
     * Hitung jumlah unit billing.
     */
    protected function calculateUnits(string $rentalType, Carbon $periodStart, Carbon $periodEnd): int
    {
        if ($rentalType === 'daily') {
            return (int) $periodStart->startOfDay()->diffInDays($periodEnd->startOfDay()) + 1;
        }

        if ($rentalType === 'weekly') {
            $days = (int) $periodStart->startOfDay()->diffInDays($periodEnd->startOfDay()) + 1;

            return (int) ceil($days / 7);
        }

        $months = ($periodEnd->year - $periodStart->year) * 12
            + ($periodEnd->month - $periodStart->month) + 1;

        return max($months, 1);
    }

    protected function resolveBillType(string $rentalType): string
    {
        return match ($rentalType) {
            'daily' => 'Sewa Harian',
            'weekly' => 'Sewa Mingguan',
            default => 'Sewa Bulanan',
        };
    }
}
