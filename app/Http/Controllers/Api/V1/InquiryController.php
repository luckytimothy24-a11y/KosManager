<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\Message;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:tenant');
    }

    /**
     * Daftar thread pertanyaan milik tenant, dikelompokkan per kos.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $threads = Message::with(['kos.owner'])
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
            })
            ->latest()
            ->get()
            ->groupBy('kos_id')
            ->map(function ($group) use ($user) {
                $latest = $group->first();

                return [
                    'kos_id' => (int) $latest->kos_id,
                    'kos_name' => $latest->kos?->name,
                    'owner_name' => $latest->kos?->owner?->name,
                    'last_message' => $latest->message,
                    'last_at' => $latest->created_at,
                    'unread' => $group->filter(fn ($m) => (int) $m->receiver_id === (int) $user->id && $m->read_at === null)->count(),
                ];
            })
            ->filter(fn ($t) => $t['kos_name'] !== null)
            ->sortByDesc('last_at')
            ->values();

        return response()->json(['data' => $threads]);
    }

    /**
     * Thread satu kos + tandai balasan owner sebagai dibaca.
     */
    public function show(Request $request, Kos $kos): JsonResponse
    {
        $user = $request->user();

        abort_unless($kos->status === 'active', 404);
        $owner = $kos->owner;
        abort_unless($owner && $owner->isOwner(), 403, 'Unauthorized.');

        $messages = Message::with('sender')
            ->forKos((int) $kos->id)
            ->between((int) $user->id, (int) $owner->id)
            ->latest()
            ->get()
            ->reverse()
            ->values();

        abort_unless($messages->isNotEmpty(), 404);

        Message::forKos((int) $kos->id)
            ->between((int) $user->id, (int) $owner->id)
            ->unread((int) $user->id)
            ->update(['read_at' => now()]);

        return response()->json([
            'data' => $messages->map(fn ($m) => [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'message' => $m->message,
                'category' => $m->category,
                'read_at' => $m->read_at,
                'created_at' => $m->created_at,
            ]),
        ]);
    }

    /**
     * Ubah pertanyaan milik sendiri selama owner belum membalas sesudahnya.
     */
    public function update(Request $request, Message $message): JsonResponse
    {
        $this->authorize('update', $message);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'category' => ['sometimes', 'nullable', 'string', 'in:'.implode(',', array_keys(Message::CATEGORIES))],
        ]);

        $replied = Message::where('kos_id', $message->kos_id)
            ->where('sender_id', $message->receiver_id)
            ->where('id', '>', $message->id)
            ->exists();

        if ($replied) {
            return response()->json([
                'message' => 'Pertanyaan sudah dibalas owner sehingga tidak dapat diubah.',
            ], 422);
        }

        $message->update([
            'message' => $validated['message'],
            'category' => $validated['category'] ?? $message->category,
        ]);

        return response()->json(['message' => 'Pertanyaan berhasil diubah.']);
    }

    /**
     * Kirim pertanyaan ke owner kos (throttle anti-spam di route).
     */
    public function store(Request $request, Kos $kos): JsonResponse
    {
        $user = $request->user();

        abort_unless($kos->status === 'active', 404);
        $owner = $kos->owner;
        abort_unless($owner && $owner->isOwner(), 403, 'Unauthorized.');
        abort_if((int) $owner->id === (int) $user->id, 403, 'Unauthorized.');

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'category' => ['sometimes', 'nullable', 'string', 'in:'.implode(',', array_keys(Message::CATEGORIES))],
        ]);

        $message = Message::create([
            'kos_id' => $kos->id,
            'sender_id' => $user->id,
            'receiver_id' => $owner->id,
            'category' => $validated['category'] ?? null,
            'message' => $validated['message'],
            'read_at' => null,
        ]);

        try {
            NotificationService::messageSent((int) $owner->id, $user->name, $kos->name, route('owner.messages.show', $user));
        } catch (\Exception $e) {
            \Log::warning('Inquiry notification failed: '.$e->getMessage());
        }

        return response()->json([
            'data' => ['id' => $message->id],
            'message' => 'Pertanyaan berhasil dikirim.',
        ], 201);
    }
}
