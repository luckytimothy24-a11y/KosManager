<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route($prefix.'.penghuni.index'); @endphp
    <x-slot name="header">
        <x-page-header title="{{ $penghuni->user->name }}" description="Detail data diri &amp; riwayat kontrak penghuni.">
            <x-button href="{{ $backUrl }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- Data diri --}}
            <div class="lg:col-span-3">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                    {{-- Hero profil --}}
                    <div class="p-6 sm:p-8 flex flex-wrap items-center gap-4 pb-6">
                        <span class="w-16 h-16 rounded-2xl bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-2xl font-bold shrink-0">{{ strtoupper(substr($penghuni->user->name, 0, 1)) }}</span>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white truncate">{{ $penghuni->user->name }}</h2>
                            <p class="text-sm text-slate-400 dark:text-slate-500 truncate">{{ $penghuni->user->email }}</p>
                            <div class="mt-2"><x-status-badge :status="$penghuni->status" context="penghuni" /></div>
                        </div>
                    </div>

                    <dl class="px-6 sm:px-8 pb-8 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm border-t border-slate-100 dark:border-slate-800 pt-6">
                        <div class="flex items-start gap-2.5">
                            <i class="ri-phone-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div class="min-w-0">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Telepon</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $penghuni->phone ?: '-' }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-id-card-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div class="min-w-0">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">No. Identitas</dt>
                                <dd class="mt-0.5 font-mono font-medium text-slate-700 dark:text-slate-200 break-all">{{ $penghuni->identity_number ?: '-' }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-door-open-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kamar</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $penghuni->kamar->room_number }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-building-2-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kos</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $penghuni->kos->name }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 sm:col-span-2">
                            <i class="ri-calendar-check-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tanggal Masuk</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::parse($penghuni->check_in_date)->translatedFormat('d F Y') }}</dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Kontrak --}}
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden h-full">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <i class="ri-file-text-line text-primary-500"></i> Riwayat Kontrak
                    </h3>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-[26rem] overflow-y-auto scrollbar-thin">
                        @forelse($penghuni->kontraks as $k)
                            <a href="{{ route("$prefix.kontrak.show", $k) }}" class="block px-6 py-4 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300 truncate">{{ $k->contract_number }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($k->start_date)->format('d M Y') }} — {{ \Carbon\Carbon::parse($k->end_date)->format('d M Y') }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($k->rental_price, 0, ',', '.') }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium shrink-0 {{ \StatusLabels::kontrakBadge($k->status) }}">{{ \StatusLabels::kontrakLabel($k->status) }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="py-10 text-center">
                                <span class="mx-auto w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                    <i class="ri-file-text-line text-lg text-slate-300 dark:text-slate-600"></i>
                                </span>
                                <p class="mt-2.5 text-sm text-slate-400 dark:text-slate-500">Belum ada kontrak.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
