<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\OwnerDashboardResource;
use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use Illuminate\Http\Request;

class OwnerDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:owner');
    }

    public function show(Request $request): OwnerDashboardResource
    {
        $userId = $request->user()->id;

        $stats = [
            'total_kos' => Kos::where('owner_id', $userId)->count(),
            'total_kamar' => Kamar::whereHas('kos', fn ($q) => $q->where('owner_id', $userId))->count(),
            'total_kamar_available' => Kamar::where('status', 'available')->whereHas('kos', fn ($q) => $q->where('owner_id', $userId))->count(),
            'total_kamar_occupied' => Kamar::where('status', 'occupied')->whereHas('kos', fn ($q) => $q->where('owner_id', $userId))->count(),
            'total_kamar_maintenance' => Kamar::where('status', 'maintenance')->whereHas('kos', fn ($q) => $q->where('owner_id', $userId))->count(),
            'needs_checkin' => Booking::needsCheckin()->whereHas('kos', fn ($q) => $q->where('owner_id', $userId))->count(),
            'pending_payments' => Pembayaran::where('verification_status', 'pending')->whereHas('penghuni.kos', fn ($q) => $q->where('owner_id', $userId))->count(),
            'total_penghunis' => Penghuni::whereHas('kos', fn ($q) => $q->where('owner_id', $userId))->where('status', 'active')->count(),
            'total_revenue' => Pembayaran::where('verification_status', 'approved')->whereHas('penghuni.kos', fn ($q) => $q->where('owner_id', $userId))->sum('amount'),
            'tagihan_outstanding' => Tagihan::payable()->whereHas('kamar.kos', fn ($q) => $q->where('owner_id', $userId))->count(),
            'tagihan_overdue' => Tagihan::where('status', 'overdue')->whereHas('kamar.kos', fn ($q) => $q->where('owner_id', $userId))->count(),
        ];

        return new OwnerDashboardResource($stats);
    }
}
