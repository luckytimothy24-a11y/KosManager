<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingCampaign;
use App\Models\Booking;
use App\Models\DamageReport;
use App\Models\Kos;
use App\Models\KosPhoto;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Kos::class);

        $query = Kos::with(['owner', 'kamar']);

        if ($request->user()->isAdmin()) {
            $kosIds = $request->user()->assignedKos()->pluck('kos.id');
            $query->whereIn('kos.id', $kosIds);
        }

        if ($request->filled('status')) {
            $query->where('moderation_status', $request->status);
        } else {
            $query->where('moderation_status', 'pending');
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%')
                    ->orWhereHas('owner', fn ($oq) => $oq->where('name', 'like', '%'.$search.'%'));
            });
        }

        $pendingKosCount = $query->count();
        $flaggedKosCount = Kos::where('moderation_status', 'flagged')
            ->when($request->user()->isAdmin(), fn ($q) => $q->whereHas('admins', fn ($aq) => $aq->where('users.id', $request->user()->id)))
            ->count();
        $reportedKosCount = Kos::whereHas('bookings', fn ($q) => $q->where('status', 'reported'))
            ->when($request->user()->isAdmin(), fn ($q) => $q->whereHas('admins', fn ($aq) => $aq->where('users.id', $request->user()->id)))
            ->count();

        $pendingPhotosQuery = KosPhoto::where('status', 'pending');
        if ($request->user()->isAdmin()) {
            $kosIds = $request->user()->assignedKos()->pluck('kos.id');
            $pendingPhotosQuery->whereIn('kos_id', $kosIds);
        }
        $pendingPhotosCount = $pendingPhotosQuery->count();

        $pendingAdsQuery = AdvertisingCampaign::where('status', 'pending_review');
        if ($request->user()->isAdmin()) {
            $kosIds = $request->user()->assignedKos()->pluck('kos.id');
            $pendingAdsQuery->whereIn('kos_id', $kosIds);
        }
        $pendingAdsCount = $pendingAdsQuery->count();

        $pendingDamageReportsQuery = DamageReport::where('status', 'pending');
        if ($request->user()->isAdmin()) {
            $kosIds = $request->user()->assignedKos()->pluck('kos.id');
            $pendingDamageReportsQuery->whereIn('kos_id', $kosIds);
        }
        $pendingDamageReports = $pendingDamageReportsQuery->count();

        $recentBookingsQuery = Booking::where('status', 'pending');
        if ($request->user()->isAdmin()) {
            $kosIds = $request->user()->assignedKos()->pluck('kos.id');
            $recentBookingsQuery->whereIn('kos_id', $kosIds);
        }
        $recentBookings = $recentBookingsQuery->count();

        $recentModerationsQuery = Kos::with('owner')
            ->whereIn('moderation_status', ['pending', 'flagged', 'rejected']);
        if ($request->user()->isAdmin()) {
            $kosIds = $request->user()->assignedKos()->pluck('kos.id');
            $recentModerationsQuery->whereIn('kos.id', $kosIds);
        }
        $recentModerations = $recentModerationsQuery->latest()->limit(10)->get();

        return view('super-admin.moderation.index', compact(
            'pendingKosCount',
            'flaggedKosCount',
            'reportedKosCount',
            'pendingPhotosCount',
            'pendingAdsCount',
            'pendingDamageReports',
            'recentBookings',
            'recentModerations'
        ));
    }

    public function kos(Request $request)
    {
        $this->authorize('viewAny', Kos::class);

        $query = Kos::with(['owner', 'kamar']);

        if ($request->user()->isAdmin()) {
            $kosIds = $request->user()->assignedKos()->pluck('kos.id');
            $query->whereIn('kos.id', $kosIds);
        }

        if ($request->filled('status')) {
            $query->where('moderation_status', $request->status);
        } else {
            $query->where('moderation_status', 'pending');
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%')
                    ->orWhereHas('owner', fn ($oq) => $oq->where('name', 'like', '%'.$search.'%'));
            });
        }

        $kos = $query->latest()->paginate(15)->withQueryString();

        return view('super-admin.moderation.kos', compact('kos'));
    }

    public function approveKos(Kos $kos)
    {
        $this->authorize('moderate', $kos);

        $previousStatus = $kos->moderation_status ?? $kos->status;
        $kos->update(['moderation_status' => 'approved', 'status' => 'active']);

        AuditLogService::approve('Moderation', "Kos \"{$kos->name}\" disetujui (sebelumnya: {$previousStatus})", data: ['kos_id' => $kos->id, 'previous_status' => $previousStatus]);

        return redirect()->route(auth()->user()->isAdmin() ? 'admin.moderation.kos' : 'super-admin.moderation.kos')->with('success', "Kos {$kos->name} berhasil disetujui.");
    }

    public function rejectKos(Request $request, Kos $kos)
    {
        $this->authorize('moderate', $kos);

        $data = $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ]);

        $previousStatus = $kos->moderation_status ?? $kos->status;
        $kos->update([
            'moderation_status' => 'rejected',
            'rejection_reason' => $data['reason'],
        ]);

        AuditLogService::reject('Moderation', "Kos \"{$kos->name}\" ditolak (sebelumnya: {$previousStatus})", data: ['kos_id' => $kos->id, 'previous_status' => $previousStatus, 'reason' => $data['reason']]);

        return redirect()->route(auth()->user()->isAdmin() ? 'admin.moderation.kos' : 'super-admin.moderation.kos')->with('success', "Kos {$kos->name} berhasil ditolak.");
    }

    public function photos(Request $request)
    {
        $this->authorize('viewAny', KosPhoto::class);

        $query = KosPhoto::with(['kos.owner'])
            ->where('status', 'pending');

        if ($request->user()->isAdmin()) {
            $kosIds = $request->user()->assignedKos()->pluck('kos.id');
            $query->whereIn('kos_id', $kosIds);
        }

        $photos = $query->latest()->paginate(15)->withQueryString();

        return view('super-admin.moderation.photos', compact('photos'));
    }

    public function approvePhoto(KosPhoto $photo)
    {
        $this->authorize('moderate', $photo);

        $photo->update(['status' => 'approved']);

        AuditLogService::approve('Moderation', "Foto Kos \"{$photo->kos->name}\" disetujui", data: ['photo_id' => $photo->id, 'kos_id' => $photo->kos_id]);

        return redirect()->route(auth()->user()->isAdmin() ? 'admin.moderation.photos' : 'super-admin.moderation.photos')->with('success', 'Foto berhasil disetujui.');
    }

    public function rejectPhoto(Request $request, KosPhoto $photo)
    {
        $this->authorize('moderate', $photo);

        $data = $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ]);

        $photo->update([
            'status' => 'rejected',
            'rejection_reason' => $data['reason'],
        ]);

        AuditLogService::reject('Moderation', "Foto Kos \"{$photo->kos->name}\" ditolak", data: ['photo_id' => $photo->id, 'kos_id' => $photo->kos_id, 'reason' => $data['reason']]);

        return redirect()->route(auth()->user()->isAdmin() ? 'admin.moderation.photos' : 'super-admin.moderation.photos')->with('success', 'Foto berhasil ditolak.');
    }

    public function advertising(Request $request)
    {
        $this->authorize('reviewAny', AdvertisingCampaign::class);

        $query = AdvertisingCampaign::with(['owner', 'kos', 'package'])
            ->where('status', 'pending_review');

        if ($request->user()->isAdmin()) {
            $kosIds = $request->user()->assignedKos()->pluck('kos.id');
            $query->where(function ($q) use ($kosIds) {
                $q->whereNull('kos_id')
                    ->orWhereIn('kos_id', $kosIds);
            });
        }

        $campaigns = $query->latest()->paginate(15)->withQueryString();

        return view('super-admin.moderation.advertising', compact('campaigns'));
    }

    public function damageReports(Request $request)
    {
        $this->authorize('viewAny', DamageReport::class);

        $query = DamageReport::with(['kos.owner', 'user'])
            ->where('status', 'pending');

        if ($request->user()->isAdmin()) {
            $kosIds = $request->user()->assignedKos()->pluck('kos.id');
            $query->whereIn('kos_id', $kosIds);
        }

        $reports = $query->latest()->paginate(15)->withQueryString();

        return view('super-admin.moderation.damage-reports', compact('reports'));
    }
}
