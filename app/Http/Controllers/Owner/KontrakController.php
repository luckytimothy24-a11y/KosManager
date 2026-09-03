<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Kontrak;
use Illuminate\Http\Request;

class KontrakController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Kontrak::with(['penghuni.user', 'kos', 'kamar']);

        if ($user->isOwner()) {
            $query->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id));
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereIn('kos_id', $kosIds);
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', '%'.$search.'%')
                    ->orWhereHas('penghuni', fn ($pq) => $pq->whereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$search.'%')));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $kontraks = $query->latest()->paginate(10)->withQueryString();

        return view('owner.kontrak.index', compact('kontraks'));
    }

    public function show(Kontrak $kontrak)
    {
        $kontrak->load(['penghuni.user', 'kos', 'kamar', 'tagihans']);
        $this->authorize('view', $kontrak);

        return view('owner.kontrak.show', compact('kontrak'));
    }
}
