<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceItem;
use App\Models\MarketplaceTransaction;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketplaceItem::with(['user', 'kos'])
            ->where('status', 'available');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('q')) {
            $search = addcslashes($request->q, '%_');
            $query->where(function ($w) use ($search) {
                $w->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sort')) {
            match ($request->sort) {
                'termurah' => $query->orderBy('price', 'asc'),
                'termahal' => $query->orderBy('price', 'desc'),
                default => $query->latest(),
            };
        } else {
            $query->latest();
        }

        $items = $query->paginate(12);

        return view('tenant.marketplace.index', compact('items'));
    }

    public function create()
    {
        return view('tenant.marketplace.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'in:baru,bekas,rusak_ringan'],
            'category' => ['required', 'in:elektronik,furniture,pakaian,buku,lainnya'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('marketplace', 'public');
        }

        $item = MarketplaceItem::create([
            'user_id' => $request->user()->id,
            'kos_id' => $request->user()->penghunis()->first()?->kos_id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'condition' => $validated['condition'],
            'category' => $validated['category'],
            'status' => MarketplaceItem::STATUS_AVAILABLE,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('tenant.marketplace.show', $item)
            ->with('success', 'Barang berhasil dijual!');
    }

    public function show(MarketplaceItem $item)
    {
        $item->load(['user', 'kos']);
        return view('tenant.marketplace.show', compact('item'));
    }

    public function chat(MarketplaceItem $item)
    {
        $item->load(['user', 'kos']);
        $buyerId = auth()->id();
        $sellerId = $item->user_id;

        $kosId = $item->kos_id;
        if (!$kosId && $item->user->penghunis()->exists()) {
            $kosId = $item->user->penghunis()->first()->kos_id;
        }

        $messages = \App\Models\Message::with('sender')
            ->where('kos_id', $kosId)
            ->where(function ($q) use ($buyerId, $sellerId) {
                $q->where('sender_id', $buyerId)->where('receiver_id', $sellerId);
                $q->orWhere('sender_id', $sellerId)->where('receiver_id', $buyerId);
            })
            ->orderBy('id')
            ->get();

        return view('tenant.marketplace.chat', compact('item', 'messages', 'kosId', 'sellerId'));
    }

    public function chatSend(Request $request, MarketplaceItem $item)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $kosId = $item->kos_id;
        if (!$kosId && $item->user->penghunis()->exists()) {
            $kosId = $item->user->penghunis()->first()->kos_id;
        }

        abort_unless($kosId, 404, 'Tidak dapat mengirim pesan.');

        \App\Models\Message::create([
            'kos_id' => $kosId,
            'sender_id' => auth()->id(),
            'receiver_id' => $item->user_id,
            'message' => $validated['message'],
            'read_at' => null,
        ]);

        return redirect()->route('tenant.marketplace.chat', $item);
    }

    public function buy(Request $request, MarketplaceItem $item)
    {
        abort_unless($item->isAvailable(), 404, 'Barang tidak tersedia.');

        if ($item->user_id === $request->user()->id) {
            return back()->with('error', 'Anda tidak bisa membeli barang sendiri.');
        }

        $transaction = MarketplaceTransaction::create([
            'item_id' => $item->id,
            'buyer_id' => $request->user()->id,
            'seller_id' => $item->user_id,
            'price' => $item->price,
            'status' => MarketplaceTransaction::STATUS_PENDING,
        ]);

        $item->update(['status' => MarketplaceItem::STATUS_RESERVED]);

        // Notifikasi ke seller
        try {
            \App\Services\NotificationService::create(
                $item->user_id,
                'marketplace_sold',
                'Barang Dibeli',
                "{$request->user()->name} ingin membeli {$item->title} seharga Rp " . number_format($item->price, 0, ',', '.'),
                ['url' => route('tenant.marketplace.show', $item)],
                "marketplace-buy:{$transaction->id}"
            );
        } catch (\Exception $e) {
            \Log::warning("Marketplace buy notification failed: {$e->getMessage()}");
        }

        return redirect()->route('tenant.marketplace.show', $item)
            ->with('success', 'Permintaan pembelian dikirim. Hubungi seller untuk konfirmasi.');
    }

    public function completeTransaction(Request $request, MarketplaceTransaction $transaction)
    {
        abort_unless($transaction->seller_id === auth()->id(), 403);
        abort_unless($transaction->status === MarketplaceTransaction::STATUS_PENDING, 404);

        $transaction->update(['status' => MarketplaceTransaction::STATUS_COMPLETED]);
        $transaction->item->update(['status' => MarketplaceItem::STATUS_SOLD]);

        return back()->with('success', 'Transaksi selesai. Barang ditandai terjual.');
    }

    public function storeReview(Request $request, MarketplaceTransaction $transaction)
    {
        abort_unless(
            $transaction->buyer_id === auth()->id() || $transaction->seller_id === auth()->id(),
            403
        );
        abort_unless($transaction->status === MarketplaceTransaction::STATUS_COMPLETED, 404);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $revieweeId = $transaction->buyer_id === auth()->id()
            ? $transaction->seller_id
            : $transaction->buyer_id;

        \App\Models\MarketplaceReview::create([
            'transaction_id' => $transaction->id,
            'reviewer_id' => auth()->id(),
            'reviewee_id' => $revieweeId,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Review berhasil dikirim.');
    }

    public function report(Request $request, MarketplaceItem $item)
    {
        $validated = $request->validate([
            'reason' => ['required', 'in:spam,penipuan,tidak_sesuai,lainnya'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        \App\Models\MarketplaceReport::create([
            'item_id' => $item->id,
            'reporter_id' => $request->user()->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Laporan berhasil dikirim. Tim kami akan meninjau.');
    }

    public function myItems(Request $request)
    {
        $items = MarketplaceItem::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return view('tenant.marketplace.my-items', compact('items'));
    }

    public function destroy(MarketplaceItem $item)
    {
        abort_unless($item->user_id === auth()->id(), 403);

        if ($item->image_path) {
            \Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return redirect()->route('tenant.marketplace.my-items')
            ->with('success', 'Listing berhasil dihapus.');
    }
}