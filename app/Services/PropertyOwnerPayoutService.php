<?php

namespace App\Services;

use App\Models\Kos;
use App\Models\Payout;
use App\Models\PayoutAdjustment;
use App\Models\PayoutDisbursement;
use App\Models\PayoutItem;
use App\Models\Pembayaran;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Payout/Settlement ke Pemilik Kos Asli — SOURCE OF TRUTH.
 *
 * Tanggung jawab service ini (controller JANGAN menghitung nominal):
 * - resolve Owner dari authenticated user (scope check)
 * - resolve property_owner_id dari kos.property_owner_id (bukan request)
 * - hitung gross dari pembayarans approved (tagihan chain + booking chain)
 * - pastikan payment_date masuk periode
 * - cegah duplicate (kos + periode) + lock transaksi
 * - tentukan amount (gross - fee; fee MVP = 0, tidak dari request)
 * - transisi status dengan guards
 *
 * Transfer bank dilakukan OWNER di luar aplikasi. KosManager hanya mencatat.
 */
class PropertyOwnerPayoutService
{
    public function resolveGross(Kos $kos, string $periodStart, string $periodEnd): float
    {
        $kosId = (int) $kos->id;

        return (float) Pembayaran::query()
            ->where('verification_status', Pembayaran::STATUS_APPROVED)
            ->whereBetween('payment_date', [$periodStart, $periodEnd])
            ->where(function ($q) use ($kosId) {
                $q->whereHas('penghuni.kos', fn ($qq) => $qq->whereKey($kosId))
                    ->orWhereHas('booking.kos', fn ($qq) => $qq->whereKey($kosId));
            })
            ->sum(DB::raw('COALESCE(rent_amount, amount)'));
    }

    public function resolveSettlement(Kos $kos, string $periodStart, string $periodEnd): array
    {
        return app(SettlementService::class)->resolveSettlementItems($kos, $periodStart, $periodEnd);
    }

    /**
     * Owner/super_admin menentukan Pemilik Kos Asli + rekening tujuan untuk kos.
     * Mengikat property owner ke kos dalam SATU transaksi.
     */
    public function setPropertyOwner(User $actor, Kos $kos, array $data): array
    {
        if (! $this->canManageKos($actor, $kos)) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        try {
            $result = DB::transaction(function () use ($kos, $data) {
                $propertyOwner = PropertyOwner::create([
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                ]);

                PropertyOwnerBankAccount::create([
                    'property_owner_id' => $propertyOwner->id,
                    'bank_name' => $data['bank_name'],
                    'account_number' => (string) $data['account_number'], // leading zero aman
                    'account_holder_name' => $data['account_holder_name'],
                ]);

                $kos->property_owner_id = $propertyOwner->id;
                $kos->save();

                return $propertyOwner;
            });
        } catch (\Exception $e) {
            \Log::warning('Set property owner failed: '.$e->getMessage());

            return ['ok' => false, 'error' => 'failed'];
        }

        try {
            AuditLogService::create(
                'Pemilik Kos Asli',
                'Pemilik kos asli ditentukan untuk payout',
                [
                    'kos_id' => $kos->id,
                    'property_owner_id' => $result->id,
                    'bank_name' => $data['bank_name'],
                    // Jangan log nomor rekening lengkap
                    'account_number_masked' => PropertyOwnerBankAccount::where('property_owner_id', $result->id)->first()?->maskedAccountNumber(),
                ]
            );
        } catch (\Exception $e) {
            \Log::warning('Property owner audit failed: '.$e->getMessage());
        }

        return ['ok' => true, 'property_owner' => $result];
    }

