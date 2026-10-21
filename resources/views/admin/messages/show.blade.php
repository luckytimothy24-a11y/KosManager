<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.messages.index') }}" class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm lg:hidden" aria-label="Kembali">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center text-white shrink-0">
                <i class="ri-user-3-line text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-[15px] font-bold text-slate-900 dark:text-slate-50 leading-none truncate">{{ $tenant->name }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5"><i class="ri-building-2-line text-slate-400"></i> {{ $kosName }}</p>
            </div>
            <span class="hidden sm:inline-flex ml-auto items-center gap-1 rounded-full bg-slate-200 dark:bg-surface-elevated text-slate-600 dark:text-slate-300 px-2.5 py-1 text-[11px] font-bold"><i class="ri-eye-line"></i> Mode Baca</span>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        <x-alert />

        <div class="flex items-center gap-2 rounded-2xl border border-amber-200 dark:border-amber-500/20 bg-amber-50 dark:bg-amber-500/[0.06] px-4 py-3 text-xs font-semibold text-amber-700 dark:text-amber-300">
            <i class="ri-information-line shrink-0"></i> Anda membaca sebagai auditor — balasan pesan hanya dapat dilakukan oleh owner kos terkait.
        </div>

        <x-chat-shell listTitle="Audit Pesan" mobile="panel">
            <x-slot name="list">
                <div class="px-5 py-4 text-xs text-slate-400 dark:text-slate-500">Gunakan halaman daftar untuk berpindah percakapan.</div>
            </x-slot>
            <x-slot name="panel">
                <div class="min-h-[16rem] flex-1 space-y-3 overflow-y-auto bg-slate-50/30 px-4 py-5 dark:bg-surface-elevated/20 sm:px-5" aria-live="polite">
                    @forelse($messages as $msg)
                        @php $isOwner = (int) $msg->sender_id !== (int) $tenant->id; @endphp
                        <div>
                            <p class="mb-1 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 {{ $isOwner ? 'text-right' : '' }}">{{ $msg->sender->name ?? ($isOwner ? 'Owner' : 'Tenant') }}</p>
                            <x-chat-bubble
                                :mine="$isOwner"
                                :message="$msg->message"
                                :category="$msg->category"
                                :time="$msg->created_at->translatedFormat('d M Y, H:i')"
                                :read="null" />
                        </div>
                    @empty
                        <div class="flex h-full flex-col items-center justify-center gap-3 py-10 text-center">
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-50">Belum ada pesan</p>
                        </div>
                    @endforelse
                </div>
            </x-slot>
        </x-chat-shell>
    </div>
</x-app-layout>
