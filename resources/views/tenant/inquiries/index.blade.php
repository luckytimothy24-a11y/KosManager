<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pertanyaan Saya" description="Riwayat pertanyaan Anda kepada pemilik kos." />
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        <x-alert />

        @if($threads->isEmpty())
            <div class="card-surface">
                <x-empty-state icon="ri-question-answer-line" title="Belum ada pertanyaan"
                               description="Buka halaman detail kos lalu tekan &quot;Tanya Owner&quot;.">
                    <a href="{{ route('tenant.kos.index') }}" class="btn-primary"><i class="ri-search-eye-line"></i> Cari Kos</a>
                </x-empty-state>
            </div>
        @else
            <x-chat-shell listTitle="Pertanyaan Saya" :listCount="count($threads)" mobile="list">
                <x-slot name="list">
                    <ul class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                        @foreach($threads as $thread)
                            <li>
                                <x-chat-list-item
                                    :href="route('tenant.inquiries.show', $thread['kos'])"
                                    :name="$thread['kos']->name"
                                    :context="$thread['kos']->owner?->name"
                                    :snippet="\Illuminate\Support\Str::limit($thread['last_message'] ?? '', 48)"
                                    :time="$thread['last_at']?->diffForHumans()"
                                    :unread="$thread['unread']" />
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
                        <p class="max-w-xs text-sm leading-relaxed text-slate-500 dark:text-slate-400">Klik salah satu thread untuk membaca balasan owner dan melanjutkan pertanyaan.</p>
                    </div>
                </x-slot>
            </x-chat-shell>
        @endif
    </div>
</x-app-layout>
