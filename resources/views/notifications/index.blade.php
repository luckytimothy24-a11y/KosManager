<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Notifikasi"
                   description="Pemberitahuan aktivitas kos Anda."
                   icon="ri-notification-3-line" />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="card-surface p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ $notifications->total() }} notifikasi
                </p>
                <form method="POST" action="{{ route('notifications.markAllRead') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-500/10 hover:bg-primary-100 dark:hover:bg-primary-500/20 transition">
                        <i class="ri-check-double-line"></i> Tandai Semua Dibaca
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-3">
@forelse($notifications as $n)
                @php
                    $url = $n->data['url'] ?? null;
                    // Fallback URL berdasarkan tipe notifikasi jika data.url kosong
                    if (empty($url)) {
                        $url = match($n->type) {
                            'booking' => route('tenant.booking.index'),
                            'payment' => route('tenant.pembayaran.index'),
                            'billing',
                            'bill' => route('tenant.tagihan.index'),
                            'message' => route('tenant.messages.index'),
                            'inquiry' => route('tenant.inquiries.index'),
                            'checkout' => route('dashboard'),
                            'checkin' => route('dashboard'),
                            'advertising' => route('super-admin.advertising.dashboard'),
                            default => null,
                        };
                    }
                    $isClickable = !empty($url);
                    $needsMarkRead = !$n->is_read;
                @endphp
                <div class="card-surface p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center gap-3 sm:gap-4 transition-colors {{ $n->is_read ? '' : 'border-primary-100 dark:border-primary-500/20' }} {{ $isClickable ? 'cursor-pointer hover:bg-slate-50/60 dark:hover:bg-white/[0.04] hover:-translate-y-px' : '' }}"
                     @if($needsMarkRead) x-data="{ submitting: false }" @endif
                     @if($isClickable && $n->is_read) @click="if(!submitting) { submitting = true; @if(!$n->is_read) document.querySelector('#mark-read-{{ $n->id }}').submit(); setTimeout(() => window.location.href = '{{ $url }}', 300); @else window.location.href = '{{ $url }}'; @endif }" @endif
                     @if($isClickable && !$n->is_read) @click="if(!submitting) { submitting = true; document.querySelector('#mark-read-{{ $n->id }}').submit(); setTimeout(() => window.location.href = '{{ $url }}', 300); }" @endif>
                    <div class="flex-1 min-w-0 flex items-start gap-3">
                        <x-notification-icon :type="$n->type" size="lg" />
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-50 leading-snug line-clamp-2">{{ $n->title }}</p>
                                @if(! $n->is_read)
                                    <span class="shrink-0 text-[10px] font-semibold text-white bg-primary-600 px-2 py-0.5 rounded-full">Baru</span>
                                @endif
                                <span class="shrink-0 text-[10px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-white/[0.06] px-2 py-0.5 rounded-full">{{ \App\Support\NotificationType::label($n->type) }}</span>
                                @if($isClickable)
                                    <i class="ri-arrow-right-s-line text-slate-300 dark:text-slate-600 shrink-0"></i>
                                @endif
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed line-clamp-2">{{ $n->message }}</p>
                            <p class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500">{{ $n->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    <div class="shrink-0 lg:text-right lg:pl-6 lg:border-l border-slate-100 dark:border-white/[0.08]">
                        @if(! $n->is_read)
                            <form id="mark-read-{{ $n->id }}" method="POST" action="{{ route('notifications.read', $n) }}" class="hidden">
                                @csrf
                            </form>
                            <button type="submit" form="mark-read-{{ $n->id }}"
                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 dark:border-white/[0.08] text-slate-600 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-white/[0.06] transition">
                                <i class="ri-check-line"></i> Tandai Dibaca
                            </button>
                        @else
                            <span class="text-xs text-slate-400 dark:text-slate-500">Sudah dibaca</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="card-surface overflow-hidden">
                    <x-empty-state icon="ri-notification-off-line" title="Belum ada notifikasi"
                                   description="Pemberitahuan aktivitas kos Anda akan tampil di sini." />
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="card-surface p-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>