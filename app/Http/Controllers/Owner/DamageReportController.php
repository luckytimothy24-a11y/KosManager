<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\DamageReport;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DamageReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Tenant: daftar laporan miliknya sendiri (bukan viewAny global).
        if ($user->isTenant()) {
            $reports = DamageReport::with(['kos', 'kamar'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10);

            return view('tenant.damage-reports.index', compact('reports'));
        }

        $this->authorize('viewAny', DamageReport::class);

        $kosIds = $request->user()->isSuperAdmin()
            ? null
            : ($request->user()->isAdmin()
                ? $request->user()->assignedKos()->pluck('kos.id')->all()
                : Kos::where('owner_id', $request->user()->id)->pluck('id')->all());

        $query = DamageReport::with(['user', 'kos', 'kamar', 'owner'])
            ->when($kosIds !== null, fn ($q) => $q->whereIn('kos_id', $kosIds))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest();

        $reports = $query->paginate(20);

        return view('owner.damage-reports.index', compact('reports', 'kosIds'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', DamageReport::class);

        $user = $request->user();

        $stays = Penghuni::where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['kos', 'kamar'])
            ->get();

        return view('tenant.damage-reports.create', compact('stays'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', DamageReport::class);

        $validated = $request->validate([
            'kos_id' => ['required', 'integer', 'exists:kos,id'],
            'kamar_id' => ['nullable', 'integer', 'exists:kamar,id'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        // Tenant hanya boleh melapor untuk kos tempat ia berpenghuni aktif.
        $penghuni = Penghuni::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('kos_id', (int) $validated['kos_id'])
            ->first();

        abort_unless($penghuni, 403, 'Anda tidak memiliki hunian aktif di kos ini.');

        // Jika kamar diisi, pastikan kamar tersebut milik kos yang sama
        // dan terkait dengan hunian tenant (bukan kamar acak / kos lain).
        $kamarId = null;
        if (! empty($validated['kamar_id'])) {
            $kamar = Kamar::whereKey((int) $validated['kamar_id'])->first();
            abort_unless($kamar && (int) $kamar->kos_id === (int) $validated['kos_id'], 403, 'Kamar tidak terkait dengan kos ini.');
            abort_unless((int) $penghuni->kamar_id === (int) $kamar->id, 403, 'Kamar tidak terkait dengan hunian Anda.');
            $kamarId = (int) $kamar->id;
        } else {
            $kamarId = $penghuni->kamar_id;
        }

        // Server-side assignment: user_id, status, dan owner_id tidak pernah
        // diambil dari input client (cegah mass-assignment / forging).
        $report = DamageReport::create([
            'user_id' => $user->id,
            'kos_id' => (int) $validated['kos_id'],
            'kamar_id' => $kamarId,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'status' => DamageReport::STATUS_PENDING,
        ]);

        return redirect()->route('tenant.damage-reports.show', $report)
            ->with('success', 'Laporan kerusakan berhasil dikirim.');
    }

    public function createOwner(Request $request)
    {
        $this->authorize('createForOwner', DamageReport::class);

        $user = $request->user();

        $kosList = $user->isSuperAdmin()
            ? Kos::orderBy('name')->get()
            : ($user->isAdmin()
                ? $user->assignedKos()->orderBy('name')->get()
                : Kos::where('owner_id', $user->id)->orderBy('name')->get());

        $kosIds = $kosList->pluck('id')->all();

        $kamarList = Kamar::whereIn('kos_id', $kosIds)->orderBy('room_number')->get();

        $penghuniList = Penghuni::whereIn('kos_id', $kosIds)
            ->where('status', 'active')
            ->with(['user', 'kos', 'kamar'])
            ->orderBy('id')
            ->get();

        return view('owner.damage-reports.create', compact('kosList', 'kamarList', 'penghuniList'));
    }

    public function storeOwner(Request $request)
    {
        $this->authorize('createForOwner', DamageReport::class);

        $validated = $request->validate([
            'kos_id' => ['required', 'integer', 'exists:kos,id'],
            'kamar_id' => ['required', 'integer', 'exists:kamar,id'],
            'penghuni_id' => ['required', 'integer', 'exists:penghunis,id'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        // Rantai scope server-side: kos → milik owner, kamar → milik kos,
        // penghuni → aktif + milik kos + menempati kamar tersebut.
        // Tidak ada kepercayaan pada owner_id/user_id dari request.
        abort_unless($user->isSuperAdmin() || $user->canAccessKos((int) $validated['kos_id']), 403, 'Anda tidak memiliki akses ke kos ini.');

        $kamar = Kamar::whereKey((int) $validated['kamar_id'])->first();
        abort_unless($kamar && (int) $kamar->kos_id === (int) $validated['kos_id'], 403, 'Kamar tidak terkait dengan kos ini.');

        $penghuni = Penghuni::whereKey((int) $validated['penghuni_id'])->first();
        abort_unless(
            $penghuni
                && $penghuni->status === 'active'
                && (int) $penghuni->kos_id === (int) $validated['kos_id']
                && (int) $penghuni->kamar_id === (int) $kamar->id,
            403,
            'Penghuni tidak terkait dengan kamar/kos ini.'
        );

        $report = DamageReport::create([
            'user_id' => $penghuni->user_id,
            'kos_id' => (int) $validated['kos_id'],
            'kamar_id' => (int) $kamar->id,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'status' => DamageReport::STATUS_PENDING,
        ]);

        $prefix = $user->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.damage-reports.show", $report)
            ->with('success', 'Laporan kerusakan berhasil dibuat untuk '.($penghuni->user->name ?? 'penghuni').'.');
    }

    public function destroy(DamageReport $report)
    {
        $this->authorize('delete', $report);

        $report->delete();

        return redirect()->route('tenant.damage-reports.index')
            ->with('success', 'Laporan kerusakan berhasil dibatalkan.');
    }

    public function show(DamageReport $report)
    {
        $this->authorize('view', $report);

        $report->load(['user', 'kos', 'kamar', 'owner']);

        if (request()->user()->isTenant()) {
            return view('tenant.damage-reports.show', compact('report'));
        }

        return view('owner.damage-reports.show', compact('report'));
    }

    public function update(Request $request, DamageReport $report)
    {
        $this->authorize('update', $report);

        $request->validate([
            'status' => 'required|in:pending,reviewing,approved,rejected,resolved',
            'owner_reply' => 'nullable|string|max:1000',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $wasPending = $report->status === DamageReport::STATUS_PENDING;

        $report->update($request->only('status', 'owner_reply', 'rejection_reason') + [
            'owner_id' => $request->user()->id,
        ]);

        if ($request->status === DamageReport::STATUS_REJECTED && $wasPending) {
            $report->update(['rejection_reason' => $request->rejection_reason]);
        }

        if (in_array($request->status, [DamageReport::STATUS_APPROVED, DamageReport::STATUS_RESOLVED])) {
            $report->update(['resolved_at' => now()]);
        }

        if ($wasPending && in_array($request->status, [DamageReport::STATUS_REVIEWING, DamageReport::STATUS_APPROVED, DamageReport::STATUS_REJECTED])) {
            NotificationService::create(
                $report->user_id,
                'damage_report',
                $request->status === DamageReport::STATUS_REJECTED
                    ? 'Lapor Kerusakan Ditolak'
                    : 'Lapor Kerusakan Ditinjau',
                $request->status === DamageReport::STATUS_REJECTED
                    ? 'Lapor kerusakan kamu telah ditolak. Alasan: '.($request->rejection_reason ?? '-')
                    : 'Lapor kerusakan kamu sedang ditinjau oleh pengelola.',
                ['damage_report_id' => $report->id],
                'damage_report_'.$report->id
            );
        }

        $prefix = $request->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.damage-reports.index")
            ->with('success', 'Status laporan diperbarui.');
    }
}
