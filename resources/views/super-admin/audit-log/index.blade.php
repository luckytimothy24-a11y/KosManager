<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Activity Log" description="Rekam jejak seluruh aktivitas penting di sistem." />
    </x-slot>

    <div class="space-y-6">
        {{-- Timeline (desktop) --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
            @forelse($logs as $log)
                <article class="flex gap-4 px-5 sm:px-6 py-4 {{ ! $loop->last ? 'border-b border-slate-100 dark:border-slate-800' : '' }}">
                    <div class="flex flex-col items-center shrink-0">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            @php
                                $actionIcon = match(true) {
                                    str_contains(strtolower($log->action), 'delete') => 'ri-delete-bin-line',
                                    str_contains(strtolower($log->action), 'update') => 'ri-edit-line',
                                    str_contains(strtolower($log->action), 'create') => 'ri-add-line',
                                    str_contains(strtolower($log->action), 'login') => 'ri-login-box-line',
                                    str_contains(strtolower($log->action), 'logout') => 'ri-logout-box-line',
                                    default => 'ri-flashlight-line',
                                };
                            @endphp
                            <i class="{{ $actionIcon }} text-slate-500 dark:text-slate-400"></i>
                        </span>
                        @if(! $loop->last)
                            <span class="w-px flex-1 my-1.5 bg-slate-100 dark:bg-slate-800"></span>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1 pb-1">
                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $log->user?->name ?? 'Sistem' }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-300">{{ $log->action }}</span>
                            @if($log->module)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">{{ $log->module }}</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ $log->description }}</p>
                        <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] text-slate-400 dark:text-slate-500">
                            <span title="{{ $log->created_at->format('d M Y H:i:s') }}"><i class="ri-time-line align-[-1px]"></i> {{ $log->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                            @if($log->ip_address)
                                <span><i class="ri-global-line align-[-1px]"></i> {{ $log->ip_address }}</span>
                            @endif
                        </div>

                        @if(is_array($log->timestamp_data) && count($log->timestamp_data))
                            <details class="mt-2 group">
                                <summary class="inline-flex items-center gap-1 text-[11px] font-semibold text-primary-500 hover:text-primary-600 cursor-pointer select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded">
                                    <i class="ri-database-2-line"></i> Metadata
                                    <i class="ri-arrow-down-s-line transition-transform group-open:rotate-180"></i>
                                </summary>
                                <pre class="mt-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-[11px] leading-relaxed text-slate-600 dark:text-slate-300 overflow-x-auto scrollbar-thin">{{ json_encode($log->timestamp_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        @endif
                    </div>
                </article>
            @empty
                <x-empty-state icon="ri-history-line" title="Belum ada aktivitas tercatat" description="Setiap aksi penting pengguna akan muncul di sini." />
            @endforelse

            @if($logs->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