    /**
     * Owner/super_admin membuat payout untuk kos.
     * Request HANYA membawa: kos_id, period_start, period_end.
     * property_owner_id, owner_id, gross, fee, amount, status = server-side.
     */
    public function createForKos(User $actor, array $input): array
    {
        $kos = Kos::find($input['kos_id'] ?? null);

        if (! $kos) {
            return ['ok' => false, 'error' => 'kos_not_found'];
        }

        if (! $this->canManageKos($actor, $kos)) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        $periodStart = (string) ($input['period_start'] ?? '');
        $periodEnd = (string) ($input['period_end'] ?? '');

        if ($periodStart === '' || $periodEnd === '' || $periodEnd < $periodStart) {
            return ['ok' => false, 'error' => 'invalid_period'];
        }

        $propertyOwnerId = $kos->property_owner_id;

        if (! $propertyOwnerId) {
            return ['ok' => false, 'error' => 'property_owner_missing'];
        }

        if (! PropertyOwnerBankAccount::where('property_owner_id', $propertyOwnerId)->exists()) {
            return ['ok' => false, 'error' => 'bank_account_missing'];
        }

        try {
            $payout = DB::transaction(function () use ($actor, $kos, $periodStart, $periodEnd, $propertyOwnerId) {
                Kos::whereKey($kos->id)->lockForUpdate()->first();
                $duplicate = Payout::where('kos_id', $kos->id)
                    ->where('period_start', $periodStart)
                    ->where('period_end', $periodEnd)
                    ->exists();
                if ($duplicate) {
                    throw new \RuntimeException('duplicate_payout');
                }
                $settlement = app(SettlementService::class)->resolveSettlementItems($kos, $periodStart, $periodEnd);
                if (! empty($settlement['needs_agreement_ids'])) {
                    throw new \RuntimeException('needs_agreement');
                }
                if ($settlement['pembayaran_count'] === 0) {
                    throw new \RuntimeException('no_eligible_income');
                }
                $gross = $settlement['gross_total'];
                $penaltyExcluded = $settlement['penalty_excluded_total'];
                $commissionTotal = $settlement['commission_total'];
                $net = $settlement['net_total'];
                $platformFee = 0.0;
                $amount = round($net - $platformFee, 2);
                if ($amount <= 0 || $gross <= 0) {
                    throw new \RuntimeException('no_eligible_income');
                }
                if ($commissionTotal > $gross + 0.005) {
                    throw new \RuntimeException('no_eligible_income');
                }
                $payout = new Payout;
                $payout->payout_number = 'PO-'.strtoupper(Str::random(12));
                $payout->kos_id = $kos->id;
                $payout->owner_id = (int) $actor->id;
                $payout->property_owner_id = (int) $propertyOwnerId;
                $payout->gross_amount = $gross;
                $payout->platform_fee = $platformFee;
                $payout->commission_total = $commissionTotal;
                $payout->penalty_excluded_total = $penaltyExcluded;
                $payout->amount = $amount;
                $payout->period_start = $periodStart;
                $payout->period_end = $periodEnd;
                $payout->status = Payout::STATUS_PENDING;
                $payout->save();
                foreach ($settlement['items'] as $item) {
                    PayoutItem::create([
                        'payout_id' => $payout->id,
                        'pembayaran_id' => $item['pembayaran']->id,
                        'gross_rent' => $item['gross_rent'],
                        'penalty_excluded' => $item['penalty_excluded'],
                        'commission_type' => $item['commission_type'],
                        'commission_rate' => $item['commission_rate'],
                        'commission_amount' => $item['commission_amount'],
                        'net_amount' => $item['net_amount'],
                    ]);
                }

                return $payout;
            });
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        } catch (\Exception $e) {
            \Log::warning('Create payout failed: '.$e->getMessage());

            return ['ok' => false, 'error' => 'failed'];
        }

        try {
            AuditLogService::create(
                'Payout Pemilik',
                "Payout {$payout->payout_number} dibuat (menunggu transfer manual)",
                [
                    'payout_id' => $payout->id,
                    'kos_id' => $payout->kos_id,
                    'property_owner_id' => $payout->property_owner_id,
                    'gross_amount' => (float) $payout->gross_amount,
                    'commission_total' => (float) ($payout->commission_total ?? 0),
                    'penalty_excluded_total' => (float) ($payout->penalty_excluded_total ?? 0),
                    'amount' => (float) $payout->amount,
                    'period' => $periodStart.' s.d. '.$periodEnd,
                ]
            );
        } catch (\Exception $e) {
            \Log::warning('Payout audit failed: '.$e->getMessage());
        }

        return ['ok' => true, 'payout' => $payout];
    }

