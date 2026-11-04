<x-app-layout>
    <x-slot name="header">
        <x-page-header title="{{ $item->title }}" description="Detail barang marketplace." />
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 py-6">
        <a href="{{ route('tenant.marketplace.index') }}" class="inline-flex items-center gap-1 text-sm text-primary-600 dark:text-primary-400 hover:underline mb-4">
            <i class="ri-arrow-left-line"></i> Kembali ke Marketplace
        </a>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            {{-- Image --}}
            <div class="md:col-span-3">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                    @if($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}" class="w-full aspect-[4/3] object-cover">
                    @else
                        <div class="aspect-[4/3] bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center">
                            <i class="ri-image-line text-7xl text-slate-300 dark:text-slate-600"></i>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Details --}}
            <div class="md:col-span-2 space-y-4">
                {{-- Status & Condition badges --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold
                        @if($item->status === 'available') bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300
                        @elseif($item->status === 'reserved') bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300
                        @else bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300
                        @endif">
                        <span class="w-1.5 h-1.5 rounded-full
                            @if($item->status === 'available') bg-emerald-500
                            @elseif($item->status === 'reserved') bg-amber-500
                            @else bg-slate-400
                            @endif"></span>
                        {{ ucfirst($item->status) }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold
                        @if($item->condition === 'baru') bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300
                        @elseif($item->condition === 'bekas') bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300
                        @else bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $item->condition)) }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        {{ ucfirst($item->category) }}
                    </span>
                </div>

                {{-- Title & Price --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-5">
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white leading-snug">{{ $item->title }}</h1>
                    <p class="text-3xl font-extrabold bg-gradient-to-r from-primary-600 to-indigo-600 dark:from-primary-400 dark:to-indigo-400 bg-clip-text text-transparent mt-2">
                        Rp {{ number_format($item->price, 0, ',', '.') }}
                    </p>
                </div>

                {{-- Description --}}
                @if($item->description)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-5">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Deskripsi</h3>
                        <p class="text-slate-600 dark:text-slate-300 leading-relaxed text-sm">{{ $item->description }}</p>
                    </div>
                @endif

                {{-- Reviews --}}
                @php
                    $reviews = \App\Models\MarketplaceReview::whereHas('transaction', fn($q) => $q->where('item_id', $item->id))->with('reviewer')->latest()->get();
                    $avgRating = $reviews->avg('rating');
                @endphp
                @if($reviews->isNotEmpty())
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-5">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">
                            Review ({{ $reviews->count() }}) · ⭐ {{ number_format($avgRating, 1) }}
                        </h3>
                        <div class="space-y-3">
                            @foreach($reviews as $review)
                                <div class="border-b border-slate-100 dark:border-white/[0.08] pb-3 last:border-0 last:pb-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-white text-xs font-bold flex items-center justify-center">
                                            {{ mb_substr($review->reviewer->name ?? '?', 0, 1) }}
                                        </div>
                                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $review->reviewer->name ?? 'N/A' }}</span>
                                        <span class="text-amber-500 text-xs">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                    </div>
                                    @if($review->comment)
                                        <p class="text-sm text-slate-600 dark:text-slate-300 ml-9">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-5">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">Penjual</h3>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-indigo-500 text-white text-sm font-bold flex items-center justify-center">
                            {{ mb_substr($item->user->name ?? '?', 0, 1) }}
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $item->user->name ?? 'N/A' }}</p>
                            @if($item->kos)
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->kos->name }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-400 dark:text-slate-500">
                        <i class="ri-calendar-line"></i> Diposting {{ $item->created_at->format('d M Y') }}
                    </div>
                </div>

                {{-- Reviews --}}
                @php
                    $reviews = \App\Models\MarketplaceReview::whereHas('transaction', fn($q) => $q->where('item_id', $item->id))->with('reviewer')->latest()->get();
                    $avgRating = $reviews->avg('rating');
                @endphp
                @if($reviews->isNotEmpty())
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-5">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">
                            Review ({{ $reviews->count() }}) · ⭐ {{ number_format($avgRating, 1) }}
                        </h3>
                        <div class="space-y-3">
                            @foreach($reviews as $review)
                                <div class="border-b border-slate-100 dark:border-white/[0.08] pb-3 last:border-0 last:pb-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-white text-xs font-bold flex items-center justify-center">
                                            {{ mb_substr($review->reviewer->name ?? '?', 0, 1) }}
                                        </div>
                                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $review->reviewer->name ?? 'N/A' }}</span>
                                        <span class="text-amber-500 text-xs">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                    </div>
                                    @if($review->comment)
                                        <p class="text-sm text-slate-600 dark:text-slate-300 ml-9">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Review form --}}
                @if($item->status === 'sold')
                    @php
                        $userTx = $item->transactions()->where('status', 'completed')
                            ->where(function($q) { $q->where('buyer_id', auth()->id())->orWhere('seller_id', auth()->id()); })
                            ->first();
                        $hasReviewed = $userTx ? \App\Models\MarketplaceReview::where('transaction_id', $userTx->id)->where('reviewer_id', auth()->id())->exists() : false;
                    @endphp
                    @if($userTx && !$hasReviewed)
                        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-5">
                            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">Beri Review</h3>
                            <form method="POST" action="{{ route('tenant.marketplace.review', $userTx) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="field-label">Rating</label>
                                    <select name="rating" class="field-input" required>
                                        <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                                        <option value="4">⭐⭐⭐⭐ (4)</option>
                                        <option value="3">⭐⭐⭐ (3)</option>
                                        <option value="2">⭐⭐ (2)</option>
                                        <option value="1">⭐ (1)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="field-label">Komentar (opsional)</label>
                                    <textarea name="comment" class="field-input" rows="2" maxlength="500"></textarea>
                                </div>
                                <button type="submit" class="btn-primary text-sm">
                                    <i class="ri-star-fill"></i> Kirim Review
                                </button>
                            </form>
                        </div>
                    @endif
                @endif

                {{-- Actions --}}
                <div class="flex flex-col gap-3">
                    @if($item->isAvailable() && $item->user_id !== auth()->id())
                        <form method="POST" action="{{ route('tenant.marketplace.buy', $item) }}">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white text-sm font-bold px-6 py-3.5 shadow-lg shadow-primary-500/30 transition-all duration-200 active:scale-95">
                                <i class="ri-hand-coin-line text-lg"></i> Beli Barang Ini
                            </button>
                        </form>
                    @elseif($item->isAvailable() && $item->user_id === auth()->id())
                        <div class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-sm font-bold px-6 py-3.5 cursor-not-allowed">
                            <i class="ri-hand-coin-line text-lg"></i> Barang Milik Anda
                        </div>
                    @endif

                    @if($item->status === 'reserved' && $item->user_id === auth()->id())
                        @php
                            $pendingTx = $item->transactions()->where('status', 'pending')->first();
                        @endphp
                        @if($pendingTx)
                            <form method="POST" action="{{ route('tenant.marketplace.complete', $pendingTx) }}">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white text-sm font-bold px-6 py-3.5 shadow-lg shadow-emerald-500/30 transition-all duration-200 active:scale-95">
                                    <i class="ri-check-line text-lg"></i> Tandai Selesai (Barang Terjual)
                                </button>
                            </form>
                        @endif
                    @endif

                    @if($item->user_id === auth()->id())
                        <form method="POST" action="{{ route('tenant.marketplace.destroy', $item) }}" onsubmit="return confirm('Hapus listing ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border border-red-200 dark:border-red-500/30 text-red-600 dark:text-red-400 text-sm font-bold px-6 py-3 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all duration-200">
                                <i class="ri-delete-bin-line"></i> Hapus Listing
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('tenant.marketplace.chat', $item) }}" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-bold px-6 py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all duration-200">
                        <i class="ri-chat-3-line text-lg"></i> Chat Penjual
                    </a>

                    {{-- Report button --}}
                    <button type="button" onclick="document.getElementById('report-modal').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500 hover:text-red-500 transition mt-2">
                        <i class="ri-flag-line"></i> Laporkan Barang
                    </button>
                </div>

                    {{-- Report button --}}
                    <button type="button" onclick="document.getElementById('report-modal').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500 hover:text-red-500 transition mt-2">
                        <i class="ri-flag-line"></i> Laporkan Barang
                    </button>
                </div>

                {{-- Report Modal --}}
                <div id="report-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Laporkan Barang</h3>
                        <form method="POST" action="{{ route('tenant.marketplace.report', $item) }}">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label class="field-label">Alasan</label>
                                    <select name="reason" class="field-input" required>
                                        <option value="spam">Spam</option>
                                        <option value="penipuan">Penipuan</option>
                                        <option value="tidak_sesuai">Tidak Sesuai</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="field-label">Deskripsi (opsional)</label>
                                    <textarea name="description" class="field-input" rows="2" maxlength="500"></textarea>
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-4">
                                <button type="button" onclick="document.getElementById('report-modal').classList.add('hidden')" class="btn-secondary">Batal</button>
                                <button type="submit" class="btn-primary">Kirim Laporan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>