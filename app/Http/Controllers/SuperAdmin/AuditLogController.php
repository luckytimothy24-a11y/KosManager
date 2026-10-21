<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = AuditLog::with('user');

        if ($request->user()->isAdmin()) {
            $kosIds = $request->user()->assignedKos()->pluck('kos.id');
            if ($kosIds->isEmpty()) {
                $query->where('id', -1);
            } else {
                $query->where(function ($q) use ($kosIds, $request) {
                    $q->where(function ($qq) use ($kosIds) {
                        $qq->whereIn('module', ['Kos', 'Kamar', 'Booking', 'Penghuni', 'Kontrak', 'Tagihan', 'Pembayaran', 'CheckIn', 'CheckOut', 'Advertising', 'DamageReport', 'Moderation', 'Fasilitas'])
                            ->whereIn('kos_id', $kosIds);
                    })->orWhere(function ($qq) use ($request) {
                        $qq->where('module', 'Auth')
                            ->where('action', 'Login')
                            ->where('user_id', $request->user()->id);
                    });
                });
            }
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%'.$search.'%')
                    ->orWhere('action', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('kos_id')) {
            $kosId = (int) $request->kos_id;

            // Admins may only filter within their own assignment scope;
            // requesting an unassigned kos is a scope violation, not an
            // empty result.
            if ($request->user()->isAdmin()) {
                abort_unless($request->user()->canAccessKos($kosId), 403, 'Kos tidak berada dalam scope operasional Anda.');
            }

            $query->where('kos_id', $kosId);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('super-admin.audit-log.index', compact('logs'));
    }
}
