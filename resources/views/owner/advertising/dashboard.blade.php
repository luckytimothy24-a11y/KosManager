<x-app-layout>
    <div class="space-y-6">
        <x-alert />

        {{-- Header Banner --}}
        <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-primary-800 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/2 -translate-x-1/4"></div>
            </div>
            <div class="relative">
                <h1 class="text-2xl sm:text-3xl font-black">Dashboard Advertising</h1>
                <p class="mt-2 text-indigo-100/80 text-sm sm:text-base">Promosikan kos Anda atau jangkau penghuni sebagai advertiser partner.</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('owner.advertising.create') }}" class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm text-white text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-white/30 transition">
                        <i class="ri-megaphone-line"></i> + Buat Kampanye
                    </a>
                    <a href="{{ route('owner.advertising.index') }}" class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-white/25 transition">
                        <i class="ri-bill-line"></i> Semua Kampanye
                    </a>
                </div>
            </div>
        </div>

        {{-- Primary Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Spending</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($stats['spending'], 0, ',', '.') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center group-hover:bg-indigo-100 dark:group-hover:bg-indigo-500/20 transition">
                        <i class="ri-money-dollar-circle-line text-2xl text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs font-medium text-slate-400 dark:text-slate-500">Total pembayaran iklan Anda</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Kampanye Aktif</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['active'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-500/10 flex items-center justify-center group-hover:bg-green-100 dark:group-hover:bg-green-500/20 transition">
                        <i class="ri-rocket-2-line text-2xl text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs font-medium text-slate-400 dark:text-slate-500">Sedang tayang</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Menunggu Review</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-yellow-50 dark:bg-yellow-500/10 flex items-center justify-center group-hover:bg-yellow-100 dark:group-hover:bg-yellow-500/20 transition">
                        <i class="ri-time-line text-2xl text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs font-medium text-slate-400 dark:text-slate-500">Menunggu persetujuan admin</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Kampanye</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $totalCampaigns }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-100 dark:group-hover:bg-blue-500/20 transition">
                        <i class="ri-bar-chart-2-line text-2xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
                <a href="{{ route('owner.advertising.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 transition">
                    Lihat semua <i class="ri-arrow-right-s-line"></i>
                </a>
            </div>
        </div>

        {{-- Performance Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Impressions</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($stats['totalImpressions']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1"><i class="ri-eye-line"></i> Tampilan</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Clicks</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($stats['totalClicks']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1"><i class="ri-cursor-line"></i> Klik</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">CTR</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['ctr'] }}%</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Click-through rate</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Booking Conversion</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['conversionRate'] }}%</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Konversi dari klik</p>
            </div>
        </div>

        {{-- Recent Campaigns --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Kampanye Terbaru</h2>
                <a href="{{ route('owner.advertising.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">
                    Lihat semua <i class="ri-arrow-right-s-line"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/40 text-left">
                        <tr>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kampanye</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kos</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Periode</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($recentCampaigns as $c)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3 font-semibold text-slate-900 dark:text-white">{{ $c->campaign_number }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">
                                    {{ $c->displayLabel() }}
                                    @if($c->isThirdParty())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded ml-1 text-[10px] font-bold {{ \App\Support\AdvertisingLabels::placementBadge($c->placement) }}">
                                            {{ \App\Support\AdvertisingLabels::placementLabel($c->placement) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold {{ \App\Support\AdvertisingLabels::campaignBadge($c->status) }}">
                                        {{ \App\Support\AdvertisingLabels::campaignLabel($c->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400">
                                    {{ $c->starts_at?->format('d/m/Y') }} - {{ $c->ends_at?->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('owner.advertising.show', $c) }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center">
                                    <i class="ri-megaphone-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">Belum ada kampanye.</p>
                                    <a href="{{ route('owner.advertising.create') }}" class="mt-3 inline-flex items-center gap-2 text-xs font-bold text-white bg-primary-500 hover:bg-primary-600 px-4 py-2.5 rounded-xl transition">
                                        <i class="ri-add-line"></i> Buat Kampanye
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
