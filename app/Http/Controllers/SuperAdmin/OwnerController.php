<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::where('role', 'owner')
            ->withCount('ownedKos', 'penghunis', 'bookings');

        if ($request->user()->isAdmin()) {
            $kosIds = $request->user()->assignedKos()->pluck('kos.id');
            $query->whereHas('ownedKos', fn ($q) => $q->whereIn('id', $kosIds));
        }

        $query->with(['ownedKos' => fn ($q) => $q->select('kos.id', 'kos.name', 'kos.status')]);

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('has_kos')) {
            $query->has('ownedKos');
        }

        $owners = $query->latest()->paginate(15)->withQueryString();

        return view('super-admin.owners.index', compact('owners'));
    }

    public function show(User $owner)
    {
        $this->authorize('view', $owner);

        $owner->load([
            'ownedKos' => fn ($q) => $q->withCount('kamar'),
        ]);

        $kosCount = $owner->ownedKos()->count();
        $kamarCount = $owner->ownedKos->sum(fn ($kos) => $kos->kamar_count);
        $tenantCount = $owner->penghunis()->count();
        $bookingCount = $owner->bookings()->count();

        $kosIds = $owner->ownedKos->pluck('id');
        $totalRevenue = Pembayaran::whereHas('penghuni', fn ($q) => $q->whereIn('kos_id', $kosIds))
            ->where('verification_status', 'approved')
            ->sum('amount');
        $pendingPayments = Pembayaran::whereHas('penghuni', fn ($q) => $q->whereIn('kos_id', $kosIds))
            ->where('verification_status', 'pending')
            ->count();

        return view('super-admin.owners.show', ['user' => $owner, 'kosCount' => $kosCount, 'kamarCount' => $kamarCount, 'tenantCount' => $tenantCount, 'bookingCount' => $bookingCount, 'totalRevenue' => $totalRevenue, 'pendingPayments' => $pendingPayments]);
    }

    public function activate(User $owner)
    {
        $this->authorize('toggleActive', $owner);

        abort_if($owner->is_active, 400, 'Owner sudah aktif.');

        $owner->update(['is_active' => true]);

        AuditLogService::log('Activate', 'Owner', "Owner {$owner->name} diaktifkan", data: ['user_id' => $owner->id]);

        return redirect()->route('super-admin.owners.index')->with('success', "Owner {$owner->name} berhasil diaktifkan.");
    }

    public function deactivate(User $owner)
    {
        $this->authorize('toggleActive', $owner);

        abort_if(! $owner->is_active, 400, 'Owner sudah nonaktif.');

        $owner->update(['is_active' => false]);

        AuditLogService::log('Deactivate', 'Owner', "Owner {$owner->name} dinonaktifkan", data: ['user_id' => $owner->id]);

        return redirect()->route('super-admin.owners.index')->with('success', "Owner {$owner->name} berhasil dinonaktifkan.");
    }
}
