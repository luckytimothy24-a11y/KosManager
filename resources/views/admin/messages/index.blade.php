<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Audit Pesan" description="Baca percakapan kos assignment — mode baca, tanpa balas." icon="ri-chat-search-line">
            @if($totalUnread > 0)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-600 text-white px-3 py-1.5 text-[11px] font-bold tracking-widest">{{ $totalUnread }} BELUM DIBACA</span>
            @endif
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        <x-alert />

        @if($conversations->isEmpty())
            <div class="card-surface">
                <x-empty-state icon="ri-chat-smile-3-line" title="Belum ada percakapan" description="Percakapan pada kos dalam assignment Anda akan muncul di sini." />
            </div>
        @else
            <x-chat-shell listTitle="Percakapan" :listCount="count($conversations)" mobile="list">
                <x-slot name="list">
                    <ul class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                        @foreach($conversations as $c)
                            <li>
                                <x-chat-list-item
                                    :href="route('admin.messages.show', $c['tenant_id'])"
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
                        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-slate-100 dark:bg-white/[0.06] text-slate-400">
                            <i class="ri-chat-search-line text-3xl"></i>
                        </span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Pilih percakapan</h2>
                        <p class="max-w-xs text-sm leading-relaxed text-slate-500 dark:text-slate-400">Mode baca untuk audit — balasan tetap wewenang owner.</p>
                    </div>
                </x-slot>
            </x-chat-shell>
        @endif
    </div>
</x-app-layout>
