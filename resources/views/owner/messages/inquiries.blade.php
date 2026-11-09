<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-sky-500 to-sky-600 flex items-center justify-center shadow-lg shadow-sky-500/25 shrink-0">
                <i class="ri-question-answer-line text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-900 dark:text-slate-50 tracking-tight leading-none">Pertanyaan Masuk</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pertanyaan calon penghuni — jawab cepat untuk meningkatkan booking.</p>
            </div>
            @if($totalUnread > 0)
                <span class="hidden sm:inline-flex ml-auto items-center gap-1.5 rounded-full bg-gradient-to-br from-sky-500 to-sky-600 text-white px-3 py-1.5 text-[11px] font-bold tracking-widest shadow-lg shadow-sky-500/20">{{ $totalUnread }} BARU</span>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        <x-alert />

        <div class="flex items-center gap-2">
            <a href="{{ route('owner.messages.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300 px-4 py-2 text-xs font-bold hover:border-primary-300 hover:text-primary-600 transition"><i class="ri-message-3-line"></i> Pesan Penghuni</a>
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-4 py-2 text-xs font-bold"><i class="ri-question-answer-line"></i> Pertanyaan Masuk</span>
        </div>

        @if($threads->isEmpty())
            <div class="card-surface">
                <div class="flex flex-col items-center justify-center gap-4 px-6 py-16 text-center relative overflow-hidden">
                    <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/20">
                        <i class="ri-chat-smile-3-line text-3xl"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Belum ada pertanyaan</h2>
                        <p class="mt-1.5 max-w-sm text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            Ketika pencari kos menekan <b class="text-slate-700 dark:text-slate-200">"Tanya Owner"</b> di halaman kos Anda, pertanyaannya akan muncul di sini.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <x-chat-shell listTitle="Pertanyaan" :listCount="count($threads)" mobile="list">
                <x-slot name="list">
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($threads as $t)
                            <li>
                                <x-chat-list-item
                                    :href="route('owner.messages.show', array_filter(['tenant' => $t['tenant_id'], 'kos' => $t['kos_id'] ?? null]))"
                                    :name="$t['tenant_name']"
                                    :context="$t['kos_name']"
                                    :snippet="\Illuminate\Support\Str::limit($t['last_message'] ?? '', 48)"
                                    :time="$t['last_at']?->diffForHumans()"
                                    :unread="$t['unread']"
                                    badge="Calon Penghuni" />
                            </li>
                        @endforeach
                    </ul>
                </x-slot>
                <x-slot name="panel">
                    <div class="flex flex-1 flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/20">
                            <i class="ri-question-answer-line text-3xl"></i>
                        </span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Pilih pertanyaan</h2>
                        <p class="max-w-xs text-sm leading-relaxed text-slate-500 dark:text-slate-400">Klik salah satu pertanyaan di daftar untuk membaca dan membalas calon penghuni.</p>
                    </div>
                </x-slot>
            </x-chat-shell>
        @endif
    </div>
</x-app-layout>
