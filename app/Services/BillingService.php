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

        // end_date adalah tanggal check-out (eksklusif), sehingga kontrak yang
        // berakhir hari ini tidak perlu dibuatkan tagihan periode baru.
        $kontraks = Kontrak::with(['penghuni.user', 'penghuni.kos.owner', 'kamar'])
            ->where('kos_id', $kos->id)
            ->where('status', 'active')
            ->where('start_date', '<=', $billingDate)
            ->where('end_date', '>', $billingDate)
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
     * Siklus mengikuti tanggal mulai kontrak, bukan billing_day kos. Parameter
     * $billingDay dipertahankan untuk kompatibilitas pemanggil lama.
     *
     * @return 'generated'|'skipped'
     */
    public function generateForKontrak(Kontrak $kontrak, int $billingDay, int $dueDays, ?Carbon $billingDate = null): string
    {
        $billingDate ??= Carbon::today();

        $period = $this->calculatePeriod($kontrak, $billingDay, $billingDate);

        \Log::info('DEBUG generateForKontrak', [
            'kontrak_id' => $kontrak->id,
            'start' => $kontrak->start_date,
            'end' => $kontrak->end_date,
            'billingDate' => $billingDate,
            'period' => $period,
        ]);

        if ($period === null) {
            return 'skipped';
        }

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
     * Buat tagihan pertama saat kontrak aktif (dipanggil setelah check-in).
     * Periode dihitung dari tanggal mulai kontrak sehingga check-in lebih awal
     * tetap menghasilkan tagihan future-dated.
     *
     * @return 'generated'|'skipped'
     */
    public function generateFirstBill(Kontrak $kontrak): string
    {
        $kos = $kontrak->kos;

        if (! $kos) {
            return 'skipped';
        }

        \Log::info('Auto-billing first bill started', [
            'kontrak_id' => $kontrak->id,
            'kos_id' => $kontrak->kos_id,
            'start_date' => $kontrak->start_date,
        ]);

        $result = $this->generateForKontrak(
            $kontrak,
            (int) ($kos->billing_day ?? 1),
            (int) ($kos->due_days ?? 5),
            $kontrak->start_date->copy()->startOfDay()
        );

        \Log::info('Auto-billing first bill finished', [
            'kontrak_id' => $kontrak->id,
            'result' => $result,
        ]);

        return $result;
    }

    /**
     * Hitung period billing berdasarkan tanggal mulai kontrak.
     *
     * - monthly: satu periode = satu bulan dari tanggal mulai kontrak.
     * - weekly: satu periode = tujuh hari dari tanggal mulai kontrak.
     * - daily: satu periode = satu hari.
     *
     * end_date kontrak diperlakukan sebagai tanggal check-out (eksklusif).
     */
    public function calculatePeriod(Kontrak $kontrak, int $billingDay, Carbon $billingDate): ?array
    {
        $contractStart = $kontrak->start_date->copy()->startOfDay();
        $contractEnd = $kontrak->end_date ? $kontrak->end_date->copy()->startOfDay() : null;
        $lastBillableDay = $contractEnd?->copy()->subDay();

        $billingDate = $billingDate->copy()->startOfDay();

        if ($billingDate->lt($contractStart)) {
            $billingDate = $contractStart->copy();
        }

        if ($lastBillableDay !== null && $billingDate->gt($lastBillableDay)) {
            return null;
        }

        $rentalType = $kontrak->rental_type;

        if ($rentalType === 'daily') {
            $days = max(0, (int) $contractStart->diffInDays($billingDate));
            $periodStart = $contractStart->copy()->addDays($days);
            $periodEnd = $periodStart->copy();
        } elseif ($rentalType === 'weekly') {
            $days = max(0, (int) $contractStart->diffInDays($billingDate));
            $weeks = intdiv($days, 7);
            $periodStart = $contractStart->copy()->addWeeks($weeks);
            $periodEnd = $periodStart->copy()->addDays(6);
        } else {
            $months = ($billingDate->year - $contractStart->year) * 12
                + ($billingDate->month - $contractStart->month);

            $periodStart = $contractStart->copy()->addMonthsNoOverflow(max($months, 0));

            if ($periodStart->gt($billingDate)) {
                $months--;
                $periodStart = $contractStart->copy()->addMonthsNoOverflow(max($months, 0));
            }

            $nextStart = $contractStart->copy()->addMonthsNoOverflow(max($months, 0) + 1);
            $periodEnd = $nextStart->copy()->subDay();

        }

        if ($lastBillableDay !== null && $periodEnd->gt($lastBillableDay)) {
            $periodEnd = $lastBillableDay->copy();
        }

        if ($periodStart->gt($periodEnd)) {
            return null;
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

        $billNumber = 'TB-'.strtoupper(Str::random(12));
        $createdTagihan = null;
        $totalAmount = 0.0;

        DB::transaction(function () use ($kontrak, $period, $dueDate, $subtotal, $billNumber, &$createdTagihan, &$totalAmount) {
            $locked = Kontrak::whereKey($kontrak->id)->lockForUpdate()->first();

            if (! $locked || $locked->status !== 'active') {
                return;
            }

            // DP hanya dipotongkan pada tagihan pertama kontrak.
            $downPayment = app(DownPaymentService::class);
            $downPaymentDiscount = $downPayment->discountForNewBill($locked, $subtotal);
            $total = max(0, round($subtotal - $downPaymentDiscount, 2));
            $status = $total <= 0 ? Tagihan::STATUS_PAID : Tagihan::STATUS_UNPAID;

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
                    'down_payment_discount' => $downPaymentDiscount,
                    'penalty' => 0,
                    'total' => $total,
                    'due_date' => $dueDate,
                    'status' => $status,
                    'active_billing_key' => Tagihan::billingKey($kontrak->id, $period['period_start'], $period['period_end']),
                ]);

                $downPayment->consume($locked, $downPaymentDiscount);

                $createdTagihan = $tagihan;
                $totalAmount = $total;

                \Log::info('Auto-billing first bill generated', [
                    'kontrak_id' => $kontrak->id,
                    'bill_number' => $billNumber,
                    'period_start' => $period['period_start'],
                    'period_end' => $period['period_end'],
                    'total' => $total,
                ]);
            } catch (QueryException $e) {
                // Unique constraint violation = duplikat, skip
                \Log::info("Auto-billing: duplikat kontrak {$kontrak->id}, skip.");
            }
        });

        if (! $createdTagihan) {
            return;
        }

        DB::afterCommit(function () use ($kontrak, $billNumber, $dueDate, $totalAmount) {
            try {
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
                        number_format($totalAmount, 0, ',', '.'),
                        $dueDate
                    );
                }

                AuditLogService::create(
                    'Tagihan',
                    "Auto-billing: tagihan {$billNumber} dibuat untuk {$kontrak->penghuni->user->name}",
                    ['kontrak_id' => $kontrak->id, 'auto' => true]
                );
            } catch (\Throwable $e) {
                \Log::warning('Auto-billing notification/audit failed: '.$e->getMessage());
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

        $units = $this->calculateUnits(
            $kontrak->rental_type,
            Carbon::parse($period['period_start']),
            Carbon::parse($period['period_end'])
        );

        $totalRoomBill = $sharedService->calculateTotalRoomBillForUnits(
            $kamar,
            $kontrak->rental_type,
            $units
        );

        $created = $sharedService->createSharedTagihans(
            $kamar,
            $this->resolveBillType($kontrak->rental_type),
            $period['period_start'],
            $period['period_end'],
            $dueDate,
            totalRoomBill: $totalRoomBill,
        );

        DB::afterCommit(function () use ($created, $dueDate) {
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
        });
    }

    /**
     * Hitung jumlah unit billing untuk satu periode siklus kontrak.
     *
     * Siklus monthly selalu satu unit (satu bulan penuh, tanpa pro-rata),
     * meskipun rentang tanggalnya menyentuh dua bulan kalender.
     */
    protected function calculateUnits(string $rentalType, Carbon $periodStart, Carbon $periodEnd): int
    {
        if ($rentalType === 'daily') {
            return max(1, (int) $periodStart->copy()->startOfDay()->diffInDays($periodEnd->copy()->startOfDay()) + 1);
        }

        if ($rentalType === 'weekly') {
            $days = (int) $periodStart->copy()->startOfDay()->diffInDays($periodEnd->copy()->startOfDay()) + 1;

            return max(1, (int) ceil($days / 7));
        }

        return 1;
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