    public function recordDisbursement(User $actor, Payout $payout, array $data): array
    {
        if (! $this->canManageKos($actor, $payout->kos)) {
            return ['ok' => false, 'error' => 'forbidden'];
        }
        if (! in_array($payout->status, [Payout::STATUS_PENDING, Payout::STATUS_TRANSFERRED, Payout::STATUS_REJECTED], true)) {
            return ['ok' => false, 'error' => 'invalid_status'];
        }
        if ($payout->status === Payout::STATUS_VERIFIED) {
            return ['ok' => false, 'error' => 'already_verified'];
        }
        $amount = round((float) ($data['amount'] ?? 0), 2);
        if ($amount <= 0) {
            return ['ok' => false, 'error' => 'invalid_amount'];
        }
        $idempotencyKey = $data['idempotency_key'] ?? null;
        if (! $idempotencyKey || ! is_string($idempotencyKey) || strlen($idempotencyKey) < 8) {
            return ['ok' => false, 'error' => 'idempotency_key_required'];
        }
        $existing = PayoutDisbursement::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            $amountMatch = abs((float) $existing->amount - $amount) < 0.005;
            $payoutMatch = (int) $existing->payout_id === (int) $payout->id;
            $transferredAt = isset($data['transferred_at']) ? substr((string) $data['transferred_at'], 0, 10) : null;
            $existingDate = $existing->transferred_at ? $existing->transferred_at->format('Y-m-d') : null;
            $dateMatch = $transferredAt === null || $transferredAt === $existingDate;
            $notesMatch = $this->normalizeNotes($data['notes'] ?? null) === $this->normalizeNotes($existing->notes ?? null);
            if (! $amountMatch || ! $payoutMatch || ! $dateMatch || ! $notesMatch) {
                return ['ok' => false, 'error' => 'idempotency_conflict'];
            }

            return ['ok' => true, 'disbursement' => $existing, 'duplicate' => true];
        }
        $proofPath = null;
        $newProofStored = false;
        try {
            $disbursement = DB::transaction(function () use ($actor, $payout, $data, $amount, $idempotencyKey, &$proofPath, &$newProofStored) {
                Payout::whereKey($payout->id)->lockForUpdate()->first();
                $fresh = Payout::find($payout->id);
                if ($fresh->status === Payout::STATUS_VERIFIED) {
                    throw new \RuntimeException('already_verified');
                }
                $verified = (float) PayoutDisbursement::where('payout_id', $payout->id)->where('status', 'verified')->sum('amount');
                $pending = (float) PayoutDisbursement::where('payout_id', $payout->id)->where('status', 'pending')->sum('amount');
                if ($verified + $pending + $amount > (float) $payout->amount + 0.005) {
                    throw new \RuntimeException('overpayment');
                }
                $dup = PayoutDisbursement::where('idempotency_key', $idempotencyKey)->first();
                if ($dup) {
                    $dupAmountMatch = abs((float) $dup->amount - $amount) < 0.005;
                    $dupPayoutMatch = (int) $dup->payout_id === (int) $payout->id;
                    $dupDate = isset($data['transferred_at']) ? substr((string) $data['transferred_at'], 0, 10) : null;
                    $dupExistingDate = $dup->transferred_at ? $dup->transferred_at->format('Y-m-d') : null;
                    $dupDateMatch = $dupDate === null || $dupDate === $dupExistingDate;
                    $dupNotesMatch = $this->normalizeNotes($data['notes'] ?? null) === $this->normalizeNotes($dup->notes ?? null);
                    if (! $dupAmountMatch || ! $dupPayoutMatch || ! $dupDateMatch || ! $dupNotesMatch) {
                        throw new \RuntimeException('idempotency_conflict');
                    }

                    return $dup;
                }
                if (empty($data['proof_file'])) {
                    throw new \RuntimeException('proof_required');
                }
                $proofPath = $data['proof_file']->store('payout-proof', 'private');
                $newProofStored = true;
                if (! $proofPath || ! \Storage::disk('private')->exists($proofPath)) {
                    throw new \RuntimeException('proof_required');
                }

                return PayoutDisbursement::create([
                    'payout_id' => $payout->id,
                    'idempotency_key' => $idempotencyKey,
                    'amount' => $amount,
                    'status' => 'pending',
                    'proof_path' => $proofPath,
                    'transferred_at' => $data['transferred_at'] ?? now(),
                    'created_by' => $actor->id,
                    'notes' => $data['notes'] ?? null,
                ]);
            });
            if (isset($disbursement->id) && ($disbursement->wasRecentlyCreated ?? true) && empty($disbursement->exists ?? false) === false) {
            }
            if (isset($disbursement->id)) {
                $isDup = isset($disbursement->exists) && ! $disbursement->wasRecentlyCreated;
                if (! $isDup) {
                    try {
                        AuditLogService::create('Payout Disbursement', "Disbursement {$amount} dicatat untuk {$payout->payout_number}", ['payout_id' => $payout->id, 'disbursement_id' => $disbursement->id, 'amount' => $amount]);
                    } catch (\Throwable $e) {
                        \Log::warning('Disbursement audit failed: '.$e->getMessage());
                    }
                }
            }
        } catch (\RuntimeException $e) {
            if (! empty($newProofStored) && ! empty($proofPath)) {
                try {
                    \Storage::disk('private')->delete($proofPath);
                } catch (\Throwable $t) {
                }
            }

            return ['ok' => false, 'error' => $e->getMessage()];
        } catch (QueryException $e) {
            if (! empty($newProofStored) && ! empty($proofPath)) {
                try {
                    \Storage::disk('private')->delete($proofPath);
                } catch (\Throwable $t) {
                }
            }
            if (str_contains($e->getMessage(), 'idempotency') || $e->getCode() === '23000') {
                $dup = PayoutDisbursement::where('idempotency_key', $idempotencyKey)->first();
                if ($dup) {
                    $dupAmountMatch = abs((float) $dup->amount - $amount) < 0.005;
                    $dupPayoutMatch = (int) $dup->payout_id === (int) $payout->id;
                    $dupDate = isset($data['transferred_at']) ? substr((string) $data['transferred_at'], 0, 10) : null;
                    $dupExistingDate = $dup->transferred_at ? $dup->transferred_at->format('Y-m-d') : null;
                    $dupDateMatch = $dupDate === null || $dupDate === $dupExistingDate;
                    $dupNotesMatch = $this->normalizeNotes($data['notes'] ?? null) === $this->normalizeNotes($dup->notes ?? null);
                    if (! $dupAmountMatch || ! $dupPayoutMatch || ! $dupDateMatch || ! $dupNotesMatch) {
                        return ['ok' => false, 'error' => 'idempotency_conflict'];
                    }

                    return ['ok' => true, 'disbursement' => $dup, 'duplicate' => true];
                }
            }
            \Log::warning('Disbursement failed: '.$e->getMessage());

            return ['ok' => false, 'error' => 'failed'];
        } catch (\Exception $e) {
            if (! empty($newProofStored) && ! empty($proofPath)) {
                try {
                    \Storage::disk('private')->delete($proofPath);
                } catch (\Throwable $t) {
                }
            }
            \Log::warning('Disbursement failed: '.$e->getMessage());

            return ['ok' => false, 'error' => 'failed'];
        }

