<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\Message;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

/**
 * Komunikasi operasional Admin ↔ Owner atas kos assigned.
 *
 * REUSE tabel `messages` existing (kos_id, sender_id, receiver_id, message, read_at).
 * Admin ≠ Owner Management — hanya contact + chat operasional.
 * Scope: admin hanya melihat/kirim ke owner dari assignedKos().
 */
class AdminOwnerMessageController extends Controller
{
    /**
     * Daftar Owner dari kos yang ditugaskan kepada Admin.
     * Bukan daftar seluruh owner platform.
     */
    public function index(Request $request)
    {
        $admin = $request->user();
        abort_unless($admin->isAdmin() || $admin->isSuperAdmin(), 403);

        $kosIds = $admin->isSuperAdmin()
            ? Kos::pluck('id')
            : $admin->assignedKos()->pluck('kos.id');

        $contacts = Kos::with('owner')
            ->whereIn('id', $kosIds)
            ->whereNotNull('owner_id')
            ->get()
            ->map(function (Kos $kos) use ($admin) {
                $owner = $kos->owner;
                $unread = Message::where('kos_id', $kos->id)
                    ->where('receiver_id', $admin->id)
                    ->where('sender_id', $owner->id)
                    ->whereNull('read_at')
                    ->count();

                return [
                    'kos' => $kos,
                    'owner' => $owner,
                    'unread' => $unread,
                ];
            })
            ->filter(fn ($c) => $c['owner'] !== null)
            ->values();

        return view('admin.owner-messages.index', compact('contacts'));
    }

    /**
     * Percakapan Admin ↔ Owner pada satu kos assigned.
     * Konteks wajib: kos + owner (server-side dari assignedKos).
     */
    public function show(Request $request, Kos $kos, User $owner)
    {
        $admin = $request->user();
        $this->assertAdminCanContact($admin, $kos, $owner);

        $messages = Message::with('sender', 'receiver')
            ->where('kos_id', $kos->id)
            ->between((int) $admin->id, (int) $owner->id)
            ->orderByDesc('created_at')
            ->get()
            ->reverse()
            ->values();

        // Tandai pesan masuk dari owner sebagai dibaca.
        Message::where('kos_id', $kos->id)
            ->where('sender_id', $owner->id)
            ->where('receiver_id', $admin->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.owner-messages.show', compact('kos', 'owner', 'messages'));
    }

    /**
     * Admin mengirim pesan ke Owner kos assigned.
     * Tidak ada Owner Management — hanya pesan operasional.
     */
    public function store(Request $request, Kos $kos, User $owner)
    {
        $admin = $request->user();
        $this->assertAdminCanContact($admin, $kos, $owner);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'category' => ['sometimes', 'nullable', 'string', 'in:'.implode(',', array_keys(Message::CATEGORIES))],
        ]);

        Message::create([
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
            AuditLogService::create(
                'Pesan',
                "Admin {$admin->name} mengirim pesan operasional ke Owner {$owner->name} ({$kos->name})",
                ['kos_id' => $kos->id, 'sender_id' => $admin->id, 'receiver_id' => $owner->id]
            );
        } catch (\Exception $e) {
            \Log::warning('Admin→Owner message notification/audit failed: '.$e->getMessage());
        }

        return redirect()
            ->route('admin.owner-messages.show', ['kos' => $kos, 'owner' => $owner])
            ->with('success', 'Pesan operasional terkirim ke pemilik kos.');
    }

    /**
     * Validasi assigned-kos scope. IDOR ditolak di server-side.
     */
    private function assertAdminCanContact(User $admin, Kos $kos, User $owner): void
    {
        if (! $admin->isAdmin() && ! $admin->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        if (! $owner->isOwner()) {
            abort(403, 'Tujuan bukan pemilik kos.');
        }

        if ((int) $kos->owner_id !== (int) $owner->id) {
            abort(403, 'Owner tidak terkait dengan kos ini.');
        }

        if ($admin->isSuperAdmin()) {
            return;
        }

        $assigned = $admin->assignedKos()->whereKey($kos->id)->exists();
        abort_unless($assigned, 403, 'Kos tidak berada dalam penugasan Anda.');
    }
}
