<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pesan CS" description="Pesan tenant ke tim KosManager — balas langsung dari sini." icon="ri-headphone-line">
            @if($totalUnread > 0)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-600 text-white px-3 py-1.5 text-[11px] font-bold tracking-widest">{{ $totalUnread }} BELUM DIBACA</span>
            @endif
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        <x-alert />

        @if($threads->isEmpty())
            <div class="card-surface">
                <x-empty-state icon="ri-customer-service-2-line" title="Belum ada pesan CS" description="Pesan dari tenant yang menghubungi Bantuan & CS akan muncul di sini." />
            </div>
        @else
            <x-chat-shell listTitle="Pesan Masuk" :listCount="count($threads)" mobile="list">
                <x-slot name="list">
                    <ul class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                        @foreach($threads as $thread)
                            <li>
                                <x-chat-list-item
                                    :href="route('admin.support.show', $thread['tenant_id'])"
                                    :name="$thread['tenant']->name"
                                    context="Bantuan & CS"
                                    :snippet="\Illuminate\Support\Str::limit($thread['last_message'] ?? '', 48)"
                                    :time="$thread['last_at']?->diffForHumans()"
                                    :unread="$thread['unread']"
                                    :badge="$thread['last_sender_id'] === $thread['tenant_id'] ? 'Perlu Balasan' : null" />
                            </li>
                        @endforeach
                    </ul>
                </x-slot>
                <x-slot name="panel">
                    <div class="flex flex-1 flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-primary-100 text-primary-600 dark:bg-primary-500/10 dark:text-primary-300">
                            <i class="ri-customer-service-2-line text-3xl"></i>
                        </span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Pilih percakapan</h2>
                        <p class="max-w-xs text-sm leading-relaxed text-slate-500 dark:text-slate-400">Buka thread tenant untuk membaca pesan dan membalas sebagai CS KosManager.</p>
                    </div>
                </x-slot>
            </x-chat-shell>
        @endif
    </div>
</x-app-layout>
