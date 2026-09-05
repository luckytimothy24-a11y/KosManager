<x-app-layout>
    <div class="space-y-6">
        <x-alert />

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('super-admin.advertising.campaigns.index') }}" class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i class="ri-arrow-left-line"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $campaign->campaign_number }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $campaign->kos?->name ?? $campaign->advertiser_name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold {{ \App\Support\AdvertisingLabels::campaignBadge($campaign->status) }}">
                    {{ \App\Support\AdvertisingLabels::campaignLabel($campaign->status) }}
                </span>
                @if($campaign->status === \App\Models\AdvertisingCampaign::STATUS_PENDING_REVIEW)
                    <form method="POST" action="{{ route('super-admin.advertising.campaigns.approve', $campaign) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-green-500 hover:bg-green-600 px-4 py-2.5 rounded-xl transition">
                            <i class="ri-check-line"></i> Setujui
                        </button>
                    </form>
                    <button type="button" onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                            class="inline-flex items-center gap-2 text-xs font-semibold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/40 px-4 py-2.5 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                        <i class="ri-close-circle-line"></i> Tolak
                    </button>
                @endif
                @if($campaign->isLive() || in_array($campaign->status, [
                    \App\Models\AdvertisingCampaign::STATUS_PENDING_REVIEW,
                    \App\Models\AdvertisingCampaign::STATUS_APPROVED,
                ]))
                    <button type="button" onclick="document.getElementById('suspend-modal').classList.remove('hidden')"
                            class="inline-flex items-center gap-2 text-xs font-semibold text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/40 px-4 py-2.5 rounded-xl hover:bg-amber-50 dark:hover:bg-amber-500/10 transition">
                        <i class="ri-pause-circle-line"></i> Tangguhkan
                    </button>
                @endif
            </div>
        </div>

        {{-- Detail grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="ri-information-line text-primary-500"></i> Detail Kampanye</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <x-advertising-detail-row :label="'Owner'" :value="$campaign->owner->name" />
                    @if($campaign->kos_id === null)
                        <x-advertising-detail-row :label="'Advertiser'" :value="$campaign->advertiser_name" />
                        <x-advertising-detail-row :label="'Headline'" :value="$campaign->headline" />
                        <x-advertising-detail-row :label="'Placement'" :value="\App\Support\AdvertisingLabels::placementLabel($campaign->placement)" />
                        <x-advertising-detail-row :label="'Destination'" :value="$campaign->destination_url" />
                    @else
                        <x-advertising-detail-row :label="'Kos'" :value="$campaign->kos?->name" />
                        <x-advertising-detail-row :label="'Perimeter'" :value="$campaign->kos?->address" />
                    @endif
                    <x-advertising-detail-row :label="'Budget'" :value="'Rp '.number_format($campaign->budget, 0, ',', '.').' ('.$campaign->package->name.')'" />
                    <x-advertising-detail-row :label="'Periode'" :value="$campaign->starts_at?->format('d M Y').' - '.$campaign->ends_at?->format('d M Y')" />
                    <x-advertising-detail-row :label="'Disetujui oleh'" :value="$campaign->approver?->name ?? '-'" />
                </dl>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="ri-megaphone-line text-indigo-500"></i> Performa Iklan</h3>
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
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="ri-bank-card-line text-green-500"></i> Pembayaran</h3>
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
                    </div>
                @endif
            </div>
        </div>

        {{-- Modals --}}
        <div id="reject-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('reject-modal').classList.add('hidden')"></div>
            <form method="POST" action="{{ route('super-admin.advertising.campaigns.reject', $campaign) }}" class="relative bg-white dark:bg-slate-900 rounded-2xl p-6 w-full max-w-md shadow-xl">
                @csrf
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tolak Kampanye</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Berikan alasan penolakan yang akan dilihat oleh owner.</p>
                <textarea name="reason" rows="3" required placeholder="Alasan penolakan..."
                          class="mt-4 w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-4 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Batal</button>
                    <button type="submit" class="text-sm font-bold text-white bg-red-500 hover:bg-red-600 px-5 py-2 rounded-xl transition">Tolak Kampanye</button>
                </div>
            </form>
        </div>

        <div id="suspend-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('suspend-modal').classList.add('hidden')"></div>
            <form method="POST" action="{{ route('super-admin.advertising.campaigns.suspend', $campaign) }}" class="relative bg-white dark:bg-slate-900 rounded-2xl p-6 w-full max-w-md shadow-xl">
                @csrf
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tangguhkan Kampanye</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kampanye akan dihentikan sementara dari tayang.</p>
                <textarea name="reason" rows="3" required placeholder="Alasan penangguhan..."
                          class="mt-4 w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-transparent"></textarea>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('suspend-modal').classList.add('hidden')" class="text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-4 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Batal</button>
                    <button type="submit" class="text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 px-5 py-2 rounded-xl transition">Tangguhkan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
