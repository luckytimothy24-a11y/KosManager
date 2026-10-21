<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard Advertising" description="Pendapatan, aktivitas, dan performa iklan secara keseluruhan." icon="ri-megaphone-line">
            <x-button href="{{ route('super-admin.advertising.packages.create') }}" type="primary">
                <i class="ri-price-tag-3-line"></i> + Paket Iklan
            </x-button>
            <x-button href="{{ route('super-admin.advertising.campaigns.index') }}" type="secondary">
                <i class="ri-bill-line"></i> Kelola Kampanye
            </x-button>
            <x-button href="{{ route('super-admin.advertising.revenue') }}" type="secondary">
                <i class="ri-line-chart-line"></i> Laporan Revenue
            </x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Revenue Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="metric-card">
                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Total Revenue</p>
                <p class="text-metric mt-1 text-slate-900 dark:text-slate-50">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</p>
                <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">{{ $stats['orders'] }} transaksi</p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Revenue Bulan Ini</p>
                <p class="text-metric mt-1 text-slate-900 dark:text-slate-50">Rp {{ number_format($stats['revenueMonth'], 0, ',', '.') }}</p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Revenue Hari Ini</p>
                <p class="text-metric mt-1 text-slate-900 dark:text-slate-50">Rp {{ number_format($stats['revenueToday'], 0, ',', '.') }}</p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Kampanye Terselesaikan</p>
                <p class="text-metric mt-1 text-slate-900 dark:text-slate-50">{{ $stats['completed'] }}</p>
            </div>
        </div>

        {{-- Campaign Status --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="metric-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-500/10 flex items-center justify-center">
                        <i class="ri-rocket-2-line text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-50">{{ $stats['active'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Aktif</p>
                    </div>
                </div>
            </div>
            <div class="metric-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-yellow-50 dark:bg-yellow-500/10 flex items-center justify-center">
                        <i class="ri-time-line text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-50">{{ $stats['pendingReview'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Menunggu Review</p>
                    </div>
                </div>
            </div>
            <div class="metric-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                        <i class="ri-bank-card-line text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-50">{{ $stats['pendingPayment'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Belum Bayar</p>
                    </div>
                </div>
            </div>
            <div class="metric-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                        <i class="ri-eye-line text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-50">{{ number_format($stats['impressions']) }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total Impressions</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Performance --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="metric-card">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Total Clicks</p>
                <p class="text-metric text-slate-900 dark:text-slate-50 mt-1">{{ number_format($stats['clicks']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1"><i class="ri-cursor-line"></i> Klik</p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">CTR</p>
                <p class="text-metric text-primary-600 dark:text-primary-400 mt-1">{{ $stats['ctr'] }}%</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Click-through rate</p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Konversi (Booking)</p>
                <p class="text-metric text-green-600 dark:text-green-400 mt-1">{{ number_format($stats['conversions']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $stats['conversionRate'] }}% dari klik</p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Order Berbayar</p>
                <p class="text-metric text-slate-900 dark:text-slate-50 mt-1">{{ number_format($stats['orders']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                    <span class="text-amber-600 dark:text-amber-400">{{ $stats['pendingOrders'] }} pending</span>
                    ·
                    <span class="text-slate-500 dark:text-slate-400">{{ $stats['refundedOrders'] }} refunded</span>
                </p>
            </div>
        </div>

        {{-- Recent Campaigns --}}
        <div class="table-card">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
                <h2 class="text-section-title">Kampanye Terbaru</h2>
                <a href="{{ route('super-admin.advertising.campaigns.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">Lihat semua <i class="ri-arrow-right-s-line"></i></a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>Kampanye</th>
                            <th>Owner</th>
                            <th>Kos</th>
                            <th>Status</th>
                            <th>Periode</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCampaigns as $c)
                            @php
                                $thumbPath = $c->displayImage() ?: ($c->kos ? (method_exists($c->kos, 'coverPath') ? $c->kos->coverPath() : $c->kos->photo) : null);
                                $thumbInitial = strtoupper(mb_substr($c->displayLabel(), 0, 1));
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-14 h-9 rounded-lg overflow-hidden bg-gradient-to-br from-primary-50 to-warn-50 dark:from-surface-elevated dark:to-surface-elevated border border-slate-100 dark:border-white/10 shrink-0 relative">
                                            @if($thumbPath)
                                                <img src="{{ asset('storage/' . $thumbPath) }}" alt="Banner {{ $c->campaign_number }}" loading="lazy" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="hidden absolute inset-0 items-center justify-center text-[10px] font-bold text-primary-600/60 dark:text-primary-300/40">{{ $thumbInitial }}</div>
                                            @else
                                                <div class="absolute inset-0 flex items-center justify-center text-[10px] font-bold text-primary-600/60 dark:text-primary-300/40">{{ $thumbInitial }}</div>
                                            @endif
                                        </div>
                                        <span class="font-mono text-xs font-bold text-slate-500 dark:text-slate-400">{{ $c->campaign_number }}</span>
                                    </div>
                                </td>
                                <td class="font-medium text-slate-800 dark:text-slate-200">{{ $c->owner->name }}</td>
                                <td class="text-slate-600 dark:text-slate-300">{{ $c->kos?->name ?? $c->advertiser_name }}</td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold {{ \App\Support\AdvertisingLabels::campaignBadge($c->status) }}">
                                        {{ \App\Support\AdvertisingLabels::campaignLabel($c->status) }}
                                    </span>
                                </td>
                                <td class="text-slate-500 dark:text-slate-400">{{ $c->starts_at?->format('d/m/Y') }} - {{ $c->ends_at?->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('super-admin.advertising.campaigns.show', $c) }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="ri-bill-line" title="Belum ada kampanye" description="Kampanye iklan baru akan muncul di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
