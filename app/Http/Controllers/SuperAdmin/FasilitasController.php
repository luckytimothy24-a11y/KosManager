<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFasilitasRequest;
use App\Models\Fasilitas;
use Illuminate\Http\Request;

class FasilitasController extends Controller
{
    public function index(Request $request)
    {
        $query = Fasilitas::withCount('kamar');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $fasilitas = $query->latest()->paginate(10)->withQueryString();

        return view('super-admin.fasilitas.index', compact('fasilitas'));
    }

    public function create()
    {
        return view('super-admin.fasilitas.create');
    }

    public function store(StoreFasilitasRequest $request)
    {
        Fasilitas::create($request->validated());

        return redirect()->route('super-admin.fasilitas.index')->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Fasilitas $fasilitas)
    {
        return view('super-admin.fasilitas.edit', compact('fasilitas'));
    }

    public function update(StoreFasilitasRequest $request, Fasilitas $fasilitas)
    {
        $fasilitas->update($request->validated());

        return redirect()->route('super-admin.fasilitas.index')->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Fasilitas $fasilitas)
    {
        $fasilitas->delete();

        return redirect()->route('super-admin.fasilitas.index')->with('success', 'Fasilitas berhasil dihapus.');
    }
}
