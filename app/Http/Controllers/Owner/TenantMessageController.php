<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Penghuni;
use App\Services\NotificationService;

class TenantMessageController extends Controller
{
    /**
     * Halaman chat Tenant → Owner. Owner terkait diambil otomatis dari kos
     * tempat tenant berpenghuni aktif — tenant tidak perlu memilih manual.
     */
    public function index()
    {
        $user = auth()->user();

        // Tenant hanya berhak pada chat dengan owner dari kos miliknya.
        // Jika tidak punya penghuni aktif, tampilkan empty state (bukan 403
        // keras) agar tenant baru tetap memahami fitur bantuan ini.
        $penghuni = Penghuni::where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['kos.owner', 'kamar'])
            ->first();

        if (! $penghuni) {
            return view('tenant.messages.index', [
                'penghuni' => null,
                'kos' => null,
                'owner' => null,
                'messages' => collect(),
            ]);
        }

        $kos = $penghuni->kos;
        $owner = $kos->owner;

        $messages = Message::with('sender')
            ->forKos((int) $kos->id)
            ->between((int) $user->id, (int) $owner->id)
            ->latest()
            ->get()
            ->reverse()
            ->values();

        // Tandai pesan masuk dari owner sebagai sudah dibaca.
        Message::forKos((int) $kos->id)
            ->between((int) $user->id, (int) $owner->id)
            ->unread((int) $user->id)
            ->update(['read_at' => now()]);

        return view('tenant.messages.index', compact('penghuni', 'kos', 'owner', 'messages'));
    }

    public function store(StoreMessageRequest $request)
    {
        $user = $request->user();

        $penghuni = Penghuni::where('user_id', $user->id)->where('status', 'active')->first();
        abort_unless($penghuni, 403, 'Anda tidak memiliki hunian aktif.');

        $kos = Kos::whereKey($penghuni->kos_id)->with('owner')->first();
        abort_unless($kos && $kos->status === 'active', 403, 'Kos tidak ditemukan.');

        $owner = $kos->owner;
        abort_unless($owner && $owner->isOwner(), 403, 'Owner kos tidak ditemukan.');

        Message::create([
            'kos_id' => $kos->id,
            'sender_id' => $user->id,
            'receiver_id' => $owner->id,
            'category' => $request->filled('category') ? $request->category : null,
            'message' => $request->message,
            'read_at' => null,
        ]);

        NotificationService::messageSent((int) $owner->id, $user->name, $kos->name, route('owner.messages.show', $user));

        return redirect()->route('tenant.messages.index')
            ->with('success', 'Pesan berhasil dikirim. Owner akan menanggapi melalui halaman chat.');
    }
}
