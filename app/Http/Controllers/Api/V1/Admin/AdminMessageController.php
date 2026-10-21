<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AdminMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin');
    }

    private function scopedKosIds(Request $request): Collection
    {
        return $request->user()->assignedKos()->pluck('kos.id');
    }

    /**
     * Daftar thread read-only pada kos assignment.
     */
    public function index(Request $request): JsonResponse
    {
        $kosIds = $this->scopedKosIds($request);

        $threads = Message::with(['sender', 'receiver', 'kos'])
            ->whereIn('kos_id', $kosIds)
            ->latest()
            ->limit(50)
            ->get()
            ->groupBy(function (Message $m) {
                $ownerId = (int) ($m->kos->owner_id ?? 0);
                $otherId = (int) $m->sender_id === $ownerId ? (int) $m->receiver_id : (int) $m->sender_id;

                return (int) $m->kos_id.'_'.$otherId;
            })
            ->map(function ($group) {
                $latest = $group->first();

                return [
                    'tenant_id' => (int) ($latest->sender_id === (int) ($latest->kos->owner_id ?? 0)
                        ? $latest->receiver_id : $latest->sender_id),
                    'kos_id' => (int) $latest->kos_id,
                    'kos_name' => $latest->kos?->name,
                    'last_message' => $latest->message,
                    'last_at' => $latest->created_at,
                ];
            })
            ->filter(fn ($t) => $t['tenant_id'] > 0 && $t['kos_name'] !== null)
            ->sortByDesc('last_at')
            ->values();

        return response()->json(['data' => $threads]);
    }

    /**
     * Baca thread read-only (tanpa menandai dibaca).
     */
    public function show(Request $request, User $tenant): JsonResponse
    {
        $kosIds = $this->scopedKosIds($request);

        $linked = Message::whereIn('kos_id', $kosIds)
            ->where(function ($q) use ($tenant) {
                $q->where('sender_id', $tenant->id)->orWhere('receiver_id', $tenant->id);
            })
            ->exists();

        if (! $linked) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $messages = Message::with('sender')
            ->whereIn('kos_id', $kosIds)
            ->where(function ($q) use ($tenant) {
                $q->where('sender_id', $tenant->id)->orWhere('receiver_id', $tenant->id);
            })
            ->latest()
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'data' => $messages->map(fn ($m) => [
                'id' => $m->id,
                'kos_id' => $m->kos_id,
                'sender_id' => $m->sender_id,
                'sender_name' => $m->sender?->name,
                'message' => $m->message,
                'category' => $m->category,
                'created_at' => $m->created_at,
            ]),
        ]);
    }
}
