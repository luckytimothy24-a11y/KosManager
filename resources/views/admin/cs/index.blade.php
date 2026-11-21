<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Customer Service" description="Inbox bersama tenant — ditangani Admin & Super Admin." icon="ri-question-answer-line">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-500 px-3 py-1.5 text-[11px] font-semibold text-white shadow">
                <i class="ri-inbox-archive-line"></i> Inbox CS
            </span>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="flex flex-wrap items-center gap-2">
            @php
                $tabs = [null => 'Semua'] + \App\Models\CsConversation::STATUSES;
            @endphp
            @foreach($tabs as $key => $label)
                @php $isActiveTab = (string) $status === (string) $key; @endphp
                <a href="{{ route('admin.cs.index', $key ? ['status' => $key] : []) }}"
                   class="inline-flex items-center rounded-full border px-3.5 py-2 text-xs font-bold transition
                          {{ $isActiveTab ? 'border-sky-500 bg-sky-500 text-white shadow-sm shadow-sky-500/30' : 'border-slate-200 bg-white text-slate-600 hover:border-sky-300 hover:text-sky-600 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-300' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-white/10 dark:bg-surface-elevated">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-3.5 dark:border-white/10 dark:bg-surface-elevated/50">
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-50">Daftar Percakapan</h2>
                <span class="inline-flex min-h-6 min-w-6 items-center justify-center rounded-full border border-slate-200 bg-white px-1.5 text-xs font-bold text-slate-700 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-200">{{ $conversations->total() }}</span>
            </div>

            @forelse($conversations as $conversation)
                @php $unread = (int) ($conversation->unread_count ?? 0); @endphp
                <a href="{{ route('admin.cs.show', $conversation) }}"
                   class="group flex items-start gap-3 border-b border-slate-100 px-5 py-4 transition last:border-b-0 hover:bg-slate-50/70 dark:border-slate-800 dark:hover:bg-slate-800/50">
                    <span class="relative grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-slate-900 text-sm font-bold text-white shadow-sm dark:bg-white dark:text-slate-900">
                        {{ mb_substr($conversation->tenant?->name ?? 'T', 0, 1) }}
                        @if($unread > 0)
                            <span class="absolute -top-1 -right-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900">{{ $unread > 99 ? '99+' : $unread }}</span>
                        @endif
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="truncate text-sm font-bold text-slate-900 dark:text-slate-50">{{ $conversation->tenant?->name ?? 'Tenant' }}</h3>
                            <span class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $conversation->statusBadgeClass() }}">{{ $conversation->statusLabel() }}</span>
                        </div>
                        <p class="mt-0.5 truncate text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $conversation->subject ?: 'Bantuan Customer Service' }}</p>
                        <p class="mt-1 truncate text-[13px] text-slate-500 dark:text-slate-400">
                            {{ $conversation->latestMessage ? \Illuminate\Support\Str::limit($conversation->latestMessage->message, 90) : 'Belum ada pesan.' }}
                        </p>
                        <p class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-slate-500 dark:text-slate-500">
                            <span><i class="ri-time-line mr-0.5"></i> {{ $conversation->updated_at->diffForHumans() }}</span>
                            @if($conversation->assignedAdmin)
                                <span>·</span>
                                <span><i class="ri-shield-user-line mr-0.5"></i> {{ $conversation->assignedAdmin->name }}</span>
                            @else
                                <span>·</span>
                                <span class="font-semibold text-amber-600 dark:text-amber-400">Belum ditugaskan</span>
                            @endif
                        </p>
                    </div>

                    <i class="ri-arrow-right-s-line mt-2 shrink-0 text-lg text-slate-300 transition group-hover:text-sky-500 dark:text-slate-600"></i>
                </a>
            @empty
                <x-empty-state
                    title="Tidak ada percakapan"
                    description="Belum ada tenant yang menghubungi Customer Service pada filter ini."
                    icon="ri-inbox-archive-line" />
            @endforelse
        </div>

        @if($conversations->hasPages())
            <div>{{ $conversations->links() }}</div>
        @endif
    </div>
</x-app-layout>
