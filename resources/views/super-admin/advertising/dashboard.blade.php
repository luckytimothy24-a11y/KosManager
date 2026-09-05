<x-app-layout>
    <div class="space-y-6">
        <x-alert />

        {{-- Header Banner --}}
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-900 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/2 -translate-x-1/4"></div>
            </div>
            <div class="relative">
                <h1 class="text-2xl sm:text-3xl font-black">Dashboard Advertising</h1>
                <p class="mt-2 text-indigo-100/80 text-sm sm:text-base">Pendapatan, aktivitas, dan performa iklan secara keseluruhan.</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('super-admin.advertising.packages.create') }}" class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm text-white text-sm font-bold px-4 py-2.5 rounded-xl hover:bg-white/25 transition">
                        <i class="ri-price-tag-3-line"></i> + Paket Iklan
                    </a>
                    <a href="{{ route('super-admin.advertising.campaigns.index') }}" class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-white/25 transition">
                        <i class="ri-bill-line"></i> Kelola Kampanye
                    </a>
                    <a href="{{ route('super-admin.advertising.revenue') }}" class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-white/25 transition">
                        <i class="ri-line-chart-line"></i> Laporan Revenue
                    </a>
                </div>
            </div>
        </div>

        {{-- Revenue Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-2xl p-5 text-white hover:shadow-lg hover:shadow-indigo-500/25 transition-shadow group">
                <p class="text-sm font-medium text-indigo-100">Total Revenue</p>
                <p class="text-2xl font-bold mt-1">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</p>
                <p class="mt-3 text-xs text-indigo-100/70">{{ $stats['orders'] }} transaksi</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Revenue Bulan Ini</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($stats['revenueMonth'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Revenue Hari Ini</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($stats['revenueToday'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Kampanye Terselesaikan</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['completed'] }}</p>
            </div>
        </div>

        {{-- Campaign Status --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-500/10 flex items-center justify-center">
                        <i class="ri-rocket-2-line text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stats['active'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Aktif</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-yellow-50 dark:bg-yellow-500/10 flex items-center justify-center">
                        <i class="ri-time-line text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stats['pendingReview'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Menunggu Review</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                        <i class="ri-bank-card-line text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stats['pendingPayment'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Belum Bayar</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center">
                        <i class="ri-eye-line text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ number_format($stats['impressions']) }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total Impressions</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Performance --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Clicks</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($stats['clicks']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1"><i class="ri-cursor-line"></i> Klik</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">CTR</p>
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $stats['ctr'] }}%</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Click-through rate</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Konversi (Booking)</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($stats['conversions']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $stats['conversionRate'] }}% dari klik</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Order Berbayar</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($stats['orders']) }}</p>
            </div>
        </div>

        {{-- Recent Campaigns --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Kampanye Terbaru</h2>
                <a href="{{ route('super-admin.advertising.campaigns.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">Lihat semua <i class="ri-arrow-right-s-line"></i></a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/40 text-left">
                        <tr>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kampanye</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Owner</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kos</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Periode</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($recentCampaigns as $c)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3 font-mono text-xs font-bold text-slate-500 dark:text-slate-400">{{ $c->campaign_number }}</td>
                                <td class="px-5 py-3 font-medium text-slate-800 dark:text-slate-200">{{ $c->owner->name }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $c->kos?->name ?? $c->advertiser_name }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold {{ \App\Support\AdvertisingLabels::campaignBadge($c->status) }}">
                                        {{ \App\Support\AdvertisingLabels::campaignLabel($c->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400">{{ $c->starts_at?->format('d/m/Y') }} - {{ $c->ends_at?->format('d/m/Y') }}</td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('super-admin.advertising.campaigns.show', $c) }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center">
                                    <i class="ri-bill-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">Belum ada kampanye.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
