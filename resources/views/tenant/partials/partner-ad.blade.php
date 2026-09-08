@php
    // Iklan partner monetisasi (DANA / Shopee / GoPay) untuk TENANT — tampilan
    // premium data-driven. Menerima:
    //   $ad               (AdvertisingCampaign aktif, kos_id NULL)
    //   $placement        string
    //   $variant          ('banner' | 'card' | 'compact')  default banner
    //
    // Identitas visual tidak pernah dipakai sebagai logo palsu: bila partner
    // registri memiliki logo, logo dipakai; jika tidak, monogram huruf awal
    // dengan warna aksen tema menjadi fallback.
    $ad = $ad ?? null;
    $placement = $placement ?? 'marketplace';
    $variant = $variant ?? 'banner';

    if ($ad) {
        $adUrl = route('tenant.ad.click', ['campaign' => $ad->id, 'placement' => $placement]);
        $image = $ad->displayImage();
        $brandLogo = $ad->partner && $ad->partner->logo ? $ad->partner->logo : ($ad->advertiser_logo ?: null);
        $brandName = $ad->partnerLabel() ?: $ad->advertiser_name ?: 'Partner';
        $partnerSlug = $ad->partner && $ad->partner->slug ? $ad->partner->slug : 'default';
        $initial = mb_strtoupper(trim(mb_substr($brandName, 0, 1)));
        $cta = $ad->displayCta();
        $a11y = trim(rtrim($cta, '→!.')) . ' ' . $brandName;

        // Tema visual per partner (string literal — dipindai Tailwind JIT).
        $themes = [
            'dana' => [
                'grad' => 'bg-gradient-to-br from-[#eaf4ff] via-white to-[#d3e9ff] dark:from-[#081d36] dark:via-[#0b2a4d] dark:to-[#081d36]',
                'badge' => 'bg-white/85 text-[#0a5fd0] border-[#0a5fd0]/15 dark:bg-[#0a6fff]/15 dark:text-[#7ab8ff] dark:border-[#3d9bff]/20',
                'cta' => 'bg-[#0a5fd0] text-white hover:bg-[#074ec2]',
                'ctaGhost' => 'text-[#0a5fd0] dark:text-[#5aa6ff]',
                'mono' => 'from-[#0a5fd0] to-[#135fd3]',
                'accent' => 'text-[#0a5fd0] dark:text-[#5aa6ff]',
                'soft' => 'bg-[#0a6fff]/10 text-[#0a6fff] dark:bg-[#5aa6ff]/10 dark:text-[#7ab8ff]',
                'overlay' => 'from-[#eaf4ff] via-[#eaf4ff]/60 to-transparent dark:from-[#081d36] dark:via-[#081d36]/70 dark:to-transparent',
                'border' => 'border-[#0a6fff]/10 hover:border-[#0a6fff]/30 dark:border-white/5 dark:hover:border-[#3d9bff]/30',
                'blob' => 'bg-[#0a6fff]/10 dark:bg-[#3d9bff]/15',
                'blob2' => 'bg-[#3d9bff]/10 dark:bg-[#5aa6ff]/10',
            ],
            'shopee' => [
                'grad' => 'bg-gradient-to-br from-[#fff3ee] via-white to-[#ffe2d5] dark:from-[#27120e] dark:via-[#3a1c13] dark:to-[#27120e]',
                'badge' => 'bg-white/85 text-[#cc2e14] border-[#ee4d2d]/15 dark:bg-[#ee4d2d]/15 dark:text-[#ff9a7c] dark:border-[#ff7a5c]/20',
                'cta' => 'bg-[#d33a1d] text-white hover:bg-[#b72a12]',
                'ctaGhost' => 'text-[#cc2e14] dark:text-[#ff7a5c]',
                'mono' => 'from-[#cc2e14] to-[#d33a1d]',
                'accent' => 'text-[#cc2e14] dark:text-[#ff7a5c]',
                'soft' => 'bg-[#ee4d2d]/10 text-[#ee4d2d] dark:bg-[#ff7a5c]/10 dark:text-[#ff9a7c]',
                'overlay' => 'from-[#fff3ee] via-[#fff3ee]/60 to-transparent dark:from-[#27120e] dark:via-[#27120e]/70 dark:to-transparent',
                'border' => 'border-[#ee4d2d]/10 hover:border-[#ee4d2d]/35 dark:border-white/5 dark:hover:border-[#ff7a5c]/30',
                'blob' => 'bg-[#ee4d2d]/10 dark:bg-[#ff7a5c]/15',
                'blob2' => 'bg-[#ff7a5c]/10 dark:bg-[#ff9a7c]/10',
            ],
            'gopay' => [
                'grad' => 'bg-gradient-to-br from-[#eefbf1] via-white to-[#d5f4de] dark:from-[#0a2417] dark:via-[#0d3120] dark:to-[#0a2417]',
                'badge' => 'bg-white/85 text-[#008316] border-[#00aa13]/15 dark:bg-[#00aa13]/15 dark:text-[#5fd96f] dark:border-[#3fce55]/20',
                'cta' => 'bg-[#008316] text-white hover:bg-[#00700c]',
                'ctaGhost' => 'text-[#008316] dark:text-[#45d060]',
                'mono' => 'from-[#008316] to-[#00760b]',
                'accent' => 'text-[#008316] dark:text-[#45d060]',
                'soft' => 'bg-[#00aa13]/10 text-[#008f0f] dark:bg-[#45d060]/10 dark:text-[#5fd96f]',
                'overlay' => 'from-[#eefbf1] via-[#eefbf1]/60 to-transparent dark:from-[#0a2417] dark:via-[#0a2417]/70 dark:to-transparent',
                'border' => 'border-[#00aa13]/10 hover:border-[#00aa13]/35 dark:border-white/5 dark:hover:border-[#3fce55]/30',
                'blob' => 'bg-[#00aa13]/10 dark:bg-[#3fce55]/15',
                'blob2' => 'bg-[#3fce55]/10 dark:bg-[#45d060]/10',
            ],
            'default' => [
                'grad' => 'bg-gradient-to-br from-slate-50 via-white to-primary-50 dark:from-slate-800 dark:via-slate-900 dark:to-slate-800',
                'badge' => 'bg-white/85 text-primary-600 border-primary-500/15 dark:bg-primary-500/15 dark:text-primary-300 dark:border-primary-400/20',
                'cta' => 'bg-primary-600 text-white hover:bg-primary-700',
                'ctaGhost' => 'text-primary-600 dark:text-primary-400',
                'mono' => 'from-primary-600 to-primary-500',
                'accent' => 'text-primary-600 dark:text-primary-400',
                'soft' => 'bg-primary-500/10 text-primary-600 dark:bg-primary-500/10 dark:text-primary-300',
                'overlay' => 'from-slate-50 via-white/70 to-transparent dark:from-slate-800 dark:via-slate-800/70 dark:to-transparent',
                'border' => 'border-slate-200/70 hover:border-primary-300 dark:border-white/5 dark:hover:border-primary-400/30',
                'blob' => 'bg-primary-500/10 dark:bg-primary-400/15',
                'blob2' => 'bg-indigo-400/10 dark:bg-primary-400/10',
            ],
        ];

        $theme = $themes[$partnerSlug] ?? $themes['default'];

        // Bramd mark: monogram dengan inisial, logo partner (jika ada) menutupi.
        $monogram = static function ($logo, $initial, $mono) {
            return '<span class="relative grid h-9 w-9 shrink-0 place-items-center overflow-hidden rounded-2xl bg-gradient-to-br sm:h-11 sm:w-11 ' . $mono . ' text-white text-sm sm:text-base font-black shadow-sm">'
                . '<span aria-hidden="true">' . $initial . '</span>'
                . ($logo ? '<img src="' . $logo . '" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">' : '')
                . '</span>';
        };
    }
