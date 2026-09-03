<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagihanRequest;
use App\Models\Kontrak;
use App\Models\Tagihan;
use App\Services\AuditLogService;
use App\Services\NotificationService;
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
            ->with(['penghuni.user', 'kamar'])
            ->get();

        return view('owner.tagihan.create', compact('kontraks'));
    }

    public function store(StoreTagihanRequest $request)
    {
        $user = $request->user();
        $kontrak = Kontrak::with('penghuni', 'kamar')->findOrFail($request->kontrak_id);

        abort_unless($user->canAccessKos((int) $kontrak->kos_id), 403);
        abort_unless($kontrak->status === 'active', 400, 'Kontrak tidak aktif.');

        $periodStart = Carbon::parse($request->period_start);
        $periodEnd = Carbon::parse($request->period_end);

        // Subtotal selalu dihitung dari kontrak (PRD §9), bukan dari input client.
        if ($kontrak->rental_type === 'daily') {
            $units = (int) $periodStart->copy()->startOfDay()->diffInDays($periodEnd->copy()->startOfDay()) + 1;
        } else {
            $units = $this->ceilingMonths($periodStart, $periodEnd);
        }
        $subtotal = round((float) $kontrak->rental_price * $units, 2);

        $discount = (float) ($request->discount ?? 0);
        $penalty = (float) ($request->penalty ?? 0);

        if ($discount > $subtotal) {
            return back()->withErrors([
                'discount' => 'Diskon tidak boleh melebihi subtotal tagihan (Rp '.number_format($subtotal, 0, ',', '.').').',
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
