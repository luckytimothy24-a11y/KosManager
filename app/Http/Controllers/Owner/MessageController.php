<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Penghuni;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Daftar percakapan Tenant → Owner. Thread per (kos, tenant) —
     * tidak digabung antar kos milik owner yang sama.
     */
    public function index()
    {
        $owner = auth()->user();

        // Identitas owner percakapan berasal dari kolom messages.owner_id —
        // tidak lagi diturunkan dari kos.owner_id saat ini.
        $messages = Message::with('sender', 'receiver', 'kos')
            ->forOwner((int) $owner->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25);

        // Hanya pesan masuk yang belum dibaca — balasan owner sendiri tidak
        // boleh dihitung sebagai unread.
        $totalUnread = Message::forOwner((int) $owner->id)
            ->unread((int) $owner->id)
            ->count();

        $conversations = self::conversationList($messages, $owner);

        return view('owner.messages.index', compact('conversations', 'totalUnread'));
    }

    /**
     * Daftar ringkas percakapan per (kos_id, lawan bicara).
     */
    public static function conversationList($messages, User $owner)
    {
        return $messages
            ->groupBy(function (Message $m) use ($owner) {
                $conversationOwnerId = (int) ($m->owner_id ?? $owner->id);
                $otherId = (int) $m->sender_id === $conversationOwnerId ? (int) $m->receiver_id : (int) $m->sender_id;

                return ((int) $m->kos_id).'_'.$otherId;
            })
            ->map(function ($group) use ($owner) {
                $latest = $group->first();
                $kosId = (int) $latest->kos_id;
                $conversationOwnerId = (int) ($latest->owner_id ?? $owner->id);
                $otherId = (int) $latest->sender_id === $conversationOwnerId ? (int) $latest->receiver_id : (int) $latest->sender_id;

                return [
                    'tenant_id' => $otherId,
                    'kos_id' => $kosId,
                    'tenant_name' => $otherId === (int) $latest->sender_id ? $latest->sender->name : $latest->receiver->name,
                    'kos_name' => $latest->kos?->name ?? '-',
                    'last_message' => $latest->message,
                    'last_at' => $latest->created_at,
                    'unread' => $group->filter(fn (Message $m) => $m->read_at === null && (int) $m->receiver_id === (int) $owner->id)->count(),
                ];
            })
            ->filter(fn (array $c) => $c['tenant_id'] > 0)
            ->sortByDesc('last_at')
            ->values();
    }

    /**
     * Daftar percakapan read-only untuk admin: hanya kos dalam assignment.
     */
    public function adminIndex(Request $request)
    {
        $admin = $request->user();
        abort_unless($admin->isAdmin() || $admin->isSuperAdmin(), 403);

        $kosIds = $admin->isSuperAdmin()
            ? Kos::pluck('id')
            : $admin->assignedKos()->pluck('kos.id');

        $messages = Message::with('sender', 'receiver', 'kos')
            ->whereIn('kos_id', $kosIds)
            ->whereNotNull('owner_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25);

        $totalUnread = Message::whereIn('kos_id', $kosIds)
            ->whereNotNull('owner_id')
            ->whereNull('read_at')
            ->count();

        $conversations = $messages
            ->groupBy(function (Message $m) {
                $ownerId = (int) ($m->owner_id ?? 0);
                $otherId = (int) $m->sender_id === $ownerId ? (int) $m->receiver_id : (int) $m->sender_id;

                return (int) $m->kos_id.'_'.$otherId;
            })
            ->map(function ($group) {
                $latest = $group->first();
                $ownerId = (int) ($latest->owner_id ?? 0);
                $otherId = (int) $latest->sender_id === $ownerId ? (int) $latest->receiver_id : (int) $latest->sender_id;
                $otherName = $otherId === (int) $latest->sender_id
                    ? ($latest->sender->name ?? '-')
                    : ($latest->receiver->name ?? '-');

                return [
                    'tenant_id' => $otherId,
                    'kos_id' => (int) $latest->kos_id,
                    'tenant_name' => $otherName,
                    'kos_name' => $latest->kos?->name ?? '-',
                    'last_message' => $latest->message,
                    'last_at' => $latest->created_at,
                    'unread' => $group->filter(fn (Message $m) => $m->read_at === null)->count(),
                ];
            })
            ->filter(fn (array $c) => $c['tenant_id'] > 0)
            ->sortByDesc('last_at')
            ->values();

        return view('admin.messages.index', compact('conversations', 'totalUnread'));
    }

    /**
     * Baca thread read-only untuk admin (audit sengketa).
     */
    public function adminShow(Request $request, User $tenant)
    {
        $admin = $request->user();
        abort_unless($admin->isAdmin() || $admin->isSuperAdmin(), 403);

        $kosIds = $admin->isSuperAdmin()
            ? Kos::pluck('id')
            : $admin->assignedKos()->pluck('kos.id');

        $linked = Message::whereIn('kos_id', $kosIds)
            ->whereNotNull('owner_id')
            ->where(function ($q) use ($tenant) {
                $q->where('sender_id', $tenant->id)->orWhere('receiver_id', $tenant->id);
            })
            ->exists();
        abort_unless($linked, 403, 'Tenant tidak terkait dengan kos dalam scope Anda.');

        $messages = Message::with('sender', 'kos')
            ->whereIn('kos_id', $kosIds)
            ->whereNotNull('owner_id')
            ->where(function ($q) use ($tenant) {
                $q->where('sender_id', $tenant->id)->orWhere('receiver_id', $tenant->id);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->reverse()
            ->values();

        abort_unless($messages->isNotEmpty(), 404);

        $kosName = $messages->first()?->kos?->name ?? 'Kos';

        try {
            AuditLogService::create(
                'Pesan',
                "Admin {$admin->name} membaca percakapan {$tenant->name} ({$kosName})",
                ['tenant_id' => $tenant->id]
            );
        } catch (\Exception $e) {
            \Log::warning('Admin message read audit failed: '.$e->getMessage());
        }

        return view('admin.messages.show', compact('tenant', 'messages', 'kosName'));
    }

    /**
     * Pertanyaan masuk dari calon penghuni.
     */
    public function inquiries()
    {
        $owner = auth()->user();
        $kosIds = Kos::where('owner_id', $owner->id)->pluck('id');

        $activeTenantIds = Penghuni::whereIn('kos_id', $kosIds)
            ->where('status', 'active')
            ->pluck('user_id')
            ->all();

        $threads = Message::with('sender', 'kos')
            ->forOwner((int) $owner->id)
            ->where('sender_id', '!=', $owner->id)
            ->whereNotIn('sender_id', $activeTenantIds)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (Message $m) => (int) $m->kos_id.'_'.(int) $m->sender_id)
            ->map(function ($group) {
                $latest = $group->first();

                return [
                    'tenant_id' => (int) $latest->sender_id,
                    'tenant_name' => $latest->sender->name ?? '-',
                    'kos' => $latest->kos,
                    'kos_id' => (int) $latest->kos_id,
                    'kos_name' => $latest->kos?->name ?? '-',
                    'last_message' => $latest->message,
                    'last_at' => $latest->created_at,
                    'unread' => $group->filter(fn (Message $m) => $m->read_at === null && (int) $m->receiver_id === (int) auth()->id())->count(),
                ];
            })
            ->filter(fn (array $t) => $t['tenant_id'] > 0 && $t['kos'] !== null)
            ->sortByDesc('last_at')
            ->values();

        $totalUnread = $threads->sum('unread');

        return view('owner.messages.inquiries', compact('threads', 'totalUnread'));
    }

    /**
     * Buka percakapan dengan satu tenant pada satu kos (query ?kos=).
     */
    public function show(Request $request, User $tenant)
    {
        $owner = auth()->user();
        $kosIds = $this->conversationKosIds($owner);

        $this->assertTenantLinked($tenant, $kosIds);

        $kosId = $this->resolveKosId($request, $owner, $tenant, $kosIds);
        abort_unless($kosId, 403, 'Percakapan tidak ditemukan.');

        $messages = Message::with('sender', 'kos')
            ->forOwner((int) $owner->id)
            ->between((int) $owner->id, (int) $tenant->id)
            ->where('kos_id', $kosId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->reverse()
            ->values();

        abort_unless($messages->isNotEmpty(), 404);

        Message::forOwner((int) $owner->id)
            ->between((int) $owner->id, (int) $tenant->id)
            ->where('kos_id', $kosId)
            ->unread((int) $owner->id)
            ->update(['read_at' => now()]);

        $kos = Kos::with('owner')->find($kosId);
        $kosName = $kos?->name ?? ($messages->first()?->kos?->name ?? 'Kos');

        $penghuni = Penghuni::where('user_id', $tenant->id)
            ->where('kos_id', $kosId)
            ->where('status', 'active')
            ->with('kamar')
            ->first();
        $kamar = $penghuni?->kamar;

        $lastIncoming = $messages->reverse()->firstWhere('sender_id', (int) $tenant->id);
        $category = $lastIncoming?->category;

        $latestTagihan = null;
        if ($penghuni && in_array($category, ['tagihan', 'pembayaran'], true)) {
            $latestTagihan = $penghuni->tagihans()->latest('period_start')->first();
        }

        $context = compact('kos', 'penghuni', 'kamar', 'category', 'latestTagihan');

        $allMessages = Message::with('sender', 'receiver', 'kos')
            ->forOwner((int) $owner->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get();
        $conversations = self::conversationList($allMessages, $owner);

        return view('owner.messages.show', compact('tenant', 'messages', 'kosName', 'context', 'conversations', 'kosId'));
    }

    /**
     * Ringkasan thread untuk polling ringan per kos.
     */
    public function latest(Request $request, User $tenant)
    {
        $owner = $request->user();
        $kosIds = $this->conversationKosIds($owner);

        $this->assertTenantLinked($tenant, $kosIds);

        $kosId = $this->resolveKosId($request, $owner, $tenant, $kosIds);
        abort_unless($kosId, 403, 'Percakapan tidak ditemukan.');

        $since = (int) $request->input('since', 0);
        $base = Message::forOwner((int) $owner->id)
            ->between((int) $owner->id, (int) $tenant->id)
            ->where('kos_id', $kosId);

        return response()->json([
            'data' => [
                'latest_id' => (int) (clone $base)->orderByDesc('id')->value('id'),
                'new_count' => (clone $base)->where('id', '>', $since)->where('sender_id', (int) $tenant->id)->count(),
                'unread' => (clone $base)->unread((int) $owner->id)->count(),
            ],
        ]);
    }

    public function reply(Request $request, User $tenant)
    {
        $owner = $request->user();
        $kosIds = $this->conversationKosIds($owner);

        $this->assertTenantLinked($tenant, $kosIds);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'kos' => ['sometimes', 'nullable', 'integer'],
        ]);

        $kosId = $this->resolveKosId($request, $owner, $tenant, $kosIds);
        abort_unless($kosId, 403, 'Percakapan tidak ditemukan.');

        Message::create([
            'kos_id' => (int) $kosId,
            'owner_id' => $owner->id,
            'sender_id' => $owner->id,
            'receiver_id' => $tenant->id,
            'message' => $validated['message'],
        ]);

        // Balasan ke Admin (komunikasi operasional) vs Tenant (bantuan penghuni)
        if ($tenant->isAdmin()) {
            NotificationService::ownerRepliedToAdmin(
                (int) $tenant->id,
                $owner->name,
                Kos::find($kosId)?->name ?? 'Kos',
                route('admin.owner-messages.show', ['kos' => $kosId, 'owner' => $owner->id])
            );
        } else {
            NotificationService::messageReplied(
                (int) $tenant->id,
                $owner->name,
                route('tenant.messages.index', ['kos' => $kosId])
            );
        }

        return redirect()
            ->route('owner.messages.show', ['tenant' => $tenant, 'kos' => $kosId])
            ->with('success', 'Balasan berhasil dikirim.');
    }

    /**
     * Daftar kos yang memiliki percakapan atas nama owner ini.
     * Sumbernya kolom messages.owner_id, bukan kos.owner_id saat ini.
     */
    private function conversationKosIds(User $owner)
    {
        return Message::forOwner((int) $owner->id)
            ->distinct()
            ->pluck('kos_id')
            ->filter()
            ->values();
    }

    /**
     * Tenant hanya dianggap terkait bila pernah bertukar pesan dalam
     * percakapan milik owner ini (kolom messages.owner_id).
     */
    private function assertTenantLinked(User $tenant, $kosIds): void
    {
        if ($kosIds->isEmpty()) {
            abort(403, 'Tenant tidak terkait dengan kos Anda.');
        }

        $linked = Message::forOwner((int) auth()->id())
            ->between((int) $tenant->id, (int) auth()->id())
            ->exists();

        abort_unless($linked, 403, 'Tenant tidak terkait dengan kos Anda.');
    }

    /**
     * Pilih kos thread: query ?kos= > penghuni aktif tenant di kos owner >
     * thread terbaru. Selalu di-scoped ke percakapan milik owner ini.
     */
    private function resolveKosId(Request $request, User $owner, User $tenant, $kosIds): ?int
    {
        $requested = $request->query('kos') ?: $request->input('kos');

        if ($requested && $kosIds->contains((int) $requested)) {
            $exists = Message::forOwner((int) $owner->id)
                ->where('kos_id', (int) $requested)
                ->between((int) $owner->id, (int) $tenant->id)
                ->exists();

            if ($exists) {
                return (int) $requested;
            }
        }

        $penghuni = Penghuni::where('user_id', $tenant->id)
            ->whereIn('kos_id', $kosIds)
            ->where('status', 'active')
            ->first();

        if ($penghuni) {
            $exists = Message::forOwner((int) $owner->id)
                ->where('kos_id', (int) $penghuni->kos_id)
                ->between((int) $owner->id, (int) $tenant->id)
                ->exists();

            if ($exists) {
                return (int) $penghuni->kos_id;
            }
        }

        $latestKosId = Message::forOwner((int) $owner->id)
            ->between((int) $owner->id, (int) $tenant->id)
            ->latest('created_at')
            ->latest('id')
            ->value('kos_id');

        return $latestKosId ? (int) $latestKosId : null;
    }
}
