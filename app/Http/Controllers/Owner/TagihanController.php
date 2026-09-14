<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagihanRequest;
use App\Models\Kontrak;
use App\Models\Tagihan;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\SharedBillingService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Tagihan::with(['penghuni.user', 'kontrak', 'kamar.kos']);

        if ($user->isOwner()) {
            $query->whereHas('kamar.kos', fn ($q) => $q->where('owner_id', $user->id));
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereHas('kamar', fn ($q) => $q->whereIn('kos_id', $kosIds));
        } elseif ($user->isTenant()) {
            $query->whereHas('penghuni', fn ($q) => $q->where('user_id', $user->id));
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tagihans = $query->latest()->paginate(10)->withQueryString();

        return view($user->isTenant() ? 'tenant.tagihan.index' : 'owner.tagihan.index', compact('tagihans'));
    }

    public function show(Tagihan $tagihan)
    {
        $this->authorize('view', $tagihan);
        $tagihan->load(['penghuni.user', 'kontrak', 'kamar.kos', 'pembayarans']);
        $user = request()->user();

        return view($user->isTenant() ? 'tenant.tagihan.show' : 'owner.tagihan.show', compact('tagihan'));
    }

    public function pay(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isTenant(), 403);

        $tagihans = Tagihan::with(['penghuni.user', 'kontrak', 'kamar.kos'])
            ->whereHas('penghuni', fn ($q) => $q->where('user_id', $user->id))
            ->whereIn('status', Tagihan::PAYABLE)
            ->latest()
            ->get();

        return view('tenant.tagihan.pay', compact('tagihans'));
    }

    public function create(Request $request)
    {
        $user = $request->user();

        $kontraks = Kontrak::where('status', 'active')
            ->when($user->isOwner(), function ($q) use ($user) {
                $q->whereHas('kos', fn ($kq) => $kq->where('owner_id', $user->id));
            })
            ->when($user->isAdmin(), function ($q) use ($user) {
                $kosIds = $user->assignedKos()->pluck('kos.id');
                $q->whereHas('kamar', fn ($kq) => $kq->whereIn('kos_id', $kosIds));
            })
            ->with(['penghuni.user', 'kamar.kos'])
            ->get();

        return view('owner.tagihan.create', compact('kontraks'));
    }

    public function store(StoreTagihanRequest $request, SharedBillingService $sharedBilling)
    {
        $user = $request->user();
        $kontrak = Kontrak::with(['penghuni.user', 'kamar'])->findOrFail($request->kontrak_id);

        abort_unless($user->canAccessKos((int) $kontrak->kos_id), 403);
        abort_unless($kontrak->status === 'active', 400, 'Kontrak tidak aktif.');

        $periodStart = Carbon::parse($request->period_start);
        $periodEnd = Carbon::parse($request->period_end);
        $discount = (float) ($request->discount ?? 0);
        $penalty = (float) ($request->penalty ?? 0);

        // ── Shared billing branch ───────────────────────────────────────
        // Kontrak is source of truth for billing_mode (snapshot from booking).
        $effectiveMode = $kontrak->billing_mode ?? $kontrak->kamar->billing_mode ?? 'personal';
        if ($effectiveMode === 'shared') {
            // Validate custom shares balance BEFORE creating any bills
            $kamar = $kontrak->kamar;
            // total_room_bill snapshot: price × units using existing weekly/daily/monthly logic
            $totalRoomBill = $sharedBilling->calculateTotalRoomBill($kamar, $kontrak->rental_type, $periodStart, $periodEnd);

            // If all active shared occupants have custom_share_amount, validate SUM == total
            $activeShared = $kamar->activeKontraks()->get()->filter(fn ($k) => ($k->billing_mode ?? $k->kamar->billing_mode ?? 'personal') === 'shared');
            // Fallback: if Kontrak billing_mode snapshot is null but kamar shared, use activeKontraks count
            if ($activeShared->isEmpty()) {
                $activeShared = $kamar->activeKontraks()->get();
            }
            $customShares = $activeShared->filter(fn ($k) => $k->custom_share_amount !== null);
            if ($customShares->isNotEmpty() && $customShares->count() === $activeShared->count()) {
                $sharesForValidation = $customShares->map(fn ($k) => ['kontrak_id' => $k->id, 'share' => (float) $k->custom_share_amount])->toArray();
                if (! $sharedBilling->validateCustomShares($totalRoomBill, $sharesForValidation)) {
                    return back()->withErrors([
                        'kontrak_id' => 'Jumlah custom share (Rp '.number_format(array_sum(array_column($sharesForValidation, 'share')), 0, ',', '.').') tidak balance dengan total tagihan kamar (Rp '.number_format($totalRoomBill, 0, ',', '.').').',
                    ])->withInput();
                }
            }

            // Also reject if partial custom sum already exceeds total
            if ($customShares->isNotEmpty()) {
                $customTotal = $customShares->sum(fn ($k) => (float) $k->custom_share_amount);
                if ($customTotal > $totalRoomBill + 0.005) {
                    return back()->withErrors([
                        'kontrak_id' => 'Jumlah custom share melebihi total tagihan kamar (Rp '.number_format($totalRoomBill, 0, ',', '.').').',
                    ])->withInput();
                }
            }

            // Use SharedBillingService as single source of truth for split/rounding/invariant/duplicate guard
            $created = $sharedBilling->createSharedTagihans(
                $kamar,
                $request->bill_type,
                $request->period_start,
                $request->period_end,
                $request->due_date,
                $discount,
                $penalty,
            );

            if (empty($created)) {
                // Determine if duplicate vs no occupants
                $exists = Tagihan::where('kontrak_id', $kontrak->id)
                    ->where('period_start', $request->period_start)
                    ->where('period_end', $request->period_end)
                    ->exists();
                if ($exists) {
                    return back()->withErrors(['kontrak_id' => 'Tagihan untuk periode tersebut sudah ada.'])->withInput();
                }

                return back()->withErrors(['kontrak_id' => 'Tidak ada penghuni aktif untuk shared billing pada kamar ini.'])->withInput();
            }

            try {
                foreach ($created as $tag) {
                    $tag->loadMissing('kontrak.penghuni.user');
                    NotificationService::billCreated($tag->kontrak->penghuni->user_id, $tag->bill_number, $request->due_date);
                    AuditLogService::create('Tagihan', "Tagihan shared {$tag->bill_number} dibuat (total_room_bill Rp ".number_format((float) $tag->total_room_bill, 0, ',', '.').')', ['kontrak_id' => $tag->kontrak_id]);
                }
            } catch (\Exception $e) {
                \Log::warning('Tagihan shared notification/audit failed: '.$e->getMessage());
            }

            $prefix = $user->isTenant() ? 'tenant' : ($user->isAdmin() ? 'admin' : 'owner');

            return redirect()->route("$prefix.tagihan.index")->with('success', 'Tagihan shared berhasil dibuat untuk '.count($created).' penghuni.');
        }

        // ── Personal flow (regression-safe) ─────────────────────────────
        // Subtotal selalu dihitung dari kontrak (PRD §9), bukan dari input client.
        if ($kontrak->rental_type === 'daily') {
            $units = (int) $periodStart->copy()->startOfDay()->diffInDays($periodEnd->copy()->startOfDay()) + 1;
        } elseif ($kontrak->rental_type === 'weekly') {
            $days = (int) $periodStart->copy()->startOfDay()->diffInDays($periodEnd->copy()->startOfDay()) + 1;
            $units = (int) ceil($days / 7);
        } else {
            $units = $this->ceilingMonths($periodStart, $periodEnd);
        }
        $subtotal = round((float) $kontrak->rental_price * $units, 2);

        if ($discount > $subtotal) {
            return back()->withErrors([
                'discount' => 'Diskon tidak boleh melebihi subtotal tagihan (Rp '.number_format($subtotal, 0, ',', '.').').',
            ])->withInput();
        }

        if ($penalty > $subtotal) {
            return back()->withErrors([
                'penalty' => 'Denda tidak boleh melebihi subtotal tagihan (Rp '.number_format($subtotal, 0, ',', '.').').',
            ])->withInput();
        }

        $total = max(0, round($subtotal - $discount + $penalty, 2));
        $billNumber = 'TB-'.strtoupper(Str::random(12));
        $duplicate = false;
        $expired = false;

        \DB::transaction(function () use ($kontrak, $request, $subtotal, $discount, $penalty, $total, $billNumber, &$duplicate, &$expired) {
            $lockedKontrak = Kontrak::whereKey($kontrak->id)->lockForUpdate()->first();

            if (! $lockedKontrak || $lockedKontrak->status !== 'active') {
                $expired = true;

                return;
            }

            $duplicate = Tagihan::where('kontrak_id', $kontrak->id)
                ->where('period_start', $request->period_start)
                ->where('period_end', $request->period_end)
                ->exists();

            if ($duplicate) {
                return;
            }

            try {
                Tagihan::create([
                    'bill_number' => $billNumber,
                    'penghuni_id' => $kontrak->penghuni_id,
                    'kontrak_id' => $kontrak->id,
                    'kamar_id' => $kontrak->kamar_id,
                    'bill_type' => $request->bill_type,
                    'period_start' => $request->period_start,
                    'period_end' => $request->period_end,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'penalty' => $penalty,
                    'total' => $total,
                    'due_date' => $request->due_date,
                    'status' => 'unpaid',
                    'active_billing_key' => $this->billingKey($kontrak->id, $request->period_start, $request->period_end),
                ]);
            } catch (QueryException $e) {
                // Backstop database-level: jika ada race yang lolos dari cek exists()
                // di atas, unique(active_billing_key) menolak baris duplikat.
                $duplicate = true;
            }
        });

        if ($expired) {
            return back()->withErrors(['kontrak_id' => 'Kontrak tidak aktif. Tagihan tidak dapat dibuat.'])->withInput();
        }

        if ($duplicate) {
            return back()->withErrors(['kontrak_id' => 'Tagihan untuk periode tersebut sudah ada.'])->withInput();
        }

        try {
            NotificationService::billCreated($kontrak->penghuni->user_id, $billNumber, $request->due_date);
            AuditLogService::create('Tagihan', "Tagihan baru {$billNumber} dibuat untuk {$kontrak->penghuni->user->name}", ['kontrak_id' => $kontrak->id]);
        } catch (\Exception $e) {
            \Log::warning('Tagihan notification/audit failed: '.$e->getMessage());
        }

        $prefix = $user->isTenant() ? 'tenant' : ($user->isAdmin() ? 'admin' : 'owner');

        return redirect()->route("$prefix.tagihan.index")->with('success', 'Tagihan berhasil dibuat.');
    }

    /**
     * Hitung jumlah bulan yang harus ditagih (ceiling months): setiap bulan
     * kalender yang tersentuh oleh periode dihitung satu bulan penuh, tanpa
     * pro-rata. Menggunakan aritmetika kalender agar hasil deterministik dan
     * konsisten (tidak terpengaruh clamping/overflow tanggal akhir bulan pada
     * Carbon::addMonth) serta selaras dengan preview klien dan rule bisnis.
     */
    private function ceilingMonths(Carbon $start, Carbon $end): int
    {
        $months = ($end->year - $start->year) * 12 + ($end->month - $start->month) + 1;

        return max($months, 1);
    }

    /**
     * Kunci deterministik untuk satu tagihan aktif: kombinasi kontrak + periode.
     * Di-per-se-kan ke kolom unik `active_billing_key` untuk mencegah duplikasi
     * di level database (race-safe), lalu dikosongkan saat soft-delete.
     */
    private function billingKey(int $kontrakId, string $periodStart, string $periodEnd): string
    {
        return $kontrakId.':'.$periodStart.':'.$periodEnd;
    }
}
