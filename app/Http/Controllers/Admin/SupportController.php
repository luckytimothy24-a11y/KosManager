<?php

namespace App\Http\Controllers\Admin;

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
     * Inbox CS: semua tenant yang pernah menghubungi tim CS.
     */
    public function index(Request $request)
    {
        $admin = $request->user();
        abort_unless($admin->isAdmin() || $admin->isSuperAdmin(), 403);

        $threads = SupportMessage::with(['tenant', 'sender'])
            ->orderByDesc('id')
            ->get()
            ->groupBy('tenant_id')
            ->map(function ($group) {
                $latest = $group->first();
                $tenant = $latest->tenant;

                if (! $tenant) {
                    return null;
                }

                $unreadFromTenant = $group
                    ->where('sender_id', $tenant->id)
                    ->whereNull('read_at')
                    ->count();

                return [
                    'tenant' => $tenant,
                    'tenant_id' => $tenant->id,
                    'last_message' => $latest->message,
                    'last_at' => $latest->created_at,
                    'last_sender_id' => $latest->sender_id,
                    'unread' => $unreadFromTenant,
                ];
            })
            ->filter()
            ->sortByDesc('last_at')
            ->values();

        $totalUnread = SupportMessage::whereColumn('sender_id', 'tenant_id')
            ->whereNull('read_at')
            ->count();

        return view('admin.support.index', compact('threads', 'totalUnread'));
    }

    /**
     * Detail thread CS dengan satu tenant.
     */
    public function show(Request $request, User $tenant)
    {
        $admin = $request->user();
        abort_unless($admin->isAdmin() || $admin->isSuperAdmin(), 403);
        abort_unless($tenant->isTenant(), 404);

        $messages = SupportMessage::with('sender')
            ->forTenant($tenant->id)
            ->orderBy('id')
            ->get();

        // Tandai pesan tenant sudah dibaca oleh tim CS.
        SupportMessage::forTenant($tenant->id)
            ->where('sender_id', $tenant->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.support.show', compact('tenant', 'messages'));
    }

    /**
     * Balas pesan tenant sebagai CS.
     */
    public function store(Request $request, User $tenant)
    {
        $admin = $request->user();
        abort_unless($admin->isAdmin() || $admin->isSuperAdmin(), 403);
        abort_unless($tenant->isTenant(), 404);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $message = SupportMessage::create([
            'tenant_id' => $tenant->id,
            'sender_id' => $admin->id,
            'receiver_id' => $tenant->id,
            'message' => $validated['message'],
            'read_at' => null,
        ]);

        NotificationService::create(
            $tenant->id,
            NotificationType::MESSAGE,
            'Balasan dari CS KosManager',
            'Tim CS membalas: '.Str::limit($validated['message'], 50),
            ['url' => route('tenant.support.index')],
            "support-reply:{$tenant->id}:{$message->id}"
        );

        return redirect()->route('admin.support.show', $tenant->id)
            ->with('success', 'Balasan terkirim.');
    }
}
