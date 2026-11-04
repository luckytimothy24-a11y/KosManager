<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectPayoutRequest;
use App\Http\Requests\SetPropertyOwnerRequest;
use App\Http\Requests\StorePayoutRequest;
use App\Http\Requests\TransferPayoutRequest;
use App\Models\Kos;
use App\Models\Payout;
use App\Services\AuditLogService;
use App\Services\PropertyOwnerPayoutService;
use App\Services\SettlementReportService;
use Illuminate\Http\Request;

/**
 * Payout/Settlement ke Pemilik Kos Asli.
 *
 * Controller HANYA authorize + delegasi ke PropertyOwnerPayoutService
 * (source of truth untuk nominal, scope, duplicate, status transition).
 *
 * Transfer bank dilakukan Owner di luar aplikasi. KosManager hanya mencatat.
 */
class PropertyOwnerPayoutController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Payout::class);

        $user = $request->user();

        $query = Payout::with(['kos', 'propertyOwner', 'propertyOwnerBankAccount', 'verifier']);

        if ($user->isOwner()) {
            $query->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payouts = $query->latest()->paginate(10)->withQueryString();

        // Kos milik actor yang belum punya pemilik asli (warning + CTA set).
        $kosWithoutPropertyOwner = collect();
        if ($user->isOwner()) {
            $kosWithoutPropertyOwner = Kos::where('owner_id', $user->id)
                ->whereNull('property_owner_id')
                ->where('status', 'active')
                ->get(['id', 'name']);
        } elseif ($user->isSuperAdmin()) {
            $kosWithoutPropertyOwner = Kos::whereNull('property_owner_id')
                ->where('status', 'active')
                ->get(['id', 'name']);
        }

        $kosOptions = $user->isOwner()
            ? Kos::where('owner_id', $user->id)->where('status', 'active')->orderBy('name')->get(['id', 'name', 'property_owner_id'])
            : Kos::where('status', 'active')->orderBy('name')->get(['id', 'name', 'property_owner_id']);

        $settlement = null;
        if ($user->isOwner()) {
            $settlement = app(SettlementReportService::class)->forOwner($user);
        }

        return view('owner.payouts.index', compact('payouts', 'kosWithoutPropertyOwner', 'kosOptions', 'settlement'));
    }

    public function show(Payout $payout)
    {
        $this->authorize('view', $payout);

        $payout->load(['kos', 'propertyOwner', 'propertyOwnerBankAccount', 'verifier', 'items.pembayaran', 'disbursements.creator', 'adjustments.pembayaran', 'adjustments.creator']);

        return view('owner.payouts.show', compact('payout'));
    }

    /**
     * Reveal nomor rekening penerima lengkap untuk Owner/Super Admin.
     *
     * Nomor lengkap tidak dimasukkan ke HTML awal (tetap masked); endpoint ini
     * hanya dipanggil saat pengguna meminta melihat, dan setiap akses dicatat
     * di audit log tanpa menyimpan nomor rekening.
     */
    public function revealRecipientAccount(Payout $payout)
    {
        $this->authorize('view', $payout);

        $payout->loadMissing('propertyOwnerBankAccount');

        $source = null;
        $bankName = null;
        $accountNumber = null;
        $accountHolderName = null;

        if ($payout->hasRecipientSnapshot()) {
            $source = 'snapshot';
            $bankName = $payout->recipient_bank_name;
            $accountNumber = $payout->recipient_account_number;
            $accountHolderName = $payout->recipient_account_holder_name;
        } elseif ($payout->propertyOwnerBankAccount) {
            // Legacy payout: snapshot historis tidak tersedia. Nomor yang
            // ditampilkan adalah rekening profil saat ini.
            $source = 'legacy';
            $bankName = $payout->propertyOwnerBankAccount->bank_name;
            $accountNumber = $payout->propertyOwnerBankAccount->account_number;
            $accountHolderName = $payout->propertyOwnerBankAccount->account_holder_name;
        }

        if ($accountNumber === null || $accountNumber === '') {
            return response()->json([
                'message' => 'Rekening tujuan belum terdaftar.',
            ], 404);
        }

        try {
            AuditLogService::log(
                'View',
                'Payout',
                'Nomor rekening penerima payout dilihat',
                data: [
                    'payout_id' => $payout->id,
                    'kos_id' => $payout->kos_id,
                    'property_owner_id' => $payout->property_owner_id,
                    'source' => $source,
                ]
            );
        } catch (\Throwable $e) {
            \Log::warning('Payout recipient account reveal audit failed: '.$e->getMessage());
        }

        return response()->json([
            'bank_name' => $bankName,
            'account_number' => (string) $accountNumber,
            'account_holder_name' => $accountHolderName,
            'source' => $source,
            'legacy' => $source === 'legacy',
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
    }

    public function store(StorePayoutRequest $request, PropertyOwnerPayoutService $service)
    {
        $this->authorize('create', Payout::class);

        $result = $service->createForKos($request->user(), $request->validated());

        if (! ($result['ok'] ?? false)) {
            $error = match ($result['error'] ?? '') {
                'kos_not_found' => 'Kos tidak ditemukan.',
                'forbidden' => 'Anda tidak memiliki akses untuk kos ini.',
                'invalid_period' => 'Periode tidak valid — akhir periode harus setelah atau sama dengan awal periode.',
                'property_owner_missing' => 'Tentukan Pemilik Kos Asli untuk kos ini terlebih dahulu.',
                'bank_account_missing' => 'Pemilik Kos Asli belum memiliki rekening tujuan.',
                'duplicate_payout' => 'Payout untuk kos dan periode ini sudah ada.',
                'needs_agreement' => 'Perjanjian komisi belum tersedia untuk sebagian pembayaran pada periode ini. Lengkapi perjanjian sebelum membuat payout.',
                'no_eligible_income' => 'Tidak ada pendapatan eligible (pembayaran approved) pada periode ini.',
                default => 'Gagal membuat payout.',
            };

            return back()->withInput()->with('error', $error);
        }

        return redirect()
            ->route('owner.payouts.show', $result['payout'])
            ->with('success', 'Payout dibuat. Silakan transfer manual ke rekening Pemilik Kos Asli, lalu unggah bukti transfer.');
    }

    public function markTransferred(Payout $payout, TransferPayoutRequest $request, PropertyOwnerPayoutService $service)
    {
        $this->authorize('update', $payout);

        $data = $request->validated();
        $result = $service->markTransferred($request->user(), $payout, $data);

        if (! ($result['ok'] ?? false)) {
            $error = match ($result['error'] ?? '') {
                'forbidden' => 'Anda tidak memiliki akses untuk payout ini.',
                'invalid_status' => 'Status payout tidak memungkinkan konfirmasi transfer.',
                'proof_required' => 'Bukti transfer wajib diunggah.',
                default => 'Gagal mengonfirmasi transfer.',
            };

            return back()->with('error', $error);
        }

        return redirect()
            ->route('owner.payouts.show', $payout)
            ->with('success', 'Transfer dikonfirmasi. Menunggu verifikasi Super Admin.');
    }

    public function verify(Payout $payout, PropertyOwnerPayoutService $service)
    {
        $this->authorize('verify', $payout);

        $result = $service->verify(request()->user(), $payout);

        if (! ($result['ok'] ?? false)) {
            $error = match ($result['error'] ?? '') {
                'forbidden' => 'Hanya Super Admin yang dapat memverifikasi payout.',
                'invalid_status' => 'Payout harus berstatus menunggu verifikasi (transferred).',
                'disbursements_require_item_verification' => 'Payout ini memiliki pencairan parsial. Verifikasi disbursement terlebih dahulu; payout induk akan otomatis terverifikasi setelah seluruh disbursement verified dan totalnya sesuai.',
                default => 'Gagal memverifikasi payout.',
            };

            return back()->with('error', $error);
        }

        return redirect()
            ->route('owner.payouts.show', $payout)
            ->with('success', 'Payout diverifikasi.');
    }

    public function reject(Payout $payout, RejectPayoutRequest $request, PropertyOwnerPayoutService $service)
    {
        $this->authorize('reject', $payout);

        $result = $service->reject($request->user(), $payout, $request->validated('reason'));

        if (! ($result['ok'] ?? false)) {
            $error = match ($result['error'] ?? '') {
                'forbidden' => 'Hanya Super Admin yang dapat menolak payout.',
                'invalid_status' => 'Payout harus berstatus menunggu verifikasi (transferred).',
                default => 'Gagal menolak payout.',
            };

            return back()->with('error', $error);
        }

        return redirect()
            ->route('owner.payouts.show', $payout)
            ->with('success', 'Payout ditolak. Owner dapat memperbaiki dan mengunggah bukti baru.');
    }

    public function downloadProof(Payout $payout)
    {
        $this->authorize('downloadProof', $payout);

        abort_unless(
            $payout->proof_path && \Storage::disk('private')->exists($payout->proof_path),
            404
        );

        return \Storage::disk('private')->download($payout->proof_path);
    }

    /**
     * Owner/super_admin mengikat Pemilik Kos Asli + rekening ke kos
     * dalam satu transaksi (via service).
     */
    public function setPropertyOwner(Kos $kos, SetPropertyOwnerRequest $request, PropertyOwnerPayoutService $service)
    {
        $result = $service->setPropertyOwner($request->user(), $kos, $request->validated());

        if (! ($result['ok'] ?? false)) {
            $error = match ($result['error'] ?? '') {
                'forbidden' => 'Anda tidak memiliki akses untuk kos ini.',
                default => 'Gagal menentukan pemilik kos asli.',
            };

            return back()->with('error', $error);
        }

        return redirect()
            ->route('owner.payouts.index')
            ->with('success', "Pemilik Kos Asli ditentukan untuk {$kos->name}.");
    }
}
