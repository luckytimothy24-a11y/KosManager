<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CsConversation;
use App\Services\NotificationService;
use App\Support\CustomerServiceTeam;
use Illuminate\Http\Request;

class CsController extends Controller
{
    /**
     * Daftar percakapan Customer Service milik tenant.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = CsConversation::forTenant((int) $user->id)
            ->with(['assignedAdmin', 'latestMessage'])
            ->withCount([
                'messages as unread_count' => fn ($q) => $q->whereNull('read_at')->where('sender_id', '!=', $user->id),
            ])
            ->latest('updated_at')
            ->paginate(10);

        return view('tenant.cs.index', compact('conversations'));
    }

    public function create()
    {
        $this->authorize('create', CsConversation::class);

        return view('tenant.cs.create');
    }

    /**
     * Buat percakapan CS baru beserta pesan pertama.
     */
    public function store(Request $request)
    {
        $this->authorize('create', CsConversation::class);

        $user = $request->user();

        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = CsConversation::create([
            'tenant_id' => $user->id,
            'status' => CsConversation::STATUS_OPEN,
            'subject' => $validated['subject'] ?: 'Bantuan Customer Service',
        ]);

        $conversation->messages()->create([
            'sender_id' => $user->id,
            'receiver_id' => null,
            'message' => $validated['message'],
        ]);

        NotificationService::csMessageToAdmins(
            CustomerServiceTeam::adminIds(),
            $user->name,
            $conversation->subject,
            route('admin.cs.show', $conversation)
        );

        return redirect()
            ->route('tenant.cs.show', $conversation)
            ->with('success', 'Pesan Customer Service berhasil dikirim.');
    }

    public function show(Request $request, CsConversation $conversation)
    {
        $this->authorize('view', $conversation);

        $user = $request->user();

        // Pesan dari Admin/Super Admin ditandai terbaca saat tenant membuka thread.
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('sender')->get();

        return view('tenant.cs.show', compact('conversation', 'messages'));
    }

    public function reply(Request $request, CsConversation $conversation)
    {
        $this->authorize('reply', $conversation);

        $user = $request->user();

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversation->messages()->create([
            'sender_id' => $user->id,
            'receiver_id' => $conversation->assigned_admin_id ?: null,
            'message' => $validated['message'],
        ]);

        // Tenant membalas percakapan resolved = buka kembali.
        $conversation->update([
            'status' => $conversation->isResolved() ? CsConversation::STATUS_OPEN : $conversation->status,
            'resolved_at' => null,
        ]);

        $conversation->touch();

        NotificationService::csMessageToAdmins(
            CustomerServiceTeam::recipientIds($conversation),
            $user->name,
            $conversation->subject ?? 'Customer Service',
            route('admin.cs.show', $conversation)
        );

        return redirect()
            ->route('tenant.cs.show', $conversation)
            ->with('success', 'Pesan berhasil dikirim.');
    }
}