        return ['ok' => true, 'disbursement' => $disbursement];
    }

    public function verifyDisbursement(User $actor, PayoutDisbursement $disbursement): array
    {
        if (! $actor->isSuperAdmin()) {
            return ['ok' => false, 'error' => 'forbidden'];
        }
        if ($disbursement->status !== 'pending') {
            return ['ok' => false, 'error' => 'invalid_status'];
        }
        try {
            $disbursement = DB::transaction(function () use ($actor, $disbursement) {
                $locked = PayoutDisbursement::whereKey($disbursement->id)->lockForUpdate()->first();
                if ($locked->status !== 'pending') {
                    throw new \RuntimeException('invalid_status');
                }
                $locked->status = 'verified';
                $locked->verified_at = now();
                $locked->verified_by = $actor->id;
                $locked->save();
                $payout = $locked->payout;
                $verifiedSum = (float) PayoutDisbursement::where('payout_id', $payout->id)->where('status', 'verified')->sum('amount');
                $pendingSum = (float) PayoutDisbursement::where('payout_id', $payout->id)->where('status', 'pending')->sum('amount');
                if ($verifiedSum >= (float) $payout->amount - 0.005 && $pendingSum < 0.005) {
                    $payout->status = Payout::STATUS_VERIFIED;
                    $payout->verified_at = now();
                    $payout->verified_by = $actor->id;
                    $payout->save();
                } elseif ($locked->status === 'verified') {
                    if ($payout->status === Payout::STATUS_PENDING) {
                        $payout->status = Payout::STATUS_TRANSFERRED;
                        $payout->save();
                    }
                }

                return $locked;
            });
            AuditLogService::approve('Payout Disbursement', "Disbursement {$disbursement->id} diverifikasi", ['disbursement_id' => $disbursement->id, 'payout_id' => $disbursement->payout_id]);
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        } catch (\Exception $e) {
            \Log::warning('Verify disbursement failed: '.$e->getMessage());

            return ['ok' => false, 'error' => 'failed'];
        }

        return ['ok' => true, 'disbursement' => $disbursement];
    }

    public function rejectDisbursement(User $actor, PayoutDisbursement $disbursement, string $reason): array
    {
        if (! $actor->isSuperAdmin()) {
            return ['ok' => false, 'error' => 'forbidden'];
        }
        if ($disbursement->status !== 'pending') {
            return ['ok' => false, 'error' => 'invalid_status'];
        }
        try {
            DB::transaction(function () use ($disbursement, $reason) {
                $locked = PayoutDisbursement::whereKey($disbursement->id)->lockForUpdate()->first();
                if ($locked->status !== 'pending') {
                    throw new \RuntimeException('invalid_status');
                }
                $locked->status = 'rejected';
                $locked->notes = $reason;
                $locked->save();
            });
            AuditLogService::reject('Payout Disbursement', "Disbursement {$disbursement->id} ditolak", ['disbursement_id' => $disbursement->id, 'reason' => $reason]);
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        } catch (\Exception $e) {
            \Log::warning('Reject disbursement failed: '.$e->getMessage());

            return ['ok' => false, 'error' => 'failed'];
        }

        return ['ok' => true];
    }

    public function createAdjustment(User $actor, array $data): array
    {
        if (! $actor->isSuperAdmin()) {
            return ['ok' => false, 'error' => 'forbidden'];
        }
        $pembayaranId = $data['pembayaran_id'] ?? null;
        $payoutId = $data['payout_id'] ?? null;
        $type = $data['type'] ?? 'refund';
        $amount = round((float) ($data['amount'] ?? 0), 2);
        $reason = $data['reason'] ?? null;
        if (! $pembayaranId || $amount <= 0) {
            return ['ok' => false, 'error' => 'invalid_amount'];
        }
        if (! $reason || strlen(trim($reason)) < 5) {
            return ['ok' => false, 'error' => 'reason_required'];
        }
        $pembayaran = Pembayaran::find($pembayaranId);
        if (! $pembayaran) {
            return ['ok' => false, 'error' => 'pembayaran_not_found'];
        }
        $payout = $payoutId ? Payout::find($payoutId) : PayoutItem::where('pembayaran_id', $pembayaranId)->first()?->payout;
        $pembayaran->loadMissing(['penghuni.kos', 'booking.kos']);
        $derivedKos = null;
        if ($pembayaran->penghuni?->kos) {
            $derivedKos = $pembayaran->penghuni->kos;
        } elseif ($pembayaran->booking?->kos) {
            $derivedKos = $pembayaran->booking->kos;
        }
        if ($payout) {
            if (! $this->canManageKos($actor, $payout->kos)) {
                return ['ok' => false, 'error' => 'forbidden'];
            }
            if (! $derivedKos || (int) $payout->kos_id !== (int) $derivedKos->id) {
                return ['ok' => false, 'error' => 'kos_mismatch'];
            }
        } else {
            if (! $derivedKos || ! $this->canManageKos($actor, $derivedKos)) {
                return ['ok' => false, 'error' => 'forbidden'];
            }
        }
        try {
            $adjustment = DB::transaction(function () use ($actor, $pembayaran, $payout, $type, $amount, $reason, $data) {
                if ($payout) {
                    Payout::whereKey($payout->id)->lockForUpdate()->first();
                }
                $item = $payout ? PayoutItem::where('payout_id', $payout->id)->where('pembayaran_id', $pembayaran->id)->first() : null;
                $totalGrossFromItems = (float) PayoutItem::where('pembayaran_id', $pembayaran->id)->sum('gross_rent');
                $grossRent = $item ? (float) $item->gross_rent : (float) ($pembayaran->rent_amount ?? $pembayaran->amount);
                if ($totalGrossFromItems > 0) {
                    $grossRent = $totalGrossFromItems;
                }
                $existingRefunded = (float) PayoutAdjustment::where('pembayaran_id', $pembayaran->id)->sum('amount');
                if ($existingRefunded + $amount > $grossRent + 0.005) {
                    throw new \RuntimeException('refund_exceeds_gross');
                }
                $commissionDelta = 0.0;
                if ($item) {
                    if ($item->commission_type === 'percent') {
                        $rate = (float) $item->commission_rate;
                        $commissionDelta = round($amount * $rate / 100, 2);
                    } else {
                        $commissionDelta = 0.0;
                    }
                } else {
                    $kos = null;
                    if ($pembayaran->penghuni) {
                        $kos = $pembayaran->penghuni->kos;
                    } elseif ($pembayaran->booking) {
                        $kos = $pembayaran->booking->kos;
                    }
                    if ($kos) {
                        $resolved = app(SettlementService::class)->resolveCommissionForPembayaran($kos, $pembayaran);
                        if ($resolved && $resolved['commission_type'] === 'percent') {
                            $commissionDelta = round($amount * (float) $resolved['commission_rate'] / 100, 2);
                        }
                    }
                }
                if (isset($data['commission_delta']) && $actor->isSuperAdmin()) {
                    $commissionDelta = round((float) $data['commission_delta'], 2);
                }
                if ($commissionDelta > $amount + 0.005) {
                    throw new \RuntimeException('commission_delta_exceeds_amount');
                }

                return PayoutAdjustment::create([
                    'payout_id' => $payout?->id,
                    'pembayaran_id' => $pembayaran->id,
                    'type' => $type,
                    'amount' => $amount,
                    'commission_delta' => $commissionDelta,
                    'reason' => $reason,
                    'created_by' => $actor->id,
                ]);
            });
            AuditLogService::create('Payout Adjustment', "Adjustment {$type} {$amount} untuk pembayaran {$pembayaran->id}", ['adjustment_id' => $adjustment->id, 'pembayaran_id' => $pembayaran->id, 'payout_id' => $payout?->id]);
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        } catch (\Exception $e) {
            \Log::warning('Create adjustment failed: '.$e->getMessage());

            return ['ok' => false, 'error' => 'failed'];
        }

        return ['ok' => true, 'adjustment' => $adjustment];
    }

    /**
     * Owner mengonfirmasi transfer manual + upload bukti.
     * Status: pending → transferred; rejected → transferred (resubmit record SAMA).
     */
    public function markTransferred(User $actor, Payout $payout, array $data): array
    {
        if (! $this->canManageKos($actor, $payout->kos)) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        if (! in_array($payout->status, Payout::transferableStatuses(), true)) {
            return ['ok' => false, 'error' => 'invalid_status'];
        }

        $proofPath = null;
        if (! empty($data['proof_file'])) {
            $proofPath = $data['proof_file']->store('payout-proof', 'private');
        } elseif ($payout->proof_path) {
            $proofPath = $payout->proof_path;
        }

        if (! $proofPath) {
            if (! empty($data['proof_file']) && $proofPath) {
                \Storage::disk('private')->delete($proofPath);
            }

            return ['ok' => false, 'error' => 'proof_required'];
        }

        $newProofStored = ! empty($data['proof_file']);
        try {
            DB::transaction(function () use ($payout, $proofPath, $data) {
                $locked = Payout::whereKey($payout->id)->lockForUpdate()->first();
                if (! in_array($locked->status, Payout::transferableStatuses(), true)) {
                    throw new \RuntimeException('invalid_status');
                }
                $locked->status = Payout::STATUS_TRANSFERRED;
                $locked->transferred_at = $data['transferred_at'];
                $locked->proof_path = $proofPath;
                $locked->notes = $data['notes'] ?? $locked->notes;
                $locked->verified_at = null;
                $locked->verified_by = null;
                $locked->save();
                $payout->setRawAttributes($locked->getAttributes(), true);
            });
        } catch (\RuntimeException $e) {
            if ($newProofStored && ! empty($proofPath)) {
                try {
                    \Storage::disk('private')->delete($proofPath);
                } catch (\Throwable $t) {
                }
            }

            return ['ok' => false, 'error' => $e->getMessage()];
        } catch (\Exception $e) {
            if ($newProofStored && ! empty($proofPath)) {
                try {
                    \Storage::disk('private')->delete($proofPath);
                } catch (\Throwable $t) {
                }
            }
            \Log::warning('Mark payout transferred failed: '.$e->getMessage());

            return ['ok' => false, 'error' => 'failed'];
        }

        try {
            AuditLogService::update(
                'Payout Pemilik',
                "Payout {$payout->payout_number} ditandai sudah ditransfer (bukti diunggah)",
                [
                    'payout_id' => $payout->id,
                    'kos_id' => $payout->kos_id,
                    'amount' => (float) $payout->amount,
                    'transferred_at' => (string) $payout->transferred_at,
                ]
            );
        } catch (\Exception $e) {
            \Log::warning('Payout transfer audit failed: '.$e->getMessage());
        }

        return ['ok' => true, 'payout' => $payout];
    }

    /**
     * Super Admin memverifikasi payout → verified.
     * Owner TIDAK boleh self-verify.
     */
    public function verify(User $actor, Payout $payout): array
    {
        if (! $actor->isSuperAdmin()) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        if ($payout->status !== Payout::STATUS_TRANSFERRED) {
            return ['ok' => false, 'error' => 'invalid_status'];
        }

        try {
            DB::transaction(function () use ($actor, $payout) {
                $locked = Payout::whereKey($payout->id)->lockForUpdate()->first();
                if ($locked->status !== Payout::STATUS_TRANSFERRED) {
                    throw new \RuntimeException('invalid_status');
                }
                $locked->status = Payout::STATUS_VERIFIED;
                $locked->verified_at = now();
                $locked->verified_by = $actor->id;
                $locked->save();
                $payout->setRawAttributes($locked->getAttributes(), true);
            });
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        } catch (\Exception $e) {
            \Log::warning('Verify payout failed: '.$e->getMessage());

            return ['ok' => false, 'error' => 'failed'];
        }

        try {
            AuditLogService::approve(
                'Payout Pemilik',
                "Payout {$payout->payout_number} diverifikasi Super Admin",
                [
                    'payout_id' => $payout->id,
                    'kos_id' => $payout->kos_id,
                    'amount' => (float) $payout->amount,
                ]
            );
        } catch (\Exception $e) {
            \Log::warning('Payout verify audit failed: '.$e->getMessage());
        }

        return ['ok' => true, 'payout' => $payout];
    }

    /**
     * Super Admin menolak payout → rejected.
     * Owner kemudian boleh resubmit record yang SAMA (upload bukti baru).
     */
    public function reject(User $actor, Payout $payout, string $reason): array
    {
        if (! $actor->isSuperAdmin()) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        if ($payout->status !== Payout::STATUS_TRANSFERRED) {
            return ['ok' => false, 'error' => 'invalid_status'];
        }

        try {
            DB::transaction(function () use ($actor, $payout, $reason) {
                $locked = Payout::whereKey($payout->id)->lockForUpdate()->first();
                if ($locked->status !== Payout::STATUS_TRANSFERRED) {
                    throw new \RuntimeException('invalid_status');
                }
                $locked->status = Payout::STATUS_REJECTED;
                $locked->verified_at = now();
                $locked->verified_by = $actor->id;
                $locked->notes = $reason;
                $locked->save();
                $payout->setRawAttributes($locked->getAttributes(), true);
            });
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        } catch (\Exception $e) {
            \Log::warning('Reject payout failed: '.$e->getMessage());

            return ['ok' => false, 'error' => 'failed'];
        }

        try {
            AuditLogService::reject(
                'Payout Pemilik',
                "Payout {$payout->payout_number} ditolak Super Admin",
                [
                    'payout_id' => $payout->id,
                    'kos_id' => $payout->kos_id,
                    'reason' => $reason,
                ]
            );
        } catch (\Exception $e) {
            \Log::warning('Payout reject audit failed: '.$e->getMessage());
        }

        return ['ok' => true, 'payout' => $payout];
    }

    private function normalizeNotes(?string $notes): string
    {
        if ($notes === null) {
            return '';
        }
        $trimmed = trim($notes);
        if ($trimmed === '') {
            return '';
        }

        return strtolower(preg_replace('/\s+/', ' ', $trimmed));
    }

    /**
     * Scope: owner hanya kos miliknya; super_admin semua.
     */
    private function canManageKos(User $actor, ?Kos $kos): bool
    {
        if (! $kos) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return true;
        }

        return $actor->isOwner() && (int) $kos->owner_id === (int) $actor->id;
    }
}
