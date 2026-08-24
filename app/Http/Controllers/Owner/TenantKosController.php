<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Http\Request;

class TenantKosController extends Controller
{
    public function index(Request $request)
    {
        $query = Kos::where('status', 'active')
            ->withCount(['kamar as kamar_tersedia' => fn ($q) => $q->where('status', 'available')])
            ->withMin(['kamar as harga_mulai' => fn ($q) => $q->where('status', 'available')], 'monthly_price');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('address', 'like', "%{$q}%"));
        }

        $kosList = $query->latest()->paginate(9)->withQueryString();

        return view('tenant.kos.index', compact('kosList'));
    }

    public function show(Kos $kos)
    {
        abort_unless($kos->status === 'active', 404);

        $kamarTersedia = Kamar::where('kos_id', $kos->id)
            ->where('status', 'available')
            ->orderBy('room_number')
            ->get();

        return view('tenant.kos.show', compact('kos', 'kamarTersedia'));
    }
}
