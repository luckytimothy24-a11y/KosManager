<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\DamageReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDamageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin');
    }

    /**
     * Daftar laporan pada kos assignment.
     */
    public function index(Request $request): JsonResponse
    {
        $kosIds = $request->user()->assignedKos()->pluck('kos.id');
        $perPage = min((int) $request->input('per_page', 15), 50);

        $reports = DamageReport::with(['user', 'kos', 'kamar'])
            ->whereIn('kos_id', $kosIds)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $reports->map(fn ($r) => [
                'id' => $r->id,
                'kos_name' => $r->kos?->name,
                'reporter' => $r->user?->name,
                'category' => $r->category,
                'status' => $r->status,
                'created_at' => $r->created_at,
            ]),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    /**
     * Detail laporan pada kos assignment.
     */
    public function show(Request $request, DamageReport $damageReport): JsonResponse
    {
        $this->authorize('view', $damageReport);

        $damageReport->load(['user', 'kos', 'kamar']);

        return response()->json([
            'data' => [
                'id' => $damageReport->id,
                'kos_name' => $damageReport->kos?->name,
                'reporter' => $damageReport->user?->name,
                'category' => $damageReport->category,
                'description' => $damageReport->description,
                'status' => $damageReport->status,
                'created_at' => $damageReport->created_at,
            ],
        ]);
    }
}
