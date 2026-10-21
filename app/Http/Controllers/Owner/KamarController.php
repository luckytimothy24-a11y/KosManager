<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKamarRequest;
use App\Http\Requests\UpdateKamarRequest;
use App\Models\Booking;
use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('room_number', 'like', '%'.$search.'%')
                    ->orWhere('room_name', 'like', '%'.$search.'%');
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
        $fasilitasList = Fasilitas::ofType('kamar')->active()->get();

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
        // 1 kamar = 1 penghuni — patungan/multi dihapus
        $data['capacity'] = 1;
        $data['billing_mode'] = 'personal';

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('kamar', 'public');
        }

        $kamar = Kamar::create($data);
        $kamar->fasilitas()->sync($fasilitasIds);

        $prefix = $request->user()->isSuperAdmin() || $request->user()->isOwner() ? 'owner' : 'admin';

        return redirect()->route("$prefix.kamar.index")->with('success', 'Kamar berhasil ditambahkan.');
    }

    public function show(Kamar $kamar)
    {
        $this->authorize('view', $kamar);

        $kamar->load(['kos', 'fasilitas', 'penghunis.user', 'penghunis.kontraks', 'bookings']);

        $tenantCandidates = request()->user()->isSuperAdmin() || request()->user()->isOwner() || request()->user()->isAdmin()
            ? User::where('role', 'tenant')
                ->whereDoesntHave('penghunis', fn ($q) => $q->where('kamar_id', $kamar->id)->where('status', 'active'))
                ->orderBy('name')
                ->get()
            : collect();

        return view('owner.kamar.show', compact('kamar', 'tenantCandidates'));
    }

    public function addPenghuni(Request $request, Kamar $kamar)
    {
        $this->authorize('update', $kamar);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'rental_type' => ['required', 'in:daily,weekly,monthly'],
            'rental_price' => ['required', 'numeric', 'min:0'],
            'billing_mode' => ['sometimes', 'nullable', 'string', 'in:personal'],
            'identity_number' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'check_in_date' => ['required', 'date'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        abort_unless($user->isTenant(), 422, 'Penghuni harus merupakan pengguna dengan peran tenant.');
        abort_if(
            Penghuni::where('user_id', $user->id)->where('kamar_id', $kamar->id)->where('status', 'active')->exists(),
            422,
            'Pengguna tersebut sudah menjadi penghuni aktif kamar ini.'
        );

        $result = \DB::transaction(function () use ($kamar, $user, $validated) {
            $locked = Kamar::whereKey($kamar->id)->lockForUpdate()->first();

            // 1 kamar = 1 penghuni — patungan dihapus
            $occupancy = $locked->occupancy();
            abort_unless($occupancy < 1, 400, 'Kamar sudah terisi. Satu kamar hanya untuk satu penghuni.');

            $penghuni = Penghuni::create([
                'user_id' => $user->id,
                'kos_id' => $locked->kos_id,
                'kamar_id' => $locked->id,
                'identity_number' => $validated['identity_number'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'check_in_date' => $validated['check_in_date'],
                'status' => 'active',
            ]);

            $kontrak = Kontrak::create([
                'contract_number' => 'KT-'.strtoupper(Str::random(12)),
                'penghuni_id' => $penghuni->id,
                'kos_id' => $locked->kos_id,
                'kamar_id' => $locked->id,
                'rental_type' => $validated['rental_type'],
                'rental_price' => $validated['rental_price'],
                'billing_mode' => 'personal',
                'start_date' => $validated['check_in_date'],
                'end_date' => Carbon::parse($validated['check_in_date'])->addYear()->toDateString(),
                'status' => 'active',
            ]);

            $locked->update(['status' => 'occupied', 'capacity' => 1, 'billing_mode' => 'personal']);

            return [$penghuni, $kontrak];
        });

        try {
            NotificationService::checkin($user->id, $kamar->room_number);
            NotificationService::kontrakActive($user->id, $result[1]->contract_number);
            AuditLogService::create('Penghuni', "Penghuni baru {$user->name} ditambahkan ke kamar {$kamar->room_number}", ['penghuni_id' => $result[0]->id, 'kamar_id' => $kamar->id]);
        } catch (\Exception $e) {
            \Log::warning('Add penghuni notification/audit failed: '.$e->getMessage());
        }

        return redirect()->route('owner.kamar.show', $kamar)->with('success', 'Penghuni berhasil ditambahkan ke kamar.');
    }

    public function edit(Kamar $kamar)
    {
        $this->authorize('update', $kamar);

        $user = request()->user();
        $fasilitasList = Fasilitas::ofType('kamar')->active()->get();
        $selectedFasilitas = $kamar->fasilitas->pluck('id')->toArray();

        return view('owner.kamar.edit', compact('kamar', 'fasilitasList', 'selectedFasilitas'));
    }

    public function update(UpdateKamarRequest $request, Kamar $kamar)
    {
        $this->authorize('update', $kamar);

        $data = $request->validated();
        $fasilitasIds = $data['fasilitas'] ?? [];
        unset($data['fasilitas']);
        // 1 kamar = 1 penghuni — patungan/multi dihapus
        $data['capacity'] = 1;
        $data['billing_mode'] = 'personal';

        $newStatus = $data['status'] ?? null;
        $hasActivePenghuni = $kamar->penghunis()->where('status', 'active')->exists();
        $hasActiveBooking = $kamar->bookings()->whereIn('status', Booking::activeStatuses())->exists();

        if ($newStatus !== null && $newStatus !== $kamar->status) {
            $occupancy = $kamar->occupancy();
            $capacity = 1; // personal only
            if ($hasActivePenghuni && $occupancy >= $capacity && ! in_array($newStatus, ['occupied'])) {
                return back()
                    ->withErrors(['status' => 'Kamar penuh dan hanya dapat dalam status terisi.'])
                    ->withInput();
            }
            if ($hasActivePenghuni && $occupancy > 0 && $occupancy < $capacity && ! in_array($newStatus, ['occupied', 'available'])) {
                if (! in_array($newStatus, ['occupied', 'available'])) {
                    return back()
                        ->withErrors(['status' => 'Kamar masih memiliki penghuni aktif.'])
                        ->withInput();
                }
            }
            if (! $hasActivePenghuni && $hasActiveBooking && ! in_array($newStatus, ['booked'])) {
                return back()
                    ->withErrors(['status' => 'Kamar masih memiliki booking aktif dan hanya dapat dalam status dipesan.'])
                    ->withInput();
            }
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

        $prefix = request()->user()->isSuperAdmin() || request()->user()->isOwner() ? 'owner' : 'admin';

        return redirect()->route("$prefix.kamar.index")->with('success', 'Kamar berhasil diperbarui.');
    }

    public function destroy(Kamar $kamar)
    {
        $this->authorize('delete', $kamar);

        $hasActivePenghuni = $kamar->penghunis()->where('status', 'active')->exists();
        $hasActiveBooking = $kamar->bookings()->whereIn('status', Booking::activeStatuses())->exists();

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

        $prefix = request()->user()->isSuperAdmin() || request()->user()->isOwner() ? 'owner' : 'admin';

        return redirect()->route("$prefix.kamar.index")->with('success', 'Kamar berhasil dihapus.');
    }
}
