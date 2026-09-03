<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKosRequest;
use App\Http\Requests\UpdateKosRequest;
use App\Models\Booking;
use App\Models\Fasilitas;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KosController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $this->authorize('viewAny', Kos::class);

        $query = Kos::withCount('kamar', 'penghunis');

        if ($user->isOwner()) {
            $query->where('owner_id', $user->id);
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereIn('id', $kosIds);
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%');
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
        $fasilitasList = Fasilitas::ofType('kos')->active()->get();

        $owners = ! $this->authUserIsOwner()
            ? User::where('role', 'owner')->orderBy('name')->get()
            : collect();

        return view('owner.kos.create', compact('fasilitasList', 'owners'));
    }

    public function store(StoreKosRequest $request)
    {
        $data = $request->validated();
        $fasilitasIds = $data['fasilitas'] ?? [];
        unset($data['fasilitas']);

        if ($request->user()->isOwner()) {
            $data['owner_id'] = $request->user()->id;
        } else {
            $request->validate(['owner_id' => ['required', Rule::exists('users', 'id')->where('role', 'owner')]]);
            $data['owner_id'] = $request->input('owner_id');
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('kos', 'public');
        }

        $kos = Kos::create($data);
        $kos->fasilitas()->sync($fasilitasIds);

        if (! $request->user()->isAdmin()) {
            $adminIds = $request->input('admins', []);
            if (is_array($adminIds)) {
                $kos->admins()->sync(array_filter($adminIds));
            }
        }

        return redirect()->route('owner.kos.index')->with('success', 'Kos berhasil ditambahkan.');
    }

    public function show(Kos $kos)
    {
        $this->authorize('view', $kos);
        $kos->loadCount('kamar', 'penghunis', 'bookings');
        $kos->load('fasilitas');

        return view('owner.kos.show', compact('kos'));
    }

    public function edit(Kos $kos)
    {
        $this->authorize('update', $kos);

        $fasilitasList = Fasilitas::ofType('kos')->active()->get();
        $selectedFasilitas = $kos->fasilitas->pluck('id')->toArray();

        $admins = User::where('role', 'admin')->orderBy('name')->get();
        $selectedAdmins = $kos->admins->pluck('id')->toArray();

        return view('owner.kos.edit', compact('kos', 'fasilitasList', 'selectedFasilitas', 'admins', 'selectedAdmins'));
    }

    public function update(UpdateKosRequest $request, Kos $kos)
    {
        $this->authorize('update', $kos);

        $data = $request->validated();
        $fasilitasIds = $data['fasilitas'] ?? [];
        unset($data['fasilitas']);

        if ($request->hasFile('photo')) {
            if ($kos->photo && \Storage::disk('public')->exists($kos->photo)) {
                \Storage::disk('public')->delete($kos->photo);
            }
            $data['photo'] = $request->file('photo')->store('kos', 'public');
        }

        $kos->update($data);
        $kos->fasilitas()->sync($fasilitasIds);

        if (! $request->user()->isAdmin()) {
            $adminIds = $request->input('admins', []);
            $kos->admins()->sync(is_array($adminIds) ? array_filter($adminIds) : []);
        }

        return redirect()->route('owner.kos.index')->with('success', 'Kos berhasil diperbarui.');
    }

    public function destroy(Kos $kos)
    {
        $this->authorize('delete', $kos);

        $hasActivePenghuni = $kos->penghunis()->where('status', 'active')->exists();
        $hasActiveBooking = $kos->bookings()->whereIn('status', Booking::activeStatuses())->exists();

        if ($hasActivePenghuni || $hasActiveBooking) {
            return redirect()->route('owner.kos.index')
                ->with('error', 'Kos tidak dapat dihapus karena masih memiliki penghuni aktif atau booking aktif.');
        }

        $hasUnpaidTagihan = $kos->penghunis()
            ->whereHas('tagihans', fn ($q) => $q->outstanding())
            ->exists();

        if ($hasUnpaidTagihan) {
            return redirect()->route('owner.kos.index')
                ->with('error', 'Kos tidak dapat dihapus karena masih memiliki tagihan yang belum lunas.');
        }

        $hasHistory = $kos->kamar()->exists()
            || $kos->penghunis()->exists()
            || $kos->bookings()->exists();

        if ($hasHistory) {
            return redirect()->route('owner.kos.index')
                ->with('error', 'Kos tidak dapat dihapus karena masih memiliki riwayat kamar, penghuni, atau booking.');
        }

        $kos->delete();

        return redirect()->route('owner.kos.index')->with('success', 'Kos berhasil dihapus.');
    }

    private function authUserIsOwner(): bool
    {
        return auth()->user()->isOwner();
    }
}
