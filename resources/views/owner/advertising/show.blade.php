<x-app-layout>
    <div class="space-y-6">
        <x-alert />

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('owner.advertising.index') }}" class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i class="ri-arrow-left-line"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $campaign->campaign_number }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $campaign->displayLabel() }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold {{ \App\Support\AdvertisingLabels::campaignBadge($campaign->status) }}">
                    {{ \App\Support\AdvertisingLabels::campaignLabel($campaign->status) }}
                </span>
                @if($campaign->canBePaid())
                    <form method="POST" action="{{ route('owner.advertising.pay', $campaign) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-green-500 hover:bg-green-600 px-4 py-2.5 rounded-xl transition">
                            <i class="ri-bank-card-line"></i> Bayar Sekarang
                        </button>
                    </form>
                @endif
                @if($campaign->canBeCancelled())
                    <form method="POST" action="{{ route('owner.advertising.cancel', $campaign) }}" onsubmit="return confirm('Batalkan kampanye ini?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-4 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            <i class="ri-close-circle-line"></i> Batalkan
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Detail + Paket --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="ri-information-line text-primary-500"></i> Detail Kampanye
                </h3>
                <dl class="mt-4 space-y-3 text-sm">
                    @if($campaign->isThirdParty())
                        <div class="flex justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Advertiser</dt>
                            <dd class="font-semibold text-slate-800 dark:text-slate-200 text-right">{{ $campaign->advertiser_name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Placement</dt>
                            <dd>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ \App\Support\AdvertisingLabels::placementBadge($campaign->placement) }}">
                                    {{ \App\Support\AdvertisingLabels::placementLabel($campaign->placement) }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Headline</dt>
                            <dd class="font-semibold text-slate-800 dark:text-slate-200 text-right max-w-[55%]">{{ $campaign->displayHeadline() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">CTA</dt>
                            <dd class="font-semibold text-slate-800 dark:text-slate-200">{{ $campaign->displayCta() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Destination</dt>
                            <dd class="font-semibold text-slate-800 dark:text-slate-200 text-right text-xs max-w-[55%] break-all">{{ $campaign->destination_url }}</dd>
                        </div>
                    @else
                        <div class="flex justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Kos</dt>
                            <dd class="font-semibold text-slate-800 dark:text-slate-200 text-right">{{ $campaign->kos?->name }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">Paket</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200">{{ $campaign->package->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">Budget</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($campaign->budget, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">Mulai</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200">{{ $campaign->starts_at?->format('d M Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">Selesai</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200">{{ $campaign->ends_at?->format('d M Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">Klik</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200">{{ $clicks }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="ri-megaphone-line text-indigo-500"></i> Performa Iklan
                </h3>
                <div class="mt-4 grid grid-cols-2 gap-4 text-center">
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($impressions) }}</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Impressions</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                        <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $ctr }}%</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">CTR</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                        <p class="text-2xl font-black text-green-600 dark:text-green-400">{{ number_format($conversions) }}</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Booking</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                        <p class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $conversionRate }}%</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Konversi</p>
                    </div>
                </div>
                @if($campaign->isThirdParty())
                    <div class="flex flex-col gap-2 mt-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Iklan ditayangkan pada placement <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ \App\Support\AdvertisingLabels::placementLabel($campaign->placement) }}</span>. Klik mengarah ke situs advertiser — konversi tidak diatribusikan ke booking kos.</p>
                        @if($campaign->destination_url)
                            <a href="{{ $campaign->destination_url }}" target="_blank" rel="noopener noreferrer" class="w-full inline-flex items-center justify-center gap-2 text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 text-slate-700 dark:text-slate-200 hover:text-indigo-600 px-4 py-2.5 rounded-xl transition">
                                Buka destination URL <i class="ri-external-link-line"></i>
                            </a>
                        @endif
                    </div>
                @else
                    <a href="{{ $campaign->kos ? route('tenant.kos.show', $campaign->kos) : '#' }}" target="_blank" class="mt-4 w-full inline-flex items-center justify-center gap-2 text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-primary-50 text-slate-700 dark:text-slate-200 hover:text-primary-600 px-4 py-2.5 rounded-xl transition">
                        Lihat halaman kos <i class="ri-external-link-line"></i>
                    </a>
                @endif
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="ri-bank-card-line text-green-500"></i> Pembayaran
                </h3>
                @if($campaign->orders->isNotEmpty())
                    <dl class="mt-4 space-y-3 text-sm">
                        @foreach($campaign->orders as $order)
                            <div class="border border-slate-100 dark:border-slate-800 rounded-xl p-3">
                                <div class="flex justify-between">
                                    <dt class="text-slate-500 dark:text-slate-400">{{ $order->order_number }}</dt>
                                    <dd class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($order->amount, 0, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between mt-1">
                                    <dt class="text-slate-500 dark:text-slate-400 text-[11px]">Status</dt>
                                    <dd>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ \App\Support\AdvertisingLabels::orderBadge($order->status) }}">
                                            {{ \App\Support\AdvertisingLabels::orderLabel($order->status) }}
                                        </span>
                                    </dd>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <div class="mt-4 text-center py-6">
                        <i class="ri-bank-card-line text-3xl text-slate-300 dark:text-slate-600"></i>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Belum ada pembayaran.</p>
                        @if($campaign->canBePaid())
                            <form method="POST" action="{{ route('owner.advertising.pay', $campaign) }}" class="mt-4">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-green-500 hover:bg-green-600 px-5 py-2.5 rounded-xl transition">
                                    <i class="ri-bank-card-line"></i> Bayar Rp {{ number_format($campaign->budget, 0, ',', '.') }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
