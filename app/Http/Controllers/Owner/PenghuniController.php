<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Penghuni;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class PenghuniController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Penghuni::class);

        $user = $request->user();

        $query = Penghuni::with(['user', 'kos', 'kamar']);

        if ($user->isOwner()) {
            $query->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id));
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereIn('kos_id', $kosIds);
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $identityHash = Penghuni::identityHash($request->search);

            $query->where(function ($q) use ($search, $identityHash) {
                // NIK dienkripsi: pencarian exact-match memakai hash.
                $q->where('identity_number_hash', $identityHash)
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $penghunis = $query->latest()->paginate(10)->withQueryString();

        return view('owner.penghuni.index', compact('penghunis'));
    }

    public function show(Penghuni $penghuni)
    {
        $this->authorize('view', $penghuni);
        $penghuni->load(['user', 'kos', 'kamar', 'kontraks', 'checkIns', 'checkOuts']);

        return view('owner.penghuni.show', compact('penghuni'));
    }

    /**
     * Buka NIK penuh (audit-logged). Dipakai tombol "Lihat" pada halaman detail.
     */
    public function revealIdentity(Penghuni $penghuni)
    {
        $this->authorize('view', $penghuni);

        AuditLogService::create(
            'Penghuni',
            'Membuka NIK penghuni '.($penghuni->user?->name ?? '#'.$penghuni->id),
            ['penghuni_id' => $penghuni->id, 'kos_id' => $penghuni->kos_id]
        );

        return response()->json([
            'identity_number' => $penghuni->identity_number,
        ]);
    }
}
