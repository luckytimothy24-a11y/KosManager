<?php

namespace App\Console\Commands;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedDemoPenghuni extends Command
{
    protected $signature = 'penghuni:seed-demo {--kos=} : Filter nama kos (opsional, pisahkan dengan koma)';

    protected $description = 'Tambah penghuni aktif demo (penghuni + kontrak + tagihan) agar dropdown laporan kerusakan lebih lengkap';

    /**
     * [email tenant, nama kos, nomor kamar]
     */
    private const TARGETS = [
        // Utama: tenant demo login
        ['tenant@example.com', 'Kos Mawar', 'SH-01'],
        ['tenant@example.com', 'Kos Kenanga', 'SH-02'],
        ['tenant@example.com', 'Kost Nawasena Seturan', 'SH-01'],
        // Opsional: factory tenant agar login lain tidak kosong
        ['ehariyah@example.org', 'Kost Nawasena Seturan', 'SH-02'],
        ['bsiregar@example.com', 'Kos Arunika Babarsari', '101'],
        ['shania27@example.org', 'Kos Maleo Residence', 'M-01'],
    ];

    public function handle(): int
    {
        $kosFilter = $this->option('kos');
        $kosNames = $kosFilter
            ? array_values(array_filter(array_map('trim', explode(',', (string) $kosFilter))))
            : null;

        $created = 0;
        $skipped = 0;
        $failed = 0;

        foreach (self::TARGETS as [$email, $kosName, $roomNumber]) {
            if ($kosNames !== null && ! in_array($kosName, $kosNames, true)) {
                continue;
            }

            $user = User::where('email', $email)->first();
            if (! $user) {
                $this->warn("SKIP  User tidak ditemukan: {$email}");
                $failed++;

                continue;
            }

            $kos = Kos::where('name', $kosName)->first();
            if (! $kos) {
                $this->warn("SKIP  Kos tidak ditemukan: {$kosName}");
                $failed++;

                continue;
            }

            $kamar = Kamar::where('kos_id', $kos->id)->where('room_number', $roomNumber)->first();
            if (! $kamar) {
                $this->warn("SKIP  Kamar tidak ditemukan: {$kosName} / {$roomNumber}");
                $failed++;

                continue;
            }

            $exists = Penghuni::where('user_id', $user->id)
                ->where('kos_id', $kos->id)
                ->where('kamar_id', $kamar->id)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                $this->line("  SKIP  {$email} sudah punya stay di {$kosName} ({$roomNumber})");
                $skipped++;

                continue;
            }

            $occupancy = Penghuni::where('kamar_id', $kamar->id)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->count();

            if ($occupancy >= (int) $kamar->capacity) {
                $this->warn("  SKIP  {$kosName} ({$roomNumber}) penuh ({$occupancy}/{$kamar->capacity})");
                $failed++;

                continue;
            }

            try {
                DB::transaction(function () use ($user, $kos, $kamar, &$created) {
                    $penghuni = Penghuni::create([
                        'user_id' => $user->id,
                        'kos_id' => $kos->id,
                        'kamar_id' => $kamar->id,
                        'identity_number' => '32'.str_pad((string) ($user->id * 1000 + $kamar->id), 12, '0', STR_PAD_LEFT),
                        'phone' => $user->phone ?? '08'.str_pad((string) ($user->id * 1000000 + $kamar->id), 10, '0', STR_PAD_LEFT),
                        'address' => 'Jl. Demo No. '.$user->id.', Yogyakarta',
                        'check_in_date' => now()->subMonth()->toDateString(),
                        'status' => 'active',
                    ]);

                    $billingMode = $kamar->billing_mode ?? 'personal';
                    $kontrak = Kontrak::create([
                        'contract_number' => $this->deterministicNumber('KT-DEMO', $user->id, $kamar->id),
                        'penghuni_id' => $penghuni->id,
                        'kos_id' => $kos->id,
                        'kamar_id' => $kamar->id,
                        'rental_type' => 'monthly',
                        'rental_price' => $kamar->monthly_price,
                        'billing_mode' => $billingMode,
                        'start_date' => now()->subMonth()->toDateString(),
                        'end_date' => now()->addMonths(5)->toDateString(),
                        'status' => 'active',
                    ]);

                    $periodStart = now()->subMonth()->startOfMonth();
                    $periodEnd = now()->copy()->endOfMonth();
                    $alreadyBilled = Tagihan::where('kontrak_id', $kontrak->id)->exists();

                    if (! $alreadyBilled) {
                        Tagihan::create([
                            'bill_number' => $this->deterministicNumber('TG-DEMO', $user->id, $kamar->id),
                            'penghuni_id' => $penghuni->id,
                            'kontrak_id' => $kontrak->id,
                            'kamar_id' => $kamar->id,
                            'bill_type' => 'rent',
                            'period_start' => $periodStart->toDateString(),
                            'period_end' => $periodEnd->toDateString(),
                            'subtotal' => $kamar->monthly_price,
                            'discount' => 0,
                            'penalty' => 0,
                            'total' => $kamar->monthly_price,
                            'due_date' => now()->addDays(7)->toDateString(),
                            'status' => 'unpaid',
                            'active_billing_key' => $kontrak->id.':'.$periodStart->toDateString().':'.$periodEnd->toDateString(),
                        ]);
                    }

                    // Sync status kamar
                    $occupancy = Penghuni::where('kamar_id', $kamar->id)
                        ->where('status', 'active')
                        ->whereNull('deleted_at')
                        ->count();
                    $capacity = (int) $kamar->capacity;
                    $isShared = $billingMode === 'shared';

                    if (! $isShared && $occupancy >= $capacity) {
                        $kamar->update(['status' => 'occupied']);
                    } elseif ($isShared && $occupancy >= $capacity) {
                        $kamar->update(['status' => 'occupied']);
                    } elseif ($occupancy > 0 && $kamar->status === 'available' && ! $isShared) {
                        $kamar->update(['status' => 'occupied']);
                    }
                    // Shared dengan sisa slot: biarkan available (occupancy-aware)

                    $created++;
                });

                $this->info("  + {$email} → {$kosName} ({$roomNumber}, cap {$kamar->capacity}, {$kamar->billing_mode})");
            } catch (\Throwable $e) {
                $this->error("  FAIL {$email} → {$kosName}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Selesai: {$created} stay baru, {$skipped} dilewati, {$failed} gagal.");

        return self::SUCCESS;
    }

    private function deterministicNumber(string $prefix, int $userId, int $kamarId): string
    {
        // Max 20 chars for contract_number / bill_number
        $raw = $prefix.'-'.$userId.'-'.$kamarId;

        return substr($raw, 0, 20);
    }
}
