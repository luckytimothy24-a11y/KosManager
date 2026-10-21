<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DamageReport;
use App\Models\Kamar;
use App\Models\Penghuni;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DamageReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:tenant');
    }

    /**
     * Daftar laporan milik tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $reports = DamageReport::with(['kos', 'kamar'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $reports->map(fn ($r) => [
                'id' => $r->id,
                'kos_id' => $r->kos_id,
                'kos_name' => $r->kos?->name,
                'kamar_id' => $r->kamar_id,
                'category' => $r->category,
                'description' => $r->description,
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
     * Buat laporan untuk kos tempat tenant berpenghuni aktif.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', DamageReport::class);

        $validated = $request->validate([
            'kos_id' => ['required', 'integer', 'exists:kos,id'],
            'kamar_id' => ['nullable', 'integer', 'exists:kamar,id'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        $penghuni = Penghuni::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('kos_id', (int) $validated['kos_id'])
            ->first();

        if (! $penghuni) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $kamarId = $penghuni->kamar_id;
        if (! empty($validated['kamar_id'])) {
            $kamar = Kamar::whereKey((int) $validated['kamar_id'])->first();
            if (! $kamar || (int) $kamar->kos_id !== (int) $validated['kos_id']
                || (int) $penghuni->kamar_id !== (int) $kamar->id) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            $kamarId = (int) $kamar->id;
        }

        $report = DamageReport::create([
            'user_id' => $user->id,
            'kos_id' => (int) $validated['kos_id'],
            'kamar_id' => $kamarId,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'status' => DamageReport::STATUS_PENDING,
        ]);

        return response()->json([
            'data' => ['id' => $report->id],
            'message' => 'Laporan kerusakan berhasil dikirim.',
        ], 201);
    }

    /**
     * Detail laporan milik tenant.
     */
    public function show(DamageReport $damageReport): JsonResponse
    {
        $this->authorize('view', $damageReport);

        $damageReport->load(['kos', 'kamar']);

        return response()->json([
            'data' => [
                'id' => $damageReport->id,
                'kos_name' => $damageReport->kos?->name,
                'category' => $damageReport->category,
                'description' => $damageReport->description,
                'status' => $damageReport->status,
                'owner_reply' => $damageReport->owner_reply,
                'created_at' => $damageReport->created_at,
            ],
        ]);
    }

    /**
     * Batalkan laporan milik tenant yang masih pending (soft-delete).
     */
    public function destroy(DamageReport $damageReport): JsonResponse
    {
        $this->authorize('delete', $damageReport);

        $damageReport->delete();

        return response()->json([
            'message' => 'Laporan kerusakan berhasil dibatalkan.',
        ]);
    }
}
