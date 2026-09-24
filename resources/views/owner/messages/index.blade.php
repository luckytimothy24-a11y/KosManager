<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/25 shrink-0">
                <i class="ri-chat-3-line text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-black text-slate-900 dark:text-white tracking-tight leading-none">Pesan Tenant</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Percakapan dengan penghuni — kamar, tagihan & fasilitas.</p>
            </div>
            @if($totalUnread > 0)
                <span class="hidden sm:inline-flex ml-auto items-center gap-1.5 rounded-full bg-gradient-to-br from-orange-500 to-orange-600 text-white px-3 py-1.5 text-[11px] font-black tracking-widest shadow-lg shadow-orange-500/20">{{ $totalUnread }} BARU</span>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6">
        <x-alert />

        @if($conversations->isEmpty())
            <div class="card-surface">
                <div class="flex flex-col items-center justify-center gap-4 px-6 py-16 text-center relative overflow-hidden">
                    <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-orange-500/10 to-amber-500/10 blur-2xl pointer-events-none"></div>
                    <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20">
                        <i class="ri-chat-smile-3-line text-3xl"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">Belum ada percakapan</h2>
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
            <div class="overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-4 dark:border-slate-800 dark:bg-slate-800/50">
                    <h2 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="grid w-8 h-8 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900"><i class="ri-message-3-line"></i></span>
                        Percakapan
                        <span class="inline-flex items-center justify-center min-w-6 h-6 rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 px-1.5 text-xs font-black text-slate-700 dark:text-slate-200">{{ count($conversations) }}</span>
                    </h2>
                    @if($totalUnread > 0)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-br from-orange-500 to-orange-600 px-3 py-1.5 text-[11px] font-black text-white shadow-lg shadow-orange-500/20">
                            <i class="ri-notification-3-line"></i> {{ $totalUnread }} belum dibaca
                        </span>
                    @endif
                </div>

                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($conversations as $c)
                        <li>
                            <a href="{{ route('owner.messages.show', $c['tenant_id']) }}"
                               class="group flex items-center gap-4 px-5 py-4 transition hover:bg-orange-50/60 dark:hover:bg-slate-800/60">
                                <span class="relative grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-black shadow-sm">
                                    {{ mb_substr($c['tenant_name'], 0, 1) }}
                                    @if($c['unread'] > 0)
                                        <span class="absolute -top-1 -right-1 grid h-5 min-w-5 place-items-center rounded-full bg-gradient-to-br from-orange-500 to-orange-600 px-1 text-[10px] font-black text-white shadow">•</span>
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="truncate text-sm font-black text-slate-900 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition">{{ $c['tenant_name'] }}</p>
                                        <span class="shrink-0 text-[11px] font-medium text-slate-400 dark:text-slate-500">{{ $c['last_at']->diffForHumans() }}</span>
                                    </div>
                                    <div class="mt-1 flex items-center justify-between gap-2">
                                        <p class="truncate text-xs {{ $c['unread'] > 0 ? 'font-bold text-slate-800 dark:text-slate-200' : 'text-slate-500 dark:text-slate-400' }}">
                                            @if(!empty($c['last_message']))
                                                {{ \Illuminate\Support\Str::limit($c['last_message'], 48) }}
                                            @else
                                                <span class="italic">Pesan kosong</span>
                                            @endif
                                        </p>
                                        @if($c['unread'] > 0)
                                            <span class="grid h-6 min-w-6 shrink-0 place-items-center rounded-full bg-gradient-to-br from-orange-500 to-orange-600 px-1.5 text-[11px] font-black text-white shadow-lg shadow-orange-500/20">{{ $c['unread'] }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-1.5 flex items-center gap-1.5 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                        <span class="grid w-5 h-5 place-items-center rounded-full bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400"><i class="ri-building-2-line text-xs"></i></span> {{ $c['kos_name'] }}
                                    </p>
                                </div>
                                <span class="shrink-0 grid w-8 h-8 place-items-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 group-hover:bg-orange-500 group-hover:text-white transition"><i class="ri-arrow-right-s-line text-lg"></i></span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="px-5 py-3 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span><i class="ri-information-line mr-1"></i> Ketuk untuk membalas</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ count($conversations) }} percakapan</span>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