@endphp

@if($ad)
    @if($variant === 'banner')
        {{-- Hero: full-width premium, split visual di desktop, aksen di mobile. --}}
        <a href="{{ $adUrl }}" aria-label="{{ $a11y }}"
           class="group relative block overflow-hidden rounded-3xl border {{ $theme['border'] }} {{ $theme['grad'] }} transition-colors duration-200">
            <div aria-hidden="true" class="pointer-events-none absolute right-2 top-2 h-40 w-40 rounded-full {{ $theme['blob'] }} blur-2xl"></div>
            <div aria-hidden="true" class="pointer-events-none absolute left-2 bottom-2 h-36 w-36 rounded-full {{ $theme['blob2'] }} blur-2xl"></div>

            @if($image)
                <div class="absolute inset-y-0 right-0 hidden w-[42%] md:block">
                    <img src="{{ $image }}" alt="" loading="lazy" class="h-full w-full object-cover" onerror="this.style.display='none'">
                    <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-r {{ $theme['overlay'] }}"></div>
                </div>
            @else
                <div aria-hidden="true" class="absolute inset-y-0 right-0 hidden w-[38%] md:block bg-cover bg-center"
                     style="background-image:linear-gradient(to bottom left, rgba(255,255,255,0), rgba(255,255,255,0));"></div>
            @endif

            <div class="relative flex flex-col justify-center gap-2.5 p-4 sm:p-6 lg:p-7 min-h-[12.5rem] sm:min-h-[13rem] {{ $image ? 'md:pr-[46%]' : '' }}">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-[0.14em] rounded-full px-2.5 py-1 border {{ $theme['badge'] }}">
                        <span aria-hidden="true" class="h-1.5 w-1.5 rounded-full {{ $theme['cta'] }}"></span>
                        Sponsored
                    </span>
                    <span class="inline-flex items-center gap-2 text-xs font-bold {{ $theme['accent'] }}">
                        {!! $monogram($brandLogo, $initial, $theme['mono']) !!}
                        <span class="tracking-tight">{{ $brandName }}</span>
                    </span>
                </div>

                <h3 class="text-[15px] sm:text-lg font-black text-slate-900 dark:text-white leading-snug line-clamp-2 max-w-2xl">{{ $ad->displayHeadline() }}</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-snug line-clamp-2 max-w-xl">{{ $ad->displayDescription() }}</p>

                <div class="mt-1 flex flex-wrap items-center gap-3">
                    <span class="inline-flex min-h-[44px] items-center gap-2 rounded-2xl {{ $theme['cta'] }} text-xs sm:text-sm font-bold px-5 shadow-sm transition-colors duration-200">
                        {{ $cta }}
                        <i class="ri-arrow-right-line transition-transform duration-200 motion-safe:group-hover:translate-x-1"></i>
                    </span>
                    <span class="text-[10px] font-medium text-slate-600 dark:text-slate-300">
                        <span class="hidden sm:inline">Iklan · Partner KosManager</span>
                        <span class="sm:hidden">Iklan</span>
                    </span>
                </div>
            </div>
        </a>

    @elseif($variant === 'card')
        {{-- Kartu vertikal kompak: digunakan untuk slot sekunder dashboard. --}}
        <a href="{{ $adUrl }}" aria-label="{{ $a11y }}"
           class="group relative flex flex-col overflow-hidden rounded-3xl border {{ $theme['border'] }} {{ $theme['grad'] }} transition-colors duration-200">
            <div aria-hidden="true" class="pointer-events-none absolute right-2 top-2 h-32 w-32 rounded-full {{ $theme['blob'] }} blur-2xl"></div>

            @if($image)
                <div class="relative aspect-[16/7] shrink-0 overflow-hidden">
                    <img src="{{ $image }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 motion-safe:group-hover:scale-[1.03]" onerror="this.style.display='none'">
                    <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 text-[9px] font-black uppercase tracking-[0.14em] rounded-full px-2 py-0.5 border {{ $theme['badge'] }}">
                        <span aria-hidden="true" class="h-1.5 w-1.5 rounded-full {{ $theme['cta'] }}"></span>
                        Sponsored
                    </span>
                </div>
            @endif

            <div class="relative flex flex-1 flex-col p-5">
                <div class="flex items-center gap-2.5">
                    {!! $monogram($brandLogo, $initial, $theme['mono']) !!}
                    <span class="min-w-0">
                        <span class="block text-[10px] font-black uppercase tracking-wider {{ $theme['accent'] }}">Sponsored</span>
                        <span class="block truncate text-sm font-bold text-slate-900 dark:text-white">{{ $brandName }}</span>
                    </span>
                </div>

                <h3 class="mt-3 text-[15px] font-extrabold text-slate-900 dark:text-white leading-snug line-clamp-2">{{ $ad->displayHeadline() }}</h3>
                <p class="mt-1.5 text-xs text-slate-600 dark:text-slate-300 leading-snug line-clamp-2">{{ $ad->displayDescription() }}</p>

                <div class="mt-auto pt-4 flex items-end justify-between gap-3">
                    <span class="text-[10px] text-slate-600 dark:text-slate-300 font-medium">Iklan · Partner KosManager</span>
                    <span class="inline-flex min-h-[44px] items-center gap-1.5 rounded-2xl {{ $theme['cta'] }} text-white text-xs font-bold px-4 shadow-sm transition-colors duration-200 active:scale-95">
                        {{ $cta }} <i class="ri-arrow-right-line transition-transform motion-safe:group-hover:translate-x-0.5"></i>
                    </span>
                </div>
            </div>
        </a>

    @else
        {{-- Compact: baris ramping — marketplace & detail kos. --}}
        <a href="{{ $adUrl }}" aria-label="{{ $a11y }}"
           class="group flex items-center gap-3 sm:gap-4 rounded-2xl border {{ $theme['border'] }} {{ $theme['grad'] }} px-3.5 py-3 sm:px-4 transition-colors duration-200">
            <span class="hidden sm:block">
                {!! $monogram($brandLogo, $initial, $theme['mono']) !!}
            </span>

            <span class="min-w-0 flex-1">
                <span class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] font-bold {{ $theme['accent'] }}">
                    <span class="inline-flex items-center gap-1 uppercase tracking-wider">
                        <span aria-hidden="true" class="h-1 w-1 rounded-full {{ $theme['cta'] }}"></span> Sponsored
                    </span>
                    <span class="truncate text-[11px] text-slate-600 dark:text-slate-300">· {{ $brandName }}</span>
                </span>
                <span class="mt-0.5 block truncate text-[13px] sm:text-sm font-bold text-slate-800 dark:text-slate-100">{{ $ad->displayHeadline() }}</span>
            </span>

            <span class="inline-flex min-h-[44px] shrink-0 items-center gap-1.5 rounded-xl {{ $theme['cta'] }} text-white text-[11px] sm:text-xs font-bold px-3.5 sm:px-4 shadow-sm transition-colors duration-200 active:scale-95">
                <span class="hidden sm:inline">{{ $cta }}</span>
                <span class="sm:hidden" aria-hidden="true">{{ $cta }}</span>
                <i class="ri-arrow-right-line transition-transform motion-safe:group-hover:translate-x-0.5"></i>
            </span>
        </a>
    @endif
@endif