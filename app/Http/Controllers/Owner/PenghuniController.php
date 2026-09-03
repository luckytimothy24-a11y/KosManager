<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Penghuni;
use Illuminate\Http\Request;

class PenghuniController extends Controller
{
    public function index(Request $request)
    {
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
            $query->where(function ($q) use ($search) {
                $q->where('identity_number', 'like', '%'.$search.'%')
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
}
