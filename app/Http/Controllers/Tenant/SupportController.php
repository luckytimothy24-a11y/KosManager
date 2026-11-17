<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\NotificationType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    /**
     * Halaman Bantuan & CS tenant.
     *
     * Satu thread per tenant. Semua pesan tenant ditampilkan tanpa memfilter
     * admin tertentu, sehingga balasan dari CS/admin mana pun tetap terlihat.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $csUser = $this->resolveCs($user->id);

        $messages = SupportMessage::with('sender')
            ->forTenant($user->id)
            ->orderBy('id')
            ->get();

        // Tandai balasan CS yang sudah dibaca tenant.
        SupportMessage::forTenant($user->id)
            ->unread($user->id)
            ->update(['read_at' => now()]);

        return view('tenant.support.index', [
            'csUser' => $csUser,
            'messages' => $messages,
        ]);
    }

    /**
     * Kirim pesan tenant ke CS.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $csUser = $this->resolveCs($user->id);

        if (! $csUser) {
            return back()->with('error', 'Tim CS belum tersedia. Silakan hubungi melalui email resmi.');
        }

        $message = SupportMessage::create([
            'tenant_id' => $user->id,
            'sender_id' => $user->id,
            'receiver_id' => $csUser->id,
            'message' => $validated['message'],
            'read_at' => null,
        ]);

        // Inbox CS shared: beri tahu semua admin/super admin aktif agar pesan
        // tidak hanya terlihat oleh admin pertama yang ter-resolve.
        $recipients = User::whereIn('role', ['admin', 'super_admin'])
            ->where('is_active', true)
            ->get();

        foreach ($recipients as $recipient) {
            NotificationService::create(
                $recipient->id,
                NotificationType::MESSAGE,
                'Pesan Baru dari Tenant',
                "{$user->name} mengirim pesan: ".Str::limit($validated['message'], 50),
                ['url' => route('admin.support.show', $user->id)],
                "support-message:{$message->id}"
            );
        }

        return redirect()->route('tenant.support.index')
            ->with('success', 'Pesan terkirim. Tim CS kami akan segera merespons.');
    }

    /**
     * Polling pesan baru untuk tenant.
     */
    public function latest(Request $request)
    {
        $user = $request->user();
        $since = (int) $request->input('since', 0);

        $base = SupportMessage::forTenant($user->id);

        return response()->json([
            'data' => [
                'latest_id' => (int) ((clone $base)->orderByDesc('id')->value('id') ?? 0),
                'new_count' => (clone $base)->where('id', '>', $since)->where('sender_id', '!=', $user->id)->count(),
                'unread' => (clone $base)->unread($user->id)->count(),
            ],
        ]);
    }

    /**
     * CS yang menangani thread tenant: admin terakhir yang membalas,
     * fallback ke admin/super admin aktif pertama.
     */
    private function resolveCs(int $tenantId): ?User
    {
        $lastReply = SupportMessage::where('tenant_id', $tenantId)
            ->where('sender_id', '!=', $tenantId)
            ->latest('id')
            ->first();

        if ($lastReply?->sender
            && $lastReply->sender->is_active
            && ($lastReply->sender->isAdmin() || $lastReply->sender->isSuperAdmin())) {
            return $lastReply->sender;
        }

        return User::whereIn('role', ['admin', 'super_admin'])
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }
}
