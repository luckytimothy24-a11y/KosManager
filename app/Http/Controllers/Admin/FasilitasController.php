<?php

namespace App\Http\Controllers\Admin;

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
        $kosIds = $request->user()->assignedKos()->pluck('kos.id');

        $query = Fasilitas::withCount('kamar', 'kos')
            ->where(function ($q) use ($kosIds) {
                $q->whereHas('kos', fn ($q) => $q->whereIn('kos.id', $kosIds))
                    ->orWhereHas('kamar', fn ($q) => $q->whereHas('kos', fn ($k) => $k->whereIn('id', $kosIds)));
            });

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where('name', 'like', '%'.$search.'%');
        }

        $type = $request->query('type', 'kamar');
        if (in_array($type, ['kos', 'kamar'])) {
            $query->where('type', $type);
        }

        $fasilitas = $query->latest()->paginate(10)->withQueryString();

        return view('admin.fasilitas.index', compact('fasilitas', 'type'));
    }

    public function create()
    {
        $this->authorize('create', Fasilitas::class);
        return view('admin.fasilitas.create');
    }

    public function store(StoreFasilitasRequest $request)
    {
        $this->authorize('create', Fasilitas::class);
        $data = $request->validated();
        $data['type'] = $request->input('type', 'kamar');

        Fasilitas::create($data);

        AuditLogService::create('Fasilitas', "Membuat fasilitas {$request->name}");

        return redirect()->route('admin.fasilitas.index', $data['type'] === 'kos' ? ['type' => 'kos'] : [])
            ->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Fasilitas $fasilitas)
    {
        $this->authorize('update', $fasilitas);
        return view('admin.fasilitas.edit', compact('fasilitas'));
    }

    public function update(StoreFasilitasRequest $request, Fasilitas $fasilitas)
    {
        $this->authorize('update', $fasilitas);
        $data = $request->validated();
        $newType = $request->input('type', $fasilitas->type);
        $data['type'] = $newType;

        if ($newType !== $fasilitas->type && ($fasilitas->kamar()->exists() || $fasilitas->kos()->exists())) {
            return redirect()->route('admin.fasilitas.edit', $fasilitas)
                ->with('error', 'Tipe fasilitas tidak dapat diubah karena masih digunakan oleh kamar atau kos.');
        }

        $fasilitas->update($data);

        AuditLogService::update('Fasilitas', "Memperbarui fasilitas {$fasilitas->name}");

        return redirect()->route('admin.fasilitas.index', $fasilitas->type === 'kos' ? ['type' => 'kos'] : [])
            ->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Fasilitas $fasilitas)
    {
        $this->authorize('delete', $fasilitas);
        if ($fasilitas->kamar()->exists() || $fasilitas->kos()->exists()) {
            return redirect()->route('admin.fasilitas.index')
                ->with('error', 'Fasilitas tidak dapat dihapus karena masih digunakan oleh kamar atau kos.');
        }

        $fasilitas->delete();

        AuditLogService::delete('Fasilitas', "Menghapus fasilitas {$fasilitas->name}");

        return redirect()->route('admin.fasilitas.index')->with('success', 'Fasilitas berhasil dihapus.');
    }
}