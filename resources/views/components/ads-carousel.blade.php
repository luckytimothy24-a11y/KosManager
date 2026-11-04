@props([
    'ads' => null,
    'placement' => 'homepage',
    'heading' => 'Penawaran Terbaik untuk Pencari Kos',
    'description' => 'Promo dan penawaran dari mitra terpilih KosManager.',
])

@php
    if ($ads !== null) {
        $ads = $ads instanceof \Illuminate\Support\Collection ? $ads : collect($ads);
    }

    $brandThemes = [
        'dana' => [
            'wash' => 'bg-gradient-to-br from-[#eaf4ff] via-white to-[#d3e9ff] dark:from-[#0a1e33] dark:via-[#0c2745] dark:to-[#0a1e33]',
            'border' => 'border-[#0a6fff]/20 hover:border-[#0a6fff]/45 dark:border-[#3d9bff]/25 dark:hover:border-[#3d9bff]/50',
            'gradient' => 'from-[#0a63d8] via-[#0a5fd0] to-[#073e8c]',
            'from' => '#0a63d8',
            'to' => '#073e8c',
            'accent' => '#8ed0ff',
            'accentStrong' => '#0848a0',
            'monogram' => 'from-[#0a6fff] to-[#0747a6]',
        ],
        'shopee' => [
            'wash' => 'bg-gradient-to-br from-[#fff3ee] via-white to-[#ffe2d5] dark:from-[#2a140e] dark:via-[#351a11] dark:to-[#2a140e]',
            'border' => 'border-[#ee4d2d]/20 hover:border-[#ee4d2d]/45 dark:border-[#ff7a5c]/25 dark:hover:border-[#ff7a5c]/50',
            'gradient' => 'from-[#d33a1d] via-[#c2351a] to-[#8f250f]',
            'from' => '#d33a1d',
            'to' => '#8f250f',
            'accent' => '#ffb199',
            'accentStrong' => '#b32d12',
            'monogram' => 'from-[#ee4d2d] to-[#b32d12]',
        ],
        'gopay' => [
            'wash' => 'bg-gradient-to-br from-[#eefbf1] via-white to-[#d5f4de] dark:from-[#082014] dark:via-[#0b2b1b] dark:to-[#082014]',
            'border' => 'border-[#00aa13]/20 hover:border-[#00aa13]/45 dark:border-[#3fce55]/25 dark:hover:border-[#3fce55]/50',
            'gradient' => 'from-[#008316] via-[#00760b] to-[#00540a]',
            'from' => '#008316',
            'to' => '#00540a',
            'accent' => '#8bea9a',
            'accentStrong' => '#005c0f',
            'monogram' => 'from-[#00aa13] to-[#00760b]',
        ],
        'default' => [
            'wash' => 'bg-gradient-to-br from-primary-50 via-white to-primary-100/70 dark:from-[#1e293b] dark:via-[#1e293b] dark:to-[#0f172a]',
            'border' => 'border-primary-200/70 hover:border-primary-400/70 dark:border-white/10 dark:hover:border-primary-500/40',
            'gradient' => 'from-primary-600 via-primary-700 to-primary-800',
            'from' => '#4f46e5',
            'to' => '#312e81',
            'accent' => '#c7d2fe',
            'accentStrong' => '#6e5443',
            'monogram' => 'from-primary-500 to-primary-700',
        ],
    ];
@endphp

