<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\Message;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API Admin ↔ Owner communication. Scope assigned-kos. Bukan Owner Management.
 */
class AdminOwnerMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin');
    }

    public function index(Request $request): JsonResponse
    {
        $admin = $request->user();
        $kosIds = $admin->isSuperAdmin()
            ? Kos::pluck('id')
            : $admin->assignedKos()->pluck('kos.id');

        $contacts = Kos::with('owner')
            ->whereIn('id', $kosIds)
            ->whereNotNull('owner_id')
            ->get()
            ->map(fn (Kos $kos) => [
                'kos_id' => $kos->id,
                'kos_name' => $kos->name,
                'owner_id' => $kos->owner_id,
                'owner_name' => $kos->owner?->name,
            ])
            ->filter(fn ($c) => $c['owner_id'] !== null)
            ->values();

        return response()->json(['data' => $contacts]);
    }

    public function show(Kos $kos, User $owner): JsonResponse
    {
        $admin = request()->user();
        $this->assertAdminCanContact($admin, $kos, $owner);

        $messages = Message::with('sender')
            ->where('kos_id', $kos->id)
            ->between((int) $admin->id, (int) $owner->id)
            ->orderBy('id')
            ->get()
            ->map(fn (Message $m) => [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'sender_name' => $m->sender?->name,
                'message' => $m->message,
                'category' => $m->category,
                'created_at' => $m->created_at,
            ]);

        return response()->json(['data' => $messages]);
    }

    public function store(Request $request, Kos $kos, User $owner): JsonResponse
    {
        $admin = $request->user();
        $this->assertAdminCanContact($admin, $kos, $owner);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'category' => ['sometimes', 'nullable', 'string', 'in:'.implode(',', array_keys(Message::CATEGORIES))],
        ]);

        $message = Message::create([
            'kos_id' => $kos->id,
            'sender_id' => $admin->id,
            'receiver_id' => $owner->id,
            'category' => $validated['category'] ?? null,
            'message' => $validated['message'],
            'read_at' => null,
        ]);

        try {
            NotificationService::adminMessageSent(
                (int) $owner->id,
                $admin->name,
                $kos->name,
                route('admin.owner-messages.show', ['kos' => $kos, 'owner' => $owner])
            );
        } catch (\Exception $e) {
            \Log::warning('API admin→owner message failed: '.$e->getMessage());
        }

        return response()->json([
            'data' => [
                'id' => $message->id,
                'message' => $message->message,
                'category' => $message->category,
                'created_at' => $message->created_at,
            ],
            'message' => 'Pesan operasional terkirim ke pemilik kos.',
        ], 201);
    }

    private function assertAdminCanContact(User $admin, Kos $kos, User $owner): void
    {
        abort_unless($admin->isAdmin() || $admin->isSuperAdmin(), 403);
        abort_unless($owner->isOwner(), 403, 'Tujuan bukan pemilik kos.');
        abort_unless((int) $kos->owner_id === (int) $owner->id, 403, 'Owner tidak terkait dengan kos ini.');

        if ($admin->isSuperAdmin()) {
            return;
        }

        abort_unless($admin->assignedKos()->whereKey($kos->id)->exists(), 403, 'Kos tidak berada dalam penugasan Anda.');
    }
}
