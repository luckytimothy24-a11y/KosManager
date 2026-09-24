<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Penghuni;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Daftar percakapan Tenant → Owner. Hanya tenant yang pernah mengirim/
     * menerima pesan pada kos milik owner saat ini yang muncul.
     */
    public function index()
    {
        $owner = auth()->user();
        $kosIds = Kos::where('owner_id', $owner->id)->pluck('id');

        $messages = Message::with('sender', 'receiver', 'kos')
            ->whereIn('kos_id', $kosIds)
            ->where(function ($q) use ($owner) {
                $q->where('sender_id', $owner->id)->orWhere('receiver_id', $owner->id);
            })
            ->orderByDesc('created_at')
            ->get();

        $conversations = $messages
            ->groupBy(fn (Message $m) => $m->sender_id === (int) $owner->id ? (int) $m->receiver_id : (int) $m->sender_id)
            ->map(function ($group) use ($owner) {
                $latest = $group->first();
                $otherId = $latest->sender_id === (int) $owner->id ? (int) $latest->receiver_id : (int) $latest->sender_id;

                return [
                    'tenant_id' => $otherId,
                    'tenant_name' => $otherId === (int) $latest->sender_id ? $latest->sender->name : $latest->receiver->name,
                    'kos_name' => $latest->kos?->name ?? '-',
                    'last_message' => $latest->message,
                    'last_at' => $latest->created_at,
                    'unread' => $group->filter(fn (Message $m) => $m->read_at === null)->count(),
                ];
            })
            ->filter(fn (array $c) => $c['tenant_id'] > 0)
            ->sortByDesc('last_at')
            ->values();

        $totalUnread = $messages->where('read_at', null)->count();

        return view('owner.messages.index', compact('conversations', 'totalUnread'));
    }

    /**
     * Buka percakapan dengan satu tenant miliknya.
     */
    public function show(User $tenant)
    {
        $owner = auth()->user();
        $kosIds = Kos::where('owner_id', $owner->id)->pluck('id');

        $this->assertTenantLinked($tenant, $kosIds);

        $messages = Message::with('sender', 'kos')
            ->whereIn('kos_id', $kosIds)
            ->between((int) $owner->id, (int) $tenant->id)
            ->orderByDesc('created_at')
            ->get()
            ->reverse()
            ->values();

        Message::whereIn('kos_id', $kosIds)
            ->between((int) $owner->id, (int) $tenant->id)
            ->unread((int) $owner->id)
            ->update(['read_at' => now()]);

        $kosName = $messages->first()?->kos?->name ?? 'Kos';

        $kos = $messages->first()?->kos;
        $penghuni = null;
        $kamar = null;

        if ($kos) {
            $penghuni = Penghuni::where('user_id', $tenant->id)
                ->where('kos_id', $kos->id)
                ->where('status', 'active')
                ->with('kamar')
                ->first();
            $kamar = $penghuni?->kamar;
        }

        $lastIncoming = $messages->reverse()->firstWhere('sender_id', (int) $tenant->id);
        $category = $lastIncoming?->category;

        $latestTagihan = null;
        if ($penghuni && in_array($category, ['tagihan', 'pembayaran'], true)) {
            $latestTagihan = $penghuni->tagihans()->latest('period_start')->first();
        }

        $context = compact('kos', 'penghuni', 'kamar', 'category', 'latestTagihan');

        return view('owner.messages.show', compact('tenant', 'messages', 'kosName', 'context'));
    }

    public function reply(Request $request, User $tenant)
    {
        $owner = $request->user();
        $kosIds = Kos::where('owner_id', $owner->id)->pluck('id');

        $this->assertTenantLinked($tenant, $kosIds);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $kosId = Message::whereIn('kos_id', $kosIds)
            ->between((int) $owner->id, (int) $tenant->id)
            ->latest('created_at')
            ->value('kos_id');

        abort_unless($kosId, 403, 'Percakapan tidak ditemukan.');

        Message::create([
            'kos_id' => (int) $kosId,
            'sender_id' => $owner->id,
            'receiver_id' => $tenant->id,
            'message' => $validated['message'],
        ]);

        NotificationService::messageReplied((int) $tenant->id, $owner->name, route('tenant.messages.index'));

        return redirect()->route('owner.messages.show', $tenant)->with('success', 'Balasan berhasil dikirim.');
    }

    /**
     * Tenant hanya dianggap terkait bila pernah bertukar pesan di salah satu
     * kos milik owner saat ini (relasi kos → pesan terverifikasi via DB).
     */
    private function assertTenantLinked(User $tenant, $kosIds): void
    {
        if ($kosIds->isEmpty()) {
            abort(403, 'Tenant tidak terkait dengan kos Anda.');
        }

        $linked = Message::whereIn('kos_id', $kosIds)
            ->between((int) $tenant->id, (int) auth()->id())
            ->exists();

        abort_unless($linked, 403, 'Tenant tidak terkait dengan kos Anda.');
    }
}
