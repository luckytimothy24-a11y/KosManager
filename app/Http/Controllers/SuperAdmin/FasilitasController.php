<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFasilitasRequest;
use App\Models\Fasilitas;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class FasilitasController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Fasilitas::class);
        $query = Fasilitas::withCount('kamar', 'kos');

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where('name', 'like', '%'.$search.'%');
        }

        $type = $request->query('type', 'kamar');
        if (in_array($type, ['kos', 'kamar'])) {
            $query->where('type', $type);
        }

        $fasilitas = $query->latest()->paginate(10)->withQueryString();
        $totalKos = Fasilitas::where('type', 'kos')->count();
        $totalKamar = Fasilitas::where('type', 'kamar')->count();

        return view('super-admin.fasilitas.index', compact('fasilitas', 'type', 'totalKos', 'totalKamar'));
    }

    public function create()
    {
        $this->authorize('viewAny', Fasilitas::class);

        return view('super-admin.fasilitas.create');
    }

    public function show(Fasilitas $fasilitas)
    {
        $this->authorize('view', $fasilitas);
        $fasilitas->loadCount('kamar', 'kos');

        return view('super-admin.fasilitas.show', compact('fasilitas'));
    }

    public function store(StoreFasilitasRequest $request)
    {
        $this->authorize('create', Fasilitas::class);
        $data = $request->validated();
        $data['type'] = $request->input('type', 'kamar');
        $data['is_active'] = $request->has('is_active');

        Fasilitas::create($data);

        AuditLogService::create('Fasilitas', "Membuat fasilitas {$request->name}");

        return redirect()->route('super-admin.fasilitas.index', $data['type'] === 'kos' ? ['type' => 'kos'] : [])
            ->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Fasilitas $fasilitas)
    {
        $this->authorize('view', $fasilitas);

        return view('super-admin.fasilitas.edit', compact('fasilitas'));
    }

    public function update(StoreFasilitasRequest $request, Fasilitas $fasilitas)
    {
        $this->authorize('update', $fasilitas);
        $data = $request->validated();
        $newType = $request->input('type', $fasilitas->type);
        $data['type'] = $newType;
        $data['is_active'] = $request->has('is_active');

        if ($newType !== $fasilitas->type && ($fasilitas->kamar()->exists() || $fasilitas->kos()->exists())) {
            return redirect()->route('super-admin.fasilitas.edit', $fasilitas)
                ->with('error', 'Tipe fasilitas tidak dapat diubah karena masih digunakan oleh kamar atau kos.');
        }

        $fasilitas->update($data);

        AuditLogService::update('Fasilitas', "Memperbarui fasilitas {$fasilitas->name}");

        return redirect()->route('super-admin.fasilitas.index', $fasilitas->type === 'kos' ? ['type' => 'kos'] : [])
            ->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Fasilitas $fasilitas)
    {
        $this->authorize('delete', $fasilitas);
        if ($fasilitas->kamar()->exists() || $fasilitas->kos()->exists()) {
            return redirect()->route('super-admin.fasilitas.index')
                ->with('error', 'Fasilitas tidak dapat dihapus karena masih digunakan oleh kamar atau kos.');
        }

        $fasilitas->delete();

        AuditLogService::delete('Fasilitas', "Menghapus fasilitas {$fasilitas->name}");

        return redirect()->route('super-admin.fasilitas.index')->with('success', 'Fasilitas berhasil dihapus.');
    }
}
