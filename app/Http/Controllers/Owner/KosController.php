<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKosRequest;
use App\Http\Requests\UpdateKosRequest;
use App\Models\Kos;
use Illuminate\Http\Request;

class KosController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Kos::withCount('kamar', 'penghunis');

        if ($user->isOwner()) {
            $query->where('owner_id', $user->id);
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereIn('id', $kosIds);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('address', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $kos = $query->latest()->paginate(10)->withQueryString();

        return view('owner.kos.index', compact('kos'));
    }

    public function create()
    {
        return view('owner.kos.create');
    }

    public function store(StoreKosRequest $request)
    {
        $data = $request->validated();

        if ($request->user()->isOwner()) {
            $data['owner_id'] = $request->user()->id;
        } else {
            $request->validate(['owner_id' => ['required', 'exists:users,id,role,owner']]);
            $data['owner_id'] = $request->input('owner_id');
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('kos', 'public');
        }

        Kos::create($data);

        return redirect()->route('owner.kos.index')->with('success', 'Kos berhasil ditambahkan.');
    }

    public function show(Kos $kos)
    {
        $this->authorize('view', $kos);
        $kos->loadCount('kamar', 'penghunis', 'bookings');

        return view('owner.kos.show', compact('kos'));
    }

    public function edit(Kos $kos)
    {
        $this->authorize('update', $kos);

        return view('owner.kos.edit', compact('kos'));
    }

    public function update(UpdateKosRequest $request, Kos $kos)
    {
        $this->authorize('update', $kos);

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($kos->photo) {
                \Storage::disk('public')->delete($kos->photo);
            }
            $data['photo'] = $request->file('photo')->store('kos', 'public');
        }

        $kos->update($data);

        return redirect()->route('owner.kos.index')->with('success', 'Kos berhasil diperbarui.');
    }

    public function destroy(Kos $kos)
    {
        $this->authorize('delete', $kos);

        $kos->delete();

        return redirect()->route('owner.kos.index')->with('success', 'Kos berhasil dihapus.');
    }
}
