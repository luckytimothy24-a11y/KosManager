<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class OwnerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $isAdmin = $request->user()->isAdmin();
        $assignedIds = $isAdmin ? $request->user()->assignedKos()->pluck('kos.id') : collect();

        $query = User::where('role', 'owner');

        if ($isAdmin) {
            $query->whereHas('ownedKos', fn ($q) => $q->whereIn('id', $assignedIds))
                ->withCount(['ownedKos as owned_kos_count' => fn ($q) => $q->whereIn('id', $assignedIds)]);
        } else {
            $query->withCount('ownedKos');
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $filteredQuery = clone $query;
        $owners = $query->latest()->paginate(15)->withQueryString();

        $totalOwners = $filteredQuery->count();
        $totalKos = $isAdmin ? $assignedIds->count() : Kos::count();

        return view('admin.owners.index', compact('owners', 'totalOwners', 'totalKos'));
    }

    public function show(User $owner)
    {
        $this->authorize('view', $owner);

        $isAdmin = auth()->user()->isAdmin();
        $assignedIds = $isAdmin ? auth()->user()->assignedKos()->pluck('kos.id') : collect();

        $kosQuery = $owner->ownedKos()
            ->select('id', 'owner_id', 'name', 'address', 'status', 'photo')
            ->with(['coverPhoto', 'photos'])
            ->withCount('kamar')
            ->withCount(['penghunis as active_penghunis_count' => fn ($q) => $q->where('status', 'active')])
            ->latest('id');

        if ($isAdmin) {
            $kosQuery->whereIn('id', $assignedIds);
        }

        $kosPaginator = $kosQuery->paginate(9)->withQueryString();
        $owner->setRelation('ownedKos', $kosPaginator->getCollection());

        $canViewKos = Route::has('admin.kos.show');
        $accessibleKosIds = $isAdmin ? $assignedIds->map(fn ($v) => (int) $v)->flip() : $kosPaginator->getCollection()->pluck('id')->map(fn ($v) => (int) $v)->flip();

        return view('admin.owners.show', compact('owner', 'canViewKos', 'kosPaginator', 'accessibleKosIds'));
    }
}