@if($ads === null)
    {{-- Skeleton ringan: hanya tampil bila data iklan belum tersedia. --}}
    <section class="bg-gradient-to-b from-gray-50/80 to-white py-10 dark:from-slate-800/40 dark:to-surface-dark md:py-12" aria-busy="true" aria-label="Memuat promo mitra">
        <div class="mx-auto max-w-[1170px] px-4 sm:px-6">
            <div class="h-6 w-32 animate-pulse rounded-full bg-slate-200/80 dark:bg-slate-700/70"></div>
            <div class="mt-3 h-8 w-72 max-w-full animate-pulse rounded-lg bg-slate-200/80 dark:bg-slate-700/70"></div>
            <div class="mt-2 h-4 w-80 max-w-full animate-pulse rounded bg-slate-200/60 dark:bg-slate-700/50"></div>
            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @for($i = 0; $i < 3; $i++)
                    <div class="rounded-2xl border border-slate-200/70 bg-white p-3 dark:border-white/10 dark:bg-surface-elevated">
                        <div class="flex items-center justify-between">
                            <div class="h-9 w-24 animate-pulse rounded-lg bg-slate-200/80 dark:bg-slate-700/70"></div>
                            <div class="h-6 w-14 animate-pulse rounded-full bg-slate-200/60 dark:bg-slate-700/50"></div>
                        </div>
                        <div class="mt-3 h-[190px] animate-pulse rounded-xl bg-slate-200/70 dark:bg-slate-700/60"></div>
                        <div class="mt-4 h-4 w-24 animate-pulse rounded bg-slate-200/70 dark:bg-slate-700/60"></div>
                        <div class="mt-2 h-5 w-full animate-pulse rounded bg-slate-200/70 dark:bg-slate-700/60"></div>
                        <div class="mt-2 h-3 w-5/6 animate-pulse rounded bg-slate-200/60 dark:bg-slate-700/50"></div>
                        <div class="mt-4 h-10 w-full animate-pulse rounded-xl bg-slate-200/70 dark:bg-slate-700/60"></div>
                    </div>
                @endfor
            </div>
        </div>
    </section>
