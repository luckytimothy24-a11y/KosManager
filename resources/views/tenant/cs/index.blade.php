<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Customer Service" description="Bantuan resmi KosManager — terpisah dari chat owner kos." icon="ri-question-answer-line">
            <a href="{{ route('tenant.cs.create') }}" class="btn-primary">
                <i class="ri-add-line"></i> Percakapan Baru
            </a>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6">
        <x-alert />

        <div class="flex items-start gap-3 rounded-2xl border border-sky-100 bg-sky-50/80 p-4 dark:border-sky-500/20 dark:bg-sky-500/10">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-sky-500 text-white shadow-sm shadow-sky-500/30">
                <i class="ri-question-answer-line text-lg"></i>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-bold text-sky-900 dark:text-sky-200">Tim Admin KosManager</p>
                <p class="mt-1 text-xs leading-relaxed text-sky-800/80 dark:text-sky-200/70">
                    Customer Service dijawab oleh Admin/Super Admin KosManager. Untuk urusan kamar, tagihan, atau fasilitas kos tertentu, gunakan
                    <a href="{{ route('tenant.messages.index') }}" class="font-bold underline decoration-sky-400 underline-offset-2">Hubungi Owner</a>.
                </p>
            </div>
        </div>

        @forelse($conversations as $conversation)
            @php
                $unread = (int) ($conversation->unread_count ?? 0);
                $latest = $conversation->latestMessage;
            @endphp
            <a href="{{ route('tenant.cs.show', $conversation) }}"
               class="group block overflow-hidden rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition hover:border-sky-200 hover:shadow-md dark:border-white/10 dark:bg-surface-elevated dark:hover:border-sky-500/30">
                <div class="flex items-start gap-3">
                    <span class="relative grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 text-white shadow-sm shadow-sky-500/30">
                        <i class="ri-question-answer-line text-lg"></i>
                        @if($unread > 0)
                            <span class="absolute -top-1 -right-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900">{{ $unread > 99 ? '99+' : $unread }}</span>
                        @endif
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="truncate text-sm font-bold text-slate-900 dark:text-slate-50">{{ $conversation->subject ?: 'Bantuan Customer Service' }}</h2>
                            <span class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $conversation->statusBadgeClass() }}">{{ $conversation->statusLabel() }}</span>
                        </div>

                        <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                            {{ $latest ? \Illuminate\Support\Str::limit($latest->message, 90) : 'Belum ada pesan.' }}
                        </p>

                        <p class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-slate-500 dark:text-slate-500">
                            <span><i class="ri-time-line mr-0.5"></i> {{ $conversation->updated_at->diffForHumans() }}</span>
                            @if($conversation->assignedAdmin)
                                <span>·</span>
                                <span><i class="ri-shield-user-line mr-0.5"></i> Ditangani {{ $conversation->assignedAdmin->name }}</span>
                            @else
                                <span>·</span>
                                <span>Menunggu tim CS</span>
                            @endif
                        </p>
                    </div>

                    <i class="ri-arrow-right-s-line mt-2 shrink-0 text-lg text-slate-300 transition group-hover:text-sky-500 dark:text-slate-600"></i>
                </div>
            </a>
        @empty
            <div class="card-surface">
                <x-empty-state
                    title="Belum ada percakapan Customer Service"
                    description="Mulai percakapan bila Anda butuh bantuan dari tim Admin KosManager — misalnya kendala akun, pembayaran, atau penggunaan aplikasi."
                    icon="ri-question-answer-line">
                    <a href="{{ route('tenant.cs.create') }}" class="btn-primary">
                        <i class="ri-add-line"></i> Mulai Percakapan
                    </a>
                </x-empty-state>
            </div>
        @endforelse

        @if($conversations->hasPages())
            <div>{{ $conversations->links() }}</div>
        @endif
    </div>
</x-app-layout>
