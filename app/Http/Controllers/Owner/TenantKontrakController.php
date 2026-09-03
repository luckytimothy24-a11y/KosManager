<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Kontrak;
use Illuminate\Http\Request;

class TenantKontrakController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->id;

        $kontraks = Kontrak::with(['kos', 'kamar'])
            ->whereHas('penghuni', fn ($q) => $q->where('user_id', $tenantId))
            ->latest()
            ->paginate(10);

        return view('tenant.kontrak.index', compact('kontraks'));
    }

    public function show(Kontrak $kontrak)
    {
        $this->authorize('view', $kontrak);

        $kontrak->load(['penghuni.user', 'kos', 'kamar', 'tagihans.pembayarans']);

        return view('tenant.kontrak.show', compact('kontrak'));
    }
}