@elseif($ads->isNotEmpty())
    <section aria-labelledby="promo-section-heading" class="relative overflow-hidden bg-gradient-to-b from-gray-50/80 to-white py-10 dark:from-slate-800/40 dark:to-surface-dark md:py-12">
        <div class="mx-auto max-w-[1170px] px-4 sm:px-6">
            {{-- Header section --}}
            <div class="mb-6 flex flex-wrap items-end justify-between gap-4 md:mb-8">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-red-500 via-orange-500 to-amber-500 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white shadow-sm shadow-red-500/25">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>
                        </svg>
                        Promo Terbatas
                    </span>
                    <h2 id="promo-section-heading" class="mt-3 text-2xl font-extrabold tracking-tight text-gray-900 dark:text-slate-50 md:text-3xl">{{ $heading }}</h2>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $description }}</p>
                </div>
                <p class="hidden items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400 sm:flex">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M8 3 4 7l4 4"/><path d="M4 7h16"/><path d="m16 21 4-4-4-4"/><path d="M20 17H4"/>
                    </svg>
                    Geser atau gunakan panah
                </p>
            </div>

            <div x-data="{
                    index: 0,
                    perView: 3,
                    total: {{ $ads->count() }},
                    paused: false,
                    hovered: false,
                    focused: false,
                    touching: false,
                    reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
                    timer: null,
                    init() {
                        this.updatePerView();
                        window.addEventListener('resize', () => this.updatePerView());
                    },
                    destroy() { clearInterval(this.timer); },
                    get totalPages() { return Math.max(1, Math.ceil(this.total / this.perView)); },
                    get shouldAutoplay() { return !this.reducedMotion && !this.paused && !this.hovered && !this.focused && !this.touching && this.totalPages > 1; },
                    updatePerView() {
                        const next = window.innerWidth >= 1024 ? 3 : (window.innerWidth >= 640 ? 2 : 1);
                        if (next !== this.perView) {
                            this.perView = next;
                            this.index = Math.min(this.index, this.totalPages - 1);
                            this.$nextTick(() => this.scrollToPage(this.index, false));
                        }
                        this.startAutoplay();
                    },
                    startAutoplay() {
                        clearInterval(this.timer);
                        this.timer = null;
                        if (!this.shouldAutoplay) return;
                        this.timer = setInterval(() => this.goTo((this.index + 1) % this.totalPages, true), 5500);
                    },
                    setHover(state) { this.hovered = state; this.startAutoplay(); },
                    setFocus(state) { this.focused = state; this.startAutoplay(); },
                    onTouchStart(event) { this.touching = true; this.startAutoplay(); },
                    onTouchEnd(event) { this.touching = false; this.startAutoplay(); },
                    togglePause() { this.paused = !this.paused; this.startAutoplay(); },
                    go(i) { this.index = Math.max(0, Math.min(this.total - 1, i)); this.index = Math.min(this.index, this.totalPages - 1); this.scrollToPage(this.index, true); this.startAutoplay(); },
                    goTo(index, fromAutoplay = false) {
                        const next = (index + this.totalPages) % this.totalPages;
                        this.index = next;
                        this.scrollToPage(next, true);
                        if (! fromAutoplay) this.startAutoplay();
                    },
                    prev() { this.goTo(this.index - 1); },
                    next() { this.goTo(this.index + 1); },
                    scrollToPage(index, smooth = true) {
                        const track = this.$refs.track;
                        if (! track) return;
                        const max = track.scrollWidth - track.clientWidth;
                        if (max <= 0) return;
                        const pageWidth = track.clientWidth + 16;
                        const left = Math.min(max, Math.max(0, index * pageWidth));
                        track.scrollTo({ left, behavior: smooth && ! this.reducedMotion ? 'smooth' : 'auto' });
                    },
                    onScroll() {
                        const track = this.$refs.track;
                        if (! track) return;
                        const max = track.scrollWidth - track.clientWidth;
                        if (max <= 1) { this.index = 0; return; }
                        const pageWidth = track.clientWidth + 16;
                        const computed = track.scrollLeft >= max - 2
                            ? this.totalPages - 1
                            : Math.min(this.totalPages - 1, Math.round(track.scrollLeft / pageWidth));
                        if (computed !== this.index) this.index = computed;
                    },
                }"
                 role="region"
                 aria-roledescription="carousel"
                 aria-label="Promo dan partner pilihan"
                 tabindex="0"
                 class="rounded-2xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-4 dark:focus-visible:ring-offset-surface-dark"
                 @mouseenter="setHover(true)"
                 @mouseleave="setHover(false)"
                 @focusin="setFocus(true)"
                 @focusout="if (! $el.contains($event.relatedTarget)) setFocus(false)"
                 @touchstart.passive="onTouchStart($event)"
                 @touchend.passive="onTouchEnd($event)"
                 @touchcancel.passive="onTouchEnd($event)"
                 @keydown.left.prevent="prev()"
                 @keydown.right.prevent="next()"
                 @keydown.home.prevent="go(0)"
                 @keydown.end.prevent="go(totalPages - 1)"
                 data-promo-reveal>
                <p class="sr-only" aria-live="polite" x-text="'Halaman ' + (index + 1) + ' dari ' + totalPages"></p>

                {{-- Track --}}
                <div class="overflow-hidden">
                    <div x-ref="track"
                         class="promo-track flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth pb-1"
                         @scroll.debounce.80ms="onScroll()">
                        @foreach($ads as $ad)
                            @php
                                $clickUrl = route('ad.click', ['campaign' => $ad->id, 'placement' => $placement]);
                                $partnerSlug = $ad->partner?->slug ?: 'default';
                                $theme = $brandThemes[$partnerSlug] ?? $brandThemes['default'];

                                $logoSrc = null;
                                $partnerLogo = $ad->partner?->logo;
                                if ($partnerLogo) {
                                    if (\Illuminate\Support\Str::startsWith($partnerLogo, ['http://', 'https://'])) {
                                        $logoSrc = $partnerLogo;
                                    } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($partnerLogo)) {
                                        $logoSrc = \Illuminate\Support\Facades\Storage::url($partnerLogo);
                                    }
                                }
                                if (! $logoSrc && $ad->advertiser_logo) {
                                    $advLogo = $ad->advertiser_logo;
                                    if (\Illuminate\Support\Str::startsWith($advLogo, ['http://', 'https://'])) {
                                        $logoSrc = $advLogo;
                                    } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($advLogo)) {
                                        $logoSrc = \Illuminate\Support\Facades\Storage::url($advLogo);
                                    }
                                }
                                if (! $logoSrc && file_exists(public_path('images/partners/'.$partnerSlug.'.svg'))) {
                                    $logoSrc = asset('images/partners/'.$partnerSlug.'.svg');
                                }

                                $daysLeft = $ad->remainingDays();
                                $showUrgency = $daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 7;
                                $urgencyText = $daysLeft === 0
                                    ? 'Berakhir hari ini'
                                    : ($daysLeft === 1 ? 'Berakhir besok' : 'Berakhir dalam '.$daysLeft.' hari');
                                $visualTheme = $ad->visualTheme();
                                $highlight = $ad->displayHighlight();
                                $hasHighlight = $highlight !== '';
                            @endphp

                            <div class="promo-reveal-item w-full shrink-0 snap-start sm:w-[calc(50%-0.5rem)] lg:w-[calc(33.333%-0.667rem)]">
                                <a href="{{ $clickUrl }}"
                                   data-ad-id="{{ $ad->id }}"
                                   rel="sponsored noopener noreferrer"
                                   target="_blank"
                                   aria-label="{{ $ad->displayCta() }} — {{ $ad->displayLabel() }} (iklan, membuka tab baru)"
                                   class="group relative flex h-full flex-col overflow-hidden rounded-2xl border {{ $theme['border'] }} {{ $theme['wash'] }} shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-surface-dark"
                                   style="--promo-from: {{ $theme['from'] }}; --promo-to: {{ $theme['to'] }}; --promo-accent: {{ $theme['accent'] }}; --promo-accent-strong: {{ $theme['accentStrong'] }};">

                                    {{-- Header: logo chip kiri + pill Iklan kanan --}}
                                    <div class="flex items-center justify-between gap-2 px-3 pb-2 pt-3">
                                        <span class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg bg-white px-2 shadow-sm ring-1 ring-black/5">
                                            @if($logoSrc)
                                                <img src="{{ $logoSrc }}"
                                                     alt="Logo {{ $ad->displayLabel() }}"
                                                     width="80"
                                                     height="24"
                                                     loading="lazy"
                                                     decoding="async"
                                                     class="h-5 w-auto max-w-[84px] object-contain"
                                                     onerror="this.classList.add('hidden'); if (this.nextElementSibling) { this.nextElementSibling.classList.remove('hidden'); }">
                                                <span class="hidden h-6 w-6 items-center justify-center rounded-md bg-gradient-to-br {{ $theme['monogram'] }} text-[11px] font-bold uppercase text-white" aria-hidden="true">{{ mb_substr($ad->displayLabel(), 0, 1) }}</span>
                                            @else
                                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-gradient-to-br {{ $theme['monogram'] }} text-[11px] font-bold uppercase text-white" aria-hidden="true">{{ mb_substr($ad->displayLabel(), 0, 1) }}</span>
                                                <span class="sr-only">{{ $ad->displayLabel() }}</span>
                                            @endif
                                        </span>

                                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200/80 bg-white/85 px-2.5 py-1 text-[11px] font-semibold text-slate-600 shadow-sm backdrop-blur-sm dark:border-white/10 dark:bg-white/10 dark:text-slate-200">
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                                            </svg>
                                            Iklan
                                            <span class="sr-only">Sponsored</span>
                                        </span>
                                    </div>

                                    {{-- Visual promo: ilustrasi SVG per tema, teks penawaran hanya bila diisi --}}
                                    <div class="promo-art-stage mx-3 relative h-[190px] overflow-hidden rounded-xl bg-gradient-to-br {{ $theme['gradient'] }}" data-promo-theme="{{ $visualTheme }}">
                                        <div class="pointer-events-none absolute -right-8 -top-10 h-36 w-36 rounded-full bg-white/20 blur-2xl" aria-hidden="true"></div>
                                        <div class="pointer-events-none absolute -bottom-12 -left-10 h-36 w-36 rounded-full bg-black/20 blur-2xl" aria-hidden="true"></div>
                                        <div class="pa-grain pointer-events-none absolute inset-0" aria-hidden="true"></div>

                                        <div class="pointer-events-none absolute inset-y-0 right-0 {{ $hasHighlight ? 'w-[60%] sm:w-[64%]' : 'w-[74%] sm:w-[78%]' }}">
                                            <x-dynamic-component :component="'promo-art.'.$visualTheme" />
                                        </div>

                                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-black/45 via-black/10 to-transparent" aria-hidden="true"></div>

                                        @if($hasHighlight)
                                            <div class="absolute inset-x-0 bottom-0 z-10 p-4 pr-[32%]">
                                                <p class="pa-highlight break-words text-[26px] font-extrabold leading-[1.05] tracking-tight text-white line-clamp-2 sm:text-[28px]">{{ $highlight }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Konten --}}
                                    <div class="flex flex-1 flex-col px-4 pb-4 pt-3">
                                        @if($ad->ends_at)
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="pa-badge inline-flex items-center gap-1 rounded-md bg-amber-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-900 dark:bg-amber-400/15 dark:text-amber-300">
                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                                                    </svg>
                                                    Promo Terbatas
                                                </span>
                                            </div>
                                        @endif

                                        <h3 class="mt-2 text-[15px] font-extrabold leading-snug text-slate-900 line-clamp-2 group-hover:text-primary-700 dark:text-slate-50 dark:group-hover:text-primary-300">
                                            {{ $ad->displayHeadline() }}
                                        </h3>
                                        <p class="mb-3 mt-1.5 text-xs leading-relaxed text-slate-600 line-clamp-2 dark:text-slate-300">
                                            {{ $ad->displayDescription() }}
                                        </p>

                                        <div class="mt-auto">
                                            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-[11px] font-medium text-slate-600 dark:text-slate-400">
                                                @if($ad->ends_at)
                                                    <span class="inline-flex items-center gap-1">
                                                        <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                                                        </svg>
                                                        Berlaku s.d. {{ $ad->ends_at->translatedFormat('d M Y') }}
                                                    </span>
                                                @endif
                                                @if($showUrgency)
                                                    <span class="inline-flex items-center gap-1 font-semibold text-amber-700 dark:text-amber-300">
                                                        <span class="h-1 w-1 rounded-full bg-current" aria-hidden="true"></span>
                                                        {{ $urgencyText }}
                                                    </span>
                                                @endif
                                                <span>S&amp;K berlaku</span>
                                            </div>

                                            <div class="mt-3 border-t border-slate-200/80 pt-3 dark:border-white/10">
                                                <span class="relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-xl px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-all duration-200 group-hover:shadow-md group-active:scale-[0.98]"
                                                      style="background-color: var(--promo-accent-strong);">
                                                    <span class="pa-shine" aria-hidden="true"></span>
                                                    {{ $ad->displayCta() }}
                                                    <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Kontrol carousel: panah rapat, dot di tengah, tombol jeda --}}
                <div x-show="totalPages > 1" x-cloak class="mt-5 flex items-center justify-center gap-2.5 sm:gap-3">
                    <button type="button"
                            @click="prev()"
                            aria-label="Promo sebelumnya"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-primary-300 hover:text-primary-600 active:scale-95 dark:border-white/10 dark:bg-surface-elevated dark:text-slate-300 dark:hover:border-primary-500/40 dark:hover:text-primary-300">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </button>

                    <div class="flex items-center gap-1.5" role="group" aria-label="Pilih halaman promo">
                        <template x-for="i in totalPages" :key="i">
                            <button type="button"
                                    @click="go(i - 1)"
                                    :aria-label="'Ke halaman ' + i"
                                    :aria-current="index === i - 1 ? 'true' : 'false'"
                                    :class="index === i - 1
                                        ? 'w-6 bg-primary-600 dark:bg-primary-400'
                                        : 'w-2.5 bg-slate-300 hover:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-500'"
                                    class="h-2.5 rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-surface-dark"></button>
                        </template>
                    </div>

                    <button type="button"
                            @click="next()"
                            aria-label="Promo berikutnya"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-primary-300 hover:text-primary-600 active:scale-95 dark:border-white/10 dark:bg-surface-elevated dark:text-slate-300 dark:hover:border-primary-500/40 dark:hover:text-primary-300">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </button>

                    <span class="mx-0.5 h-6 w-px bg-slate-200 dark:bg-slate-700" aria-hidden="true"></span>

                    <button type="button"
                            @click="togglePause()"
                            :aria-label="paused ? 'Putar otomatis promo' : 'Jeda putar otomatis promo'"
                            :aria-pressed="paused ? 'true' : 'false'"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 active:scale-95 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-slate-200">
                        <svg x-show="! paused" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/><path d="M10 15V9"/><path d="M14 15V9"/>
                        </svg>
                        <svg x-show="paused" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/><path d="m10 8 6 4-6 4V8z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        @once
            <style>
                [x-cloak] { display: none !important; }
            </style>
            <script>
                (function () {
                    function formatBalance(value) {
                        return 'Rp ' + Number(value).toLocaleString('id-ID');
                    }

                    function balanceValue(el) {
                        return Math.max(0, parseInt(el.getAttribute('data-pa-balance'), 10) || 0);
                    }

                    function stopBalance(el) {
                        el.dataset.paToken = '';
                    }

                    function setFinalBalance(card) {
                        card.querySelectorAll('[data-pa-balance]').forEach(function (el) {
                            stopBalance(el);
                            el.textContent = formatBalance(balanceValue(el));
                        });
                    }

                    function pauseBalance(card) {
                        setFinalBalance(card);
                    }

                    function startBalance(card) {
                        card.querySelectorAll('[data-pa-balance]').forEach(function (el) {
                            var target = balanceValue(el);
                            var token = String(Date.now()) + Math.random();
                            el.dataset.paToken = token;
                            el.textContent = formatBalance(0);

                            var start = null;
                            function frame(now) {
                                if (el.dataset.paToken !== token) return;
                                if (start === null) start = now;
                                var progress = Math.min(1, (now - start) / 1300);
                                var eased = 1 - Math.pow(1 - progress, 3);
                                el.textContent = formatBalance(Math.round(target * eased));
                                if (progress < 1) window.requestAnimationFrame(frame);
                            }

                            window.requestAnimationFrame(frame);
                        });
                    }

                    function revealPromo() {
                        document.querySelectorAll('[data-promo-reveal]').forEach(function (root) {
                            var cards = Array.from(root.querySelectorAll('.promo-reveal-item'));
                            if (!cards.length) return;

                            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
                            var visible = new Set();

                            function play(card) {
                                if (reduce.matches || document.hidden) {
                                    card.classList.remove('is-playing');
                                    pauseBalance(card);
                                    return;
                                }
                                card.classList.add('is-playing');
                                startBalance(card);
                            }

                            if (!('IntersectionObserver' in window) || reduce.matches) {
                                cards.forEach(function (card) {
                                    card.classList.add('is-visible');
                                    setFinalBalance(card);
                                });
                                return;
                            }

                            root.classList.add('promo-reveal-init');

                            var revealObserver = new IntersectionObserver(function (entries) {
                                entries.forEach(function (entry) {
                                    if (!entry.isIntersecting) return;
                                    cards.forEach(function (card, index) {
                                        window.setTimeout(function () { card.classList.add('is-visible'); }, index * 90);
                                    });
                                    revealObserver.unobserve(entry.target);
                                });
                            }, { threshold: 0.12 });
                            revealObserver.observe(root);

                            var playObserver = new IntersectionObserver(function (entries) {
                                entries.forEach(function (entry) {
                                    var card = entry.target;
                                    if (entry.isIntersecting && entry.intersectionRatio >= 0.2) {
                                        visible.add(card);
                                        play(card);
                                    } else {
                                        visible.delete(card);
                                        card.classList.remove('is-playing');
                                        pauseBalance(card);
                                    }
                                });
                            }, { threshold: [0, 0.2, 0.5] });
                            cards.forEach(function (card) { playObserver.observe(card); });

                            document.addEventListener('visibilitychange', function () {
                                if (document.hidden) {
                                    cards.forEach(function (card) {
                                        card.classList.remove('is-playing');
                                        pauseBalance(card);
                                    });
                                    return;
                                }
                                cards.forEach(function (card) {
                                    if (visible.has(card)) play(card);
                                });
                            });

                            if (typeof reduce.addEventListener === 'function') {
                                reduce.addEventListener('change', function (event) {
                                    if (event.matches) {
                                        cards.forEach(function (card) {
                                            card.classList.add('is-visible');
                                            card.classList.remove('is-playing');
                                            setFinalBalance(card);
                                        });
                                        return;
                                    }
                                    cards.forEach(function (card) {
                                        if (visible.has(card)) play(card);
                                    });
                                });
                            }
                        });
                    }

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', revealPromo);
                    } else {
                        revealPromo();
                    }
                })();
            </script>
        @endonce
    </section>
@endif
