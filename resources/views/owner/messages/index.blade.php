<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/25 shrink-0">
                <i class="ri-chat-3-line text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-900 dark:text-slate-50 tracking-tight leading-none">Pesan Tenant</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Percakapan dengan penghuni – kamar, tagihan & fasilitas.</p>
            </div>
            @if($totalUnread > 0)
                <span class="hidden sm:inline-flex ml-auto items-center gap-1.5 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-white px-3 py-1.5 text-[11px] font-bold tracking-widest shadow-lg shadow-primary-500/20">{{ $totalUnread }} BARU</span>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        <x-alert />

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-4 py-2 text-xs font-bold"><i class="ri-message-3-line"></i> Pesan Penghuni</span>
            <a href="{{ route('owner.messages.inquiries') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300 px-4 py-2 text-xs font-bold hover:border-sky-300 hover:text-sky-600 transition"> <i class="ri-question-answer-line"></i> Pertanyaan Masuk @if(($sidebarBadges['unreadInquiries'] ?? 0) > 0)<span class="min-w-[20px] h-5 px-1 rounded-full bg-sky-500 text-white text-[10px] font-bold inline-flex items-center justify-center">{{ $sidebarBadges['unreadInquiries'] }}</span>@endif</a>
        </div>

        @if($conversations->isEmpty())
            <div class="card-surface">
                <div class="flex flex-col items-center justify-center gap-4 px-6 py-16 text-center relative overflow-hidden">
                    <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-primary-500/10 to-amber-500/10 blur-2xl pointer-events-none"></div>
                    <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20">
                        <i class="ri-chat-smile-3-line text-3xl"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Belum ada percakapan</h2>
                        <p class="mt-1.5 max-w-sm text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            Ketika penghuni menulis pesan via <b class="text-slate-700 dark:text-slate-200">"Hubungi Owner"</b>, chat-nya akan muncul di sini. Balas cepat agar penghuni merasa dibantu.
                        </p>
                    </div>
                    <a href="{{ route('dashboard') }}" class="mt-2 inline-flex items-center gap-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-4 py-2.5 text-sm font-bold hover:bg-black dark:hover:bg-slate-100 transition">
                        <i class="ri-dashboard-line"></i> Ke Dashboard
                    </a>
                </div>
            </div>
        @else
            <x-chat-shell listTitle="Percakapan" :listCount="count($conversations)" mobile="list">
                <x-slot name="list">
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($conversations as $c)
                            <li>
                                <x-chat-list-item
                                    :href="route('owner.messages.show', array_filter(['tenant' => $c['tenant_id'], 'kos' => $c['kos_id'] ?? null]))"
                                    :name="$c['tenant_name']"
                                    :context="$c['kos_name']"
                                    :snippet="\Illuminate\Support\Str::limit($c['last_message'] ?? '', 48)"
                                    :time="$c['last_at']?->diffForHumans()"
                                    :unread="$c['unread']" />
                            </li>
                        @endforeach
                    </ul>
                </x-slot>
                <x-slot name="panel">
                    <div class="flex flex-1 flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20">
                            <i class="ri-chat-smile-3-line text-3xl"></i>
                        </span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Pilih percakapan</h2>
                        <p class="max-w-xs text-sm leading-relaxed text-slate-500 dark:text-slate-400">Klik salah satu chat di daftar untuk membaca dan membalas pesan penghuni.</p>
                    </div>
                </x-slot>
            </x-chat-shell>
        @endif
    </div>
</x-app-layout>
