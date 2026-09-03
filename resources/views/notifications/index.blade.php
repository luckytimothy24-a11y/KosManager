<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Notifikasi" description="Pemberitahuan aktivitas kos Anda." />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ $notifications->total() }} notifikasi
                </p>
                <form method="POST" action="{{ route('notifications.markAllRead') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-500/10 hover:bg-primary-100 dark:hover:bg-primary-500/20 transition">
                        <i class="ri-check-double-line"></i> Tandai Semua Dibaca
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-3">
            @forelse($notifications as $n)
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border p-5 flex flex-col lg:flex-row lg:items-center gap-4 transition-colors {{ $n->is_read ? 'border-slate-100 dark:border-slate-800' : 'border-primary-100 dark:border-primary-500/20' }}">
                    <div class="flex-1 min-w-0 flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center {{ $n->is_read ? 'bg-slate-100 dark:bg-slate-800 text-slate-400' : 'bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400' }}">
                            <i class="{{ $n->is_read ? 'ri-checkbox-circle-line' : 'ri-notification-3-line' }} text-base"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $n->title }}</p>
                                @if(! $n->is_read)
                                    <span class="text-[10px] font-bold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-500/10 px-2 py-0.5 rounded-full uppercase tracking-wider">Baru</span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $n->message }}</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 tracking-wide">{{ $n->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    <div class="shrink-0 lg:text-right lg:pl-6 lg:border-l border-slate-100 dark:border-slate-800">
                        @if(! $n->is_read)
                            <form method="POST" action="{{ route('notifications.read', $n) }}">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                    <i class="ri-check-line"></i> Tandai Dibaca
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-slate-400 dark:text-slate-500">Sudah dibaca</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                    <x-empty-state icon="ri-notification-off-line" title="Belum ada notifikasi"
                                   description="Pemberitahuan aktivitas kos Anda akan tampil di sini." />
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>