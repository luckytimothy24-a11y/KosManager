<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Notifikasi"
                       description="Pemberitahuan aktivitas kos Anda."
                       icon="ri-notification-3-line" />
    </x-slot>

    <div class="space-y-6" x-data="notificationsPage()">
        <!-- Toast container -->
        <div x-show="toast.visible" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" class="fixed bottom-4 right-4 z-50" style="display: none;">
            <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 shadow-xl shadow-slate-900/30 min-w-[280px] max-w-sm" :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-red-600'">
                <i :class="toast.type === 'success' ? 'ri-check-line text-xl' : 'ri-error-warning-line text-xl'"></i>
                <span x-text="toast.message" class="text-sm font-medium"></span>
            </div>
        </div>

        <x-alert />

        <div class="card-surface p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ $notifications->total() }} notifikasi
                </p>
                <form method="POST" action="{{ route('notifications.markAllRead') }}" @submit.prevent="markAllRead($event)">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-500/10 hover:bg-primary-100 dark:hover:bg-primary-500/20 transition"
                            :disabled="markAllLoading">
                        <i class="ri-check-double-line"></i>
                        <span x-show="!markAllLoading">Tandai Semua Dibaca</span>
                        <span x-show="markAllLoading" class="flex items-center gap-1.5">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Filter tabs -->
        <div class="flex flex-wrap gap-2 mb-4" role="tablist" aria-label="Filter notifikasi">
            @php
                $filters = [
                    '' => 'Semua',
                    'unread' => 'Belum dibaca',
                    'booking' => 'Booking',
                    'billing' => 'Tagihan',
                    'payment' => 'Pembayaran',
                    'message' => 'Pesan',
                    'kontrak' => 'Kontrak',
                    'checkin' => 'Check-In',
                    'checkout' => 'Check-Out',
                    'damage_report' => 'Lapor Kerusakan',
                    'advertising' => 'Advertising',
                    'identity' => 'Identitas',
                ];
                $currentFilter = request('filter', '');
            @endphp
            @foreach($filters as $key => $label)
                <a href="{{ request()->url() }}?filter={{ $key }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-medium transition
                          {{ $currentFilter === $key
                              ? 'bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400'
                              : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                   role="tab"
                   :aria-selected="{{ $currentFilter === $key ? 'true' : 'false' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Grouped notifications -->
        @php
            // Group notifications by date for display
            $groups = [];
            foreach ($notifications as $n) {
                $dateKey = $n->created_at->startOfDay();
                $label = match (true) {
                    $dateKey->isToday() => 'Hari ini',
                    $dateKey->isYesterday() => 'Kemarin',
                    $dateKey->isSameWeek(now()) => 'Minggu ini',
                    default => $dateKey->translatedFormat('l, d F Y'),
                };
                $groups[$label][$dateKey->format('Y-m-d')][] = $n;
            }
        @endphp

        @forelse($groups as $dateLabel => $dayGroups)
            <div class="space-y-3">
                <h3 class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider px-1">
                    {{ $dateLabel }}
                </h3>
                @foreach($dayGroups as $day => $items)
                    @foreach($items as $n)
                        @php
                            $url = \App\Support\NotificationLink::resolve($n, auth()->user());
                            $isClickable = !empty($url);
                        @endphp
                        <div class="card-surface p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center gap-3 sm:gap-4 transition-colors {{ $n->is_read ? '' : 'border-primary-100 dark:border-primary-500/20' }} {{ $isClickable ? 'cursor-pointer hover:bg-slate-50/60 dark:hover:bg-white/[0.04] hover:-translate-y-px' : '' }}"
                             @if($isClickable) @click="if (!$event.target.closest('button, a, form')) document.getElementById('open-{{ $n->id }}').requestSubmit()" @endif
                             x-show="filter === '{{ $currentFilter }}' || filter === '' || (filter === 'unread' && !$n->is_read) || filter === '{{ $n->type }}'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95">
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
                                @if($isClickable)
                                    {{-- Dipakai saat kartu diketuk: tandai dibaca sekaligus pindah halaman. --}}
                                    <form id="open-{{ $n->id }}" method="POST" action="{{ route('notifications.read', $n) }}" class="hidden">
                                        @csrf
                                        <input type="hidden" name="redirect_to_target" value="1">
                                    </form>
                                @endif
                                @if(! $n->is_read)
                                    {{-- Tombol ini hanya menandai dibaca, tetap di halaman notifikasi. --}}
                                    <form id="mark-read-{{ $n->id }}" method="POST" action="{{ route('notifications.read', $n) }}">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 dark:border-white/[0.08] text-slate-600 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-white/[0.06] transition">
                                            <i class="ri-check-line"></i> Tandai Dibaca
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500">Sudah dibaca</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        @empty
            <div class="card-surface overflow-hidden">
                <x-empty-state icon="ri-notification-off-line" title="Belum ada notifikasi"
                               description="Pemberitahuan aktivitas kos Anda akan tampil di sini." />
            </div>
        @endforelse

        @if($notifications->hasPages())
            <div class="card-surface p-4">
                {{ $notifications->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <script>
        function notificationsPage() {
            return {
                toast: { visible: false, message: '', type: 'success' },
                markAllLoading: false,
                filter: '{{ $currentFilter }}',

                showToast(message, type = 'success') {
                    this.toast = { visible: true, message, type };
                    setTimeout(() => this.toast.visible = false, 3000);
                },

                async markAllRead(event) {
                    event.preventDefault();
                    this.markAllLoading = true;
                    const form = event.target;
                    const formData = new FormData(form);

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': formData.get('_token'),
                                'Accept': 'application/json',
                            },
                        });

                        if (response.redirected) {
                            window.location.href = response.url;
                            return;
                        }

                        const data = await response.json();
                        if (data.success || response.ok) {
                            this.showToast('Semua notifikasi ditandai sudah dibaca', 'success');
                            // Reload page to reflect changes
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            this.showToast(data.message || 'Gagal menandai semua dibaca', 'error');
                        }
                    } catch (e) {
                        this.showToast('Terjadi kesalahan', 'error');
                    } finally {
                        this.markAllLoading = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>