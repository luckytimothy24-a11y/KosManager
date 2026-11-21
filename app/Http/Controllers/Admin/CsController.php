<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CsConversation;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CsController extends Controller
{
    /**
     * Inbox bersama Customer Service untuk Admin/Super Admin.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        if (! array_key_exists((string) $status, CsConversation::STATUSES)) {
            $status = null;
        }

        $conversations = CsConversation::with(['tenant', 'assignedAdmin', 'latestMessage'])
            ->withStatus($status)
            ->withCount([
                'messages as unread_count' => fn ($q) => $q->whereNull('read_at')
                    ->whereColumn('sender_id', 'cs_conversations.tenant_id'),
            ])
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.cs.index', compact('conversations', 'status'));
    }

    public function show(Request $request, CsConversation $conversation)
    {
        $this->authorize('view', $conversation);

        $conversation->load(['tenant', 'assignedAdmin']);

        // Pesan dari tenant ditandai terbaca saat admin membuka thread.
        $conversation->messages()
            ->where('sender_id', $conversation->tenant_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('sender')->get();

        return view('admin.cs.show', compact('conversation', 'messages'));
    }

    public function reply(Request $request, CsConversation $conversation)
    {
        $this->authorize('reply', $conversation);

        $admin = $request->user();

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversation->messages()->create([
            'sender_id' => $admin->id,
            'receiver_id' => $conversation->tenant_id,
            'message' => $validated['message'],
        ]);

        $conversation->update([
            'status' => CsConversation::STATUS_IN_PROGRESS,
            'assigned_admin_id' => $conversation->assigned_admin_id ?? $admin->id,
            'resolved_at' => null,
        ]);

        $conversation->touch();

        NotificationService::csReplyToTenant(
            (int) $conversation->tenant_id,
            $admin->name,
            route('tenant.cs.show', $conversation)
        );

        return redirect()
            ->route('admin.cs.show', $conversation)
            ->with('success', 'Balasan Customer Service berhasil dikirim.');
    }

    public function updateStatus(Request $request, CsConversation $conversation)
    {
        $this->authorize('updateStatus', $conversation);

        $admin = $request->user();

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(CsConversation::STATUSES))],
        ]);

        $conversation->update([
            'status' => $validated['status'],
            'assigned_admin_id' => $conversation->assigned_admin_id ?? $admin->id,
            'resolved_at' => $validated['status'] === CsConversation::STATUS_RESOLVED ? now() : null,
        ]);

        return redirect()
            ->route('admin.cs.show', $conversation)
            ->with('success', 'Status percakapan diperbarui.');
    }
}
