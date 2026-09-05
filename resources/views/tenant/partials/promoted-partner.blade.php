@php
    // Iklan ADVERTISER PIHAK KETIGA yang JELAS terpisah dari listing kos organik.
    // Menerima: $ad (AdvertisingCampaign aktif, kos_id NULL), $placement, $variant ('banner'|'card').
    $ad = $ad ?? null;
    $placement = $placement ?? 'marketplace';
    $variant = $variant ?? 'banner';
    $adUrl = $ad ? route('tenant.ad.click', ['campaign' => $ad->id, 'placement' => $placement]) : '#';
    $image = $ad ? $ad->displayImage() : null;
    $initial = $ad ? mb_strtoupper(trim(mb_substr((string) $ad->advertiser_name, 0, 1))) : 'P';
@endphp

@if($ad)
    @if($variant === 'card')
        <a href="{{ $adUrl }}"
           class="group block bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden hover:shadow-xl hover:-translate-y-1 hover:border-primary-200 dark:hover:border-primary-500/30 transition-all duration-200">
            @if($image)
                <div class="relative aspect-[16/9] overflow-hidden bg-gradient-to-br from-indigo-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700">
                    <img src="{{ $image }}" alt="{{ $ad->advertiser_name }}" loading="lazy"
                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                         onerror="this.style.display='none'">
                </div>
            @endif
            <div class="p-5">
                <div class="flex items-center gap-2.5">
                    <span class="shrink-0 w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-primary-600 text-white flex items-center justify-center text-sm font-black shadow-sm">
                        {{ $ad->advertiser_logo ? '' : $initial }}
                        @if($ad->advertiser_logo)
                            <img src="{{ $ad->advertiser_logo }}" alt="" class="w-9 h-9 rounded-xl object-cover" onerror="this.style.display='none'">
                        @endif
                    </span>
                    <span class="min-w-0">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-indigo-500 dark:text-indigo-400"><i class="ri-megaphone-fill mr-0.5"></i> Promoted Partner</span>
                        <span class="block text-sm font-bold text-slate-900 dark:text-white truncate">{{ $ad->advertiser_name }}</span>
                    </span>
                </div>

                <h3 class="mt-3 font-bold text-slate-900 dark:text-white leading-snug line-clamp-2 group-hover:text-primary-500 dark:group-hover:text-primary-300 transition">{{ $ad->displayHeadline() }}</h3>
                <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">{{ $ad->displayDescription() }}</p>

                <div class="mt-4 flex items-center justify-between gap-3">
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Iklan · Partner KosManager</span>
                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-bold px-4 py-2 transition active:scale-95">
                        {{ $ad->displayCta() }} <i class="ri-arrow-right-line text-xs"></i>
                    </span>
                </div>
            </div>
        </a>
    @else
        <a href="{{ $adUrl }}"
           class="group relative block overflow-hidden rounded-2xl border border-indigo-100 dark:border-indigo-500/20 bg-gradient-to-br from-white via-indigo-50/60 to-primary-50 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
            @if($image)
                <div class="absolute inset-y-0 right-0 w-1/3 sm:w-1/4 hidden md:block">
                    <img src="{{ $image }}" alt="{{ $ad->advertiser_name }}" loading="lazy"
                         class="w-full h-full object-cover"
                         onerror="this.style.display='none'">
                    <div class="absolute inset-0 bg-gradient-to-r from-white dark:from-slate-900 via-white/20 dark:via-slate-900/20 to-transparent"></div>
                </div>
            @endif

            <div class="relative p-5 sm:p-6 pr-6 md:pr-28 lg:pr-36 flex flex-col justify-center min-h-[9.5rem]">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg bg-indigo-500 text-white">
                        <i class="ri-megaphone-fill text-[10px]"></i> Promoted Partner
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                        @if($ad->advertiser_logo)
                            <img src="{{ $ad->advertiser_logo }}" alt="" class="w-4 h-4 rounded object-cover" onerror="this.style.display='none'">
                        @endif
                        {{ $ad->advertiser_name }}
                    </span>
                </div>

                <h3 class="mt-2 text-base sm:text-lg font-black text-slate-900 dark:text-white leading-snug line-clamp-2 group-hover:text-primary-500 dark:group-hover:text-primary-300 transition">{{ $ad->displayHeadline() }}</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 line-clamp-1 sm:line-clamp-2 leading-relaxed sm:max-w-xl">{{ $ad->displayDescription() }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 bg-indigo-500 group-hover:bg-indigo-600 text-white text-xs font-bold px-4 py-2 rounded-xl transition active:scale-95">
                        {{ $ad->displayCta() }} <i class="ri-arrow-right-line text-xs"></i>
                    </span>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Iklan · Partner KosManager</span>
                </div>
            </div>
        </a>
    @endif
@endif