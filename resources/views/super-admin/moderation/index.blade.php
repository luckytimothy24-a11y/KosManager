<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Moderasi" description="Pengawasan platform — kos, foto, iklan, dan laporan." icon="ri-shield-check-line">
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Metric tiles --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <div class="metric-card text-center">
                <p class="text-2xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400">{{ $pendingKosCount }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Kos Pending</p>
            </div>
            <div class="metric-card text-center">
                <p class="text-2xl font-extrabold tracking-tight text-red-600 dark:text-red-400">{{ $flaggedKosCount }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Kos Flagged</p>
            </div>
            <div class="metric-card text-center">
                <p class="text-2xl font-extrabold tracking-tight text-blue-600 dark:text-blue-400">{{ $pendingAdsCount }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Iklan Pending</p>
            </div>
            <div class="metric-card text-center">
                <p class="text-2xl font-extrabold tracking-tight text-orange-600 dark:text-orange-400">{{ $pendingPhotosCount }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Foto Pending</p>
            </div>
            <div class="metric-card text-center">
                <p class="text-2xl font-extrabold tracking-tight text-purple-600 dark:text-purple-400">{{ $pendingDamageReports }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Laporan Kerusakan</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="card-surface">
            <div class="px-4 sm:px-5 pt-4">
                <nav class="flex flex-wrap gap-1.5" aria-label="Navigasi moderasi">
                    <a href="{{ route('super-admin.moderation.index') }}"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('super-admin.moderation.index') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('super-admin.moderation.kos') }}"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('super-admin.moderation.kos*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                        Kos
                    </a>
                    <a href="{{ route('super-admin.moderation.photos') }}"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('super-admin.moderation.photos') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                        Foto
                    </a>
                    <a href="{{ route('super-admin.moderation.advertising') }}"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('super-admin.moderation.advertising') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                        Iklan
                    </a>
                    <a href="{{ route('super-admin.moderation.damage-reports') }}"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('super-admin.moderation.damage-reports') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                        Laporan
                    </a>
                </nav>
            </div>

            @if(session('moderation_log'))
                <div class="px-4 sm:px-5 pt-4">
                    <x-alert type="success" :message="session('moderation_log')" />
                </div>
            @endif

            <div class="p-4 sm:p-5">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 mb-3">Moderasi Terakhir</h3>
                @if($recentModerations->count() > 0)
                    <div class="space-y-2">
                        @foreach($recentModerations as $kos)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-50/80 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06]">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50 truncate">{{ $kos->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Owner: {{ $kos->owner->name ?? '-' }} · Status: {{ $kos->status }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 shrink-0">
                                    @if(in_array($kos->status, ['pending', 'flagged']))
                                        <form method="POST" action="{{ route('super-admin.moderation.kos.approve', $kos) }}">
                                            @csrf @method('POST')
                                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">Setujui</button>
                                        </form>
                                        <x-confirm-dialog
                                            title="Tolak kos ini?"
                                            description="Apakah Anda yakin ingin menolak {{ $kos->name }}?"
                                            confirmText="Ya, Tolak"
                                            :actions="null"
                                            triggerClass="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition"
                                        >
                                            Tolak
                                            <x-slot name="actions">
                                                <form method="POST" action="{{ route('super-admin.moderation.kos.reject', $kos) }}">
                                                    @csrf @method('POST')
                                                    <button type="submit" class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">Ya, Tolak</button>
                                                </form>
                                            </x-slot>
                                        </x-confirm-dialog>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state icon="ri-shield-check-line" title="Tidak ada moderasi terakhir" description="Aktivitas moderasi akan muncul di sini." />
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
