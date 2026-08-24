<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKamarRequest;
use App\Http\Requests\UpdateKamarRequest;
use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Http\Request;

class KamarController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Kamar::class);

        $user = $request->user();

        $query = Kamar::with('kos', 'fasilitas');

        if ($user->isOwner()) {
            $query->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id));
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereIn('kos_id', $kosIds);
        }

        if ($request->filled('kos_id')) {
            $query->where('kos_id', $request->kos_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('room_number', 'like', '%'.$request->search.'%')
                    ->orWhere('room_name', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $kamars = $query->latest()->paginate(10)->withQueryString();

        $kosList = $user->isOwner()
            ? Kos::where('owner_id', $user->id)->get()
            : ($user->isAdmin() ? $user->assignedKos : Kos::all());

        return view('owner.kamar.index', compact('kamars', 'kosList'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Kamar::class);

        $user = $request->user();
        $kosList = $user->isOwner()
            ? Kos::where('owner_id', $user->id)->get()
            : ($user->isAdmin() ? $user->assignedKos : Kos::all());
        $fasilitasList = Fasilitas::all();

        return view('owner.kamar.create', compact('kosList', 'fasilitasList'));
    }

    public function store(StoreKamarRequest $request)
    {
        $this->authorize('create', Kamar::class);

        $kos = Kos::findOrFail($request->kos_id);
        $this->authorize('update', $kos);

        $data = $request->validated();
        $fasilitasIds = $data['fasilitas'] ?? [];
        unset($data['fasilitas']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('kamar', 'public');
        }

        $kamar = Kamar::create($data);
        $kamar->fasilitas()->sync($fasilitasIds);

        return redirect()->route('owner.kamar.index')->with('success', 'Kamar berhasil ditambahkan.');
    }

    public function show(Kamar $kamar)
    {
        $this->authorize('view', $kamar);

        $kamar->load('kos', 'fasilitas', 'penghunis.user', 'bookings');

        return view('owner.kamar.show', compact('kamar'));
    }

    public function edit(Kamar $kamar)
    {
        $this->authorize('update', $kamar);

        $user = request()->user();
        $kosList = $user->isOwner()
            ? Kos::where('owner_id', $user->id)->get()
            : ($user->isAdmin() ? $user->assignedKos : Kos::all());
        $fasilitasList = Fasilitas::all();
        $selectedFasilitas = $kamar->fasilitas->pluck('id')->toArray();

        return view('owner.kamar.edit', compact('kamar', 'kosList', 'fasilitasList', 'selectedFasilitas'));
    }

    public function update(UpdateKamarRequest $request, Kamar $kamar)
    {
        $this->authorize('update', $kamar);

        $data = $request->validated();
        $fasilitasIds = $data['fasilitas'] ?? [];
        unset($data['fasilitas']);

        if (($data['status'] ?? null) === 'available'
            && $kamar->status === 'occupied'
            && $kamar->penghunis()->where('status', 'active')->exists()) {
            return back()
                ->withErrors(['status' => 'Kamar masih memiliki penghuni aktif dan tidak dapat diubah menjadi tersedia.'])
                ->withInput();
        }

        if ($request->hasFile('photo')) {
            if ($kamar->photo) {
                \Storage::disk('public')->delete($kamar->photo);
            }
            $data['photo'] = $request->file('photo')->store('kamar', 'public');
        }

        $kamar->update($data);

        if ($request->has('fasilitas')) {
            $kamar->fasilitas()->sync($fasilitasIds);
        }

        return redirect()->route('owner.kamar.index')->with('success', 'Kamar berhasil diperbarui.');
    }

    public function destroy(Kamar $kamar)
    {
        $this->authorize('delete', $kamar);

        $hasActivePenghuni = $kamar->penghunis()->where('status', 'active')->exists();
        $hasActiveBooking = $kamar->bookings()->whereIn('status', ['pending', 'approved'])->exists();

        if ($hasActivePenghuni || $hasActiveBooking) {
            return redirect()->route('owner.kamar.index')
                ->with('error', 'Kamar tidak dapat dihapus karena masih memiliki penghuni aktif atau booking aktif.');
        }

        $hasHistory = $kamar->kontraks()->exists()
            || $kamar->tagihans()->exists()
            || $kamar->checkIns()->exists()
            || $kamar->checkOuts()->exists()
            || $kamar->bookings()->exists();

        if ($hasHistory) {
            return redirect()->route('owner.kamar.index')
                ->with('error', 'Kamar tidak dapat dihapus karena masih memiliki riwayat kontrak, tagihan, pembayaran, check-in, atau booking. Ubah status kamar menjadi maintenance bila tidak digunakan.');
        }

        $kamar->delete();

        return redirect()->route('owner.kamar.index')->with('success', 'Kamar berhasil dihapus.');
    }
}
