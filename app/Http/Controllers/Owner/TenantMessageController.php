<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Penghuni;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TenantMessageController extends Controller
{
    /**
     * Inbox chat tenant: semua thread per kos (penghuni aktif + riwayat
     * tanya/kos lain). Thread terpisah per kos — tidak digabung antar owner.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $penghunis = Penghuni::where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['kos.owner', 'kamar'])
            ->get();

        $messagedKosIds = Message::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
        })->pluck('kos_id')->unique()->filter();

        $kosIds = $penghunis->pluck('kos_id')->merge($messagedKosIds)->unique()->filter()->values();

        $threads = collect();
        $kos = null;
        $owner = null;
        $messages = collect();
        $penghuni = null;

        if ($kosIds->isNotEmpty()) {
            $kosList = Kos::with('owner')->whereIn('id', $kosIds)->get()->keyBy('id');

            $messagesByKos = Message::with('sender')
                ->whereIn('kos_id', $kosIds)
                ->where(function ($q) use ($user) {
                    $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
                })
                ->orderBy('id')
                ->get()
                ->groupBy('kos_id');

            foreach ($kosIds as $kosId) {
                $kosItem = $kosList->get($kosId);
                if (! $kosItem || ! $kosItem->owner) {
                    continue;
                }

                $group = ($messagesByKos->get($kosId) ?? collect())
                    ->filter(fn (Message $m) => $this->involvesUserAndOwner($m, $user->id, (int) $kosItem->owner->id));

                $isActiveStay = $penghunis->contains('kos_id', $kosItem->id);
                if ($group->isEmpty() && ! $isActiveStay) {
                    continue;
                }

                $latest = $group->last();

                $threads->push([
                    'kos' => $kosItem,
                    'owner' => $kosItem->owner,
                    'kos_id' => (int) $kosItem->id,
                    'last_message' => $latest?->message,
                    'last_at' => $latest?->created_at,
                    'unread' => $group->filter(fn (Message $m) => (int) $m->receiver_id === (int) $user->id && $m->read_at === null)->count(),
                    'is_active_stay' => $isActiveStay,
                ]);
            }

            $threads = $threads->sortBy([
                fn (array $a, array $b) => ((int) $b['is_active_stay'] <=> (int) $a['is_active_stay']),
                fn (array $a, array $b) => optional($b['last_at'])->timestamp <=> optional($a['last_at'])->timestamp,
            ])->values();

            $requestedKosId = $request->query('kos');
            $activeThread = null;

            if ($requestedKosId) {
                $activeThread = $threads->firstWhere('kos_id', (int) $requestedKosId);
            }

            if (! $activeThread) {
                $activeThread = $threads->firstWhere('is_active_stay', true) ?? $threads->first();
            }

            if ($activeThread) {
                $kos = $activeThread['kos'];
                $owner = $activeThread['owner'];
                $penghuni = $penghunis->firstWhere('kos_id', $kos->id);

                $messages = ($messagesByKos->get($kos->id) ?? collect())
                    ->filter(fn (Message $m) => $this->involvesUserAndOwner($m, $user->id, (int) $owner->id))
                    ->sortBy('id')
                    ->values();

                Message::where('receiver_id', $user->id)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }
        }

        $requestedKosId = $request->query('kos');
        if (! $kos && $requestedKosId) {
            $preKos = Kos::with('owner')->whereKey($requestedKosId)->first();
            if ($preKos && $preKos->status === 'active' && $preKos->owner && $preKos->owner->isOwner() && (int) $preKos->owner->id !== (int) $user->id) {
                $kos = $preKos;
                $owner = $preKos->owner;
                $penghuni = $penghunis->firstWhere('kos_id', $kos->id);
                $messages = Message::with('sender')->forKos((int) $kos->id)->between((int) $user->id, (int) $owner->id)->orderBy('id')->get();
            }
        }

        $preSalesKos = collect();
        if (! $kos) {
            $preSalesKos = Kos::with('owner')->where('status', 'active')->whereHas('owner', fn ($q) => $q->where('role', 'owner'))->where('owner_id', '!=', $user->id)->latest()->take(6)->get();
        }

        return view('tenant.messages.index', [
            'penghuni' => $penghuni,
            'kos' => $kos,
            'owner' => $owner,
            'messages' => $messages,
            'threads' => $threads,
            'preSalesKos' => $preSalesKos,
        ]);
    }

    /**
     * Form "Tanya Owner" untuk pencari kos: tenant login TANPA harus
     * menjadi penghuni aktif. Kos tujuan diambil dari route binding.
     */
    public function ask(Kos $kos)
    {
        abort_unless($kos->status === 'active', 404);

        $owner = $kos->owner;
        abort_unless($owner && $owner->isOwner(), 403, 'Owner kos tidak ditemukan.');

        return view('tenant.messages.ask', compact('kos', 'owner'));
    }

    /**
     * Simpan pertanyaan pencari kos. receiver_id diturunkan server-side
     * dari owner kos — tidak dipercaya dari input client.
     */
    public function askStore(StoreMessageRequest $request, Kos $kos)
    {
        $user = $request->user();

        abort_unless($kos->status === 'active', 404);

        $owner = $kos->owner;
        abort_unless($owner && $owner->isOwner(), 403, 'Owner kos tidak ditemukan.');
        abort_if((int) $owner->id === (int) $user->id, 403);

        Message::create([
            'kos_id' => $kos->id,
            'sender_id' => $user->id,
            'receiver_id' => $owner->id,
            'category' => $request->filled('category') ? $request->category : null,
            'message' => $request->message,
            'read_at' => null,
        ]);

        NotificationService::messageSent(
            (int) $owner->id,
            $user->name,
            $kos->name,
            route('owner.messages.show', ['tenant' => $user, 'kos' => $kos->id])
        );

        return redirect()->route('tenant.inquiries.index')
            ->with('success', 'Pertanyaan berhasil dikirim. Owner akan menanggapi melalui pesan.');
    }

    /**
     * Riwayat pertanyaan saya: thread per kos terhadap owner kos tersebut.
     */
    public function inquiries()
    {
        $user = auth()->user();

        $threads = Message::with(['kos.owner'])
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
            })
            ->latest()
            ->get()
            ->groupBy('kos_id')
            ->map(function ($group) use ($user) {
                $latest = $group->first();
                $kos = $latest->kos;
                $otherId = (int) $latest->sender_id === (int) $user->id
                    ? (int) $latest->receiver_id
                    : (int) $latest->sender_id;

                return [
                    'kos' => $kos,
                    'other_id' => $otherId,
                    'last_message' => $latest->message,
                    'last_at' => $latest->created_at,
                    'unread' => $group->filter(fn ($m) => (int) $m->receiver_id === (int) $user->id && $m->read_at === null)->count(),
                ];
            })
            ->filter(fn ($t) => $t['kos'] !== null)
            ->sortByDesc('last_at')
            ->values();

        $kosIds = $threads->map(fn ($t) => (int) $t['kos']->id)->all();
        if (! empty($kosIds)) {
            Message::whereIn('kos_id', $kosIds)
                ->unread((int) $user->id)
                ->update(['read_at' => now()]);
        }

        return view('tenant.inquiries.index', compact('threads'));
    }

    /**
     * Thread satu kos: riwayat tanya-jawab pencari kos dengan owner kos
     * tersebut + form balasan lanjutan (reuse askStore).
     */
    public function inquiryShow(Kos $kos)
    {
        $user = auth()->user();

        abort_unless($kos->status === 'active', 404);

        $owner = $kos->owner;
        abort_unless($owner && $owner->isOwner(), 403, 'Owner kos tidak ditemukan.');

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

        $ownerReplyIds = Message::forKos((int) $kos->id)
            ->where('sender_id', (int) $owner->id)
            ->pluck('id');
        $editableIds = $messages
            ->filter(fn ($m) => (int) $m->sender_id === (int) $user->id
                && ! $ownerReplyIds->contains(fn ($id) => $id > $m->id))
            ->map(fn ($m) => $m->id)
            ->all();

        $threads = Message::with(['kos.owner'])
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
            })
            ->latest()
            ->get()
            ->groupBy('kos_id')
            ->map(function ($group) use ($user) {
                $latest = $group->first();
                $kosItem = $latest->kos;

                return [
                    'kos' => $kosItem,
                    'last_message' => $latest->message,
                    'last_at' => $latest->created_at,
                    'unread' => $group->filter(fn ($m) => (int) $m->receiver_id === (int) $user->id && $m->read_at === null)->count(),
                ];
            })
            ->filter(fn ($t) => $t['kos'] !== null)
            ->sortByDesc('last_at')
            ->values();

        return view('tenant.inquiries.show', compact('kos', 'owner', 'messages', 'threads', 'editableIds'));
    }

    /**
     * Ringkasan thread untuk polling — per kos aktif (query ?kos=).
     */
    public function latest(Request $request)
    {
        $user = $request->user();
        $kosId = $request->query('kos');

        if ($kosId) {
            $kos = Kos::whereKey($kosId)->with('owner')->first();
            abort_unless($kos && $kos->status === 'active' && $kos->owner && $kos->owner->isOwner(), 403);

            $owner = $kos->owner;
            abort_unless($this->canChatWithKos($user, $kos, $owner), 403);
        } else {
            $penghuni = Penghuni::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            abort_unless($penghuni, 403);

            $kos = Kos::whereKey($penghuni->kos_id)->with('owner')->first();
            abort_unless($kos && $kos->status === 'active', 403);

            $owner = $kos->owner;
            abort_unless($owner && $owner->isOwner(), 403);
        }

        $since = (int) request()->input('since', 0);
        $base = Message::forKos((int) $kos->id)
            ->between((int) $user->id, (int) $owner->id);

        return response()->json([
            'data' => [
                'latest_id' => (int) (clone $base)->orderByDesc('id')->value('id'),
                'new_count' => (clone $base)->where('id', '>', $since)->where('sender_id', (int) $owner->id)->count(),
                'unread' => (clone $base)->unread((int) $user->id)->count(),
            ],
        ]);
    }

    /**
     * Edit pertanyaan milik sendiri selama owner belum membalas
     * sesudahnya pada thread kos yang sama.
     */
    public function inquiryUpdate(Request $request, Message $message)
    {
        $user = $request->user();
        $this->authorize('update', $message);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'category' => ['sometimes', 'nullable', 'string', 'in:'.implode(',', array_keys(Message::CATEGORIES))],
        ]);

        $replied = Message::where('kos_id', $message->kos_id)
            ->where('sender_id', $message->receiver_id)
            ->where('id', '>', $message->id)
            ->exists();
        abort_if($replied, 422, 'Pertanyaan sudah dibalas owner sehingga tidak dapat diubah.');

        $message->update([
            'message' => $validated['message'],
            'category' => $validated['category'] ?? $message->category,
        ]);

        return redirect()->route('tenant.inquiries.show', $message->kos_id)
            ->with('success', 'Pertanyaan berhasil diubah.');
    }

    public function store(StoreMessageRequest $request)
    {
        $user = $request->user();
        $kosId = $request->input('kos_id');

        if ($kosId) {
            $kos = Kos::whereKey($kosId)->with('owner')->first();
            abort_unless($kos && $kos->status === 'active', 403, 'Kos tidak ditemukan.');

            $owner = $kos->owner;
            abort_unless($owner && $owner->isOwner(), 403, 'Owner kos tidak ditemukan.');
            abort_unless($this->canChatWithKos($user, $kos, $owner), 403, 'Anda tidak memiliki akses chat ke kos ini.');
        } else {
            // BC: fallback ke penghuni aktif pertama (tanpa pemilihan kos).
            $penghuni = Penghuni::where('user_id', $user->id)->where('status', 'active')->first();
            abort_unless($penghuni, 403, 'Anda tidak memiliki hunian aktif.');

            $kos = Kos::whereKey($penghuni->kos_id)->with('owner')->first();
            abort_unless($kos && $kos->status === 'active', 403, 'Kos tidak ditemukan.');

            $owner = $kos->owner;
            abort_unless($owner && $owner->isOwner(), 403, 'Owner kos tidak ditemukan.');
        }

        Message::create([
            'kos_id' => $kos->id,
            'sender_id' => $user->id,
            'receiver_id' => $owner->id,
            'category' => $request->filled('category') ? $request->category : null,
            'message' => $request->message,
            'read_at' => null,
        ]);

        NotificationService::messageSent(
            (int) $owner->id,
            $user->name,
            $kos->name,
            route('owner.messages.show', ['tenant' => $user, 'kos' => $kos->id])
        );

        return redirect()->route('tenant.messages.index', ['kos' => $kos->id])
            ->with('success', 'Pesan berhasil dikirim. Owner akan menanggapi melalui halaman chat.');
    }

    private function involvesUserAndOwner(Message $message, int $userId, int $ownerId): bool
    {
        return ((int) $message->sender_id === $userId && (int) $message->receiver_id === $ownerId)
            || ((int) $message->sender_id === $ownerId && (int) $message->receiver_id === $userId);
    }

    /**
     * Pre-sales: tenant boleh chat ke kos aktif dengan owner valid,
     * tanpa harus penghuni aktif atau riwayat pesan. Validasi kos aktif
     * + owner valid tetap wajib (caller memverifikasi sebelum invoke).
     */
    private function canChatWithKos($user, Kos $kos, $owner): bool
    {
        $isPenghuni = Penghuni::where('user_id', $user->id)
            ->where('kos_id', $kos->id)
            ->where('status', 'active')
            ->exists();

        if ($isPenghuni) {
            return true;
        }

        if (Message::where('kos_id', $kos->id)
            ->between((int) $user->id, (int) $owner->id)
            ->exists()) {
            return true;
        }

        return $kos->status === 'active'
            && $owner && $owner->isOwner()
            && (int) $owner->id !== (int) $user->id;
    }
}
