<x-app-layout>
    <div x-data="{ filterOpen: false }" class="space-y-6">

        {{-- Hero Search --}}
        <div class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white dark:bg-slate-900 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white dark:bg-slate-900 rounded-full translate-y-1/2 -translate-x-1/4"></div>
            </div>
            <div class="relative">
                <h1 class="text-xl sm:text-2xl font-bold">Cari Kos Impianmu</h1>
                <p class="mt-1.5 text-primary-100/80 text-sm">Temukan kos yang cocok untuk kebutuhanmu.</p>
                <form method="GET" action="{{ route('tenant.kos.index') }}" class="mt-4 flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1 max-w-lg">
                        <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <label for="search-kos" class="sr-only">Cari kos, lokasi, atau fasilitas</label>
                        <input type="text" name="q" id="search-kos" value="{{ request('q') }}" placeholder="Cari kos, lokasi, atau fasilitas..." autocomplete="off"
                               class="w-full pl-10 pr-4 py-3 rounded-xl bg-white/95 dark:bg-slate-900/95 text-slate-900 dark:text-white text-sm placeholder-slate-400 focus:ring-2 focus:ring-white/30 transition border-0 shadow-lg">
                    </div>
                    <button type="button" x-on:click="filterOpen = !filterOpen"
                            class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur text-sm font-semibold transition border border-white/20">
                        <i class="ri-filter-3-line"></i> Filter
                        @if(request()->hasAny(['q', 'loc', 'price_min', 'price_max', 'facilities', 'tersedia_only']) || request('sort', 'terbaru') !== 'terbaru')
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                        @endif
                    </button>
                    <button type="submit" class="px-5 py-3 bg-white text-primary-700 text-sm font-bold rounded-xl hover:bg-primary-50 transition shadow-lg shadow-black/10 shrink-0">Cari</button>
                    @if(request('loc'))
                        <input type="hidden" name="loc" value="{{ request('loc') }}">
                    @endif
                    @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif
                    @if(request('price_min'))
                        <input type="hidden" name="price_min" value="{{ request('price_min') }}">
                    @endif
                    @if(request('price_max'))
                        <input type="hidden" name="price_max" value="{{ request('price_max') }}">
                    @endif
                    @if(request('tersedia_only'))
                        <input type="hidden" name="tersedia_only" value="1">
                    @endif
                    @foreach(request('facilities', []) as $fid)
                        <input type="hidden" name="facilities[]" value="{{ $fid }}">
                    @endforeach
                </form>
            </div>
        </div>

        {{-- Iklan marketplace — advertiser PIHAK KETIGA, JELAS terpisah dari daftar kos organik.
             Daftar kos di bawah TIDAK pernah diboost/diubah oleh iklan ini. --}}
        @if(($marketplaceAds ?? collect())->isNotEmpty())
            <section aria-label="Partner untuk penghuni kos" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($marketplaceAds as $idx => $ad)
                    @include('tenant.partials.promoted-partner', [
                        'ad' => $ad,
                        'placement' => 'marketplace',
                        'variant' => $idx === 0 ? 'banner' : 'card',
                    ])
                @endforeach
            </section>
        @endif

        {{-- Quick Filter Chips (facilities ACTUAL dari master data) --}}
        @if($fasilitasList->isNotEmpty())
            <div class="flex items-start gap-2 flex-wrap" aria-label="Filter cepat">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mt-2 shrink-0">Cari cepat:</span>
                <div class="flex flex-wrap gap-2">
                    @foreach($fasilitasList->take(8) as $f)
                        @php
                            $activeFids = request('facilities', []);
                            $isActive = in_array($f->id, $activeFids);
                            $nextFids = $isActive
                                ? array_values(array_diff($activeFids, [$f->id]))
                                : array_values(array_merge($activeFids, [$f->id]));
                        @endphp
                        <a href="{{ request()->fullUrlWithQuery(['facilities' => $nextFids ?: null]) }}"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-full border transition {{ $isActive ? 'bg-primary-500 text-white border-primary-500 shadow-sm shadow-primary-500/30' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-500/50' }}">
                            @if($f->icon)
                                <i class="{{ $f->icon }} text-[11px]"></i>
                            @endif
                            {{ $f->name }}
                            @if($isActive)
                                <i class="ri-check-line text-[11px]"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Location / Address Discovery Chips --}}
        @if(($locations ?? collect())->isNotEmpty())
            <div class="flex items-start gap-2 flex-wrap" aria-label="Jelajahi berdasarkan lokasi">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mt-2 shrink-0">Lokasi:</span>
                <div class="flex flex-wrap gap-2">
                    @foreach($locations->take(10) as $loc)
                        @php $locActive = $selectedLocation === $loc->address; @endphp
                        <a href="{{ $locActive ? request()->fullUrlWithQuery(['loc' => null]) : request()->fullUrlWithQuery(['loc' => $loc->address, 'page' => null]) }}"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-full border transition {{ $locActive ? 'bg-primary-500 text-white border-primary-500 shadow-sm shadow-primary-500/30' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-500/50' }}"
                           title="{{ $loc->address }}">
                            <i class="ri-map-pin-2-line text-[11px]"></i>
                            <span class="max-w-[12rem] truncate">{{ $loc->address }}</span>
                            <span class="opacity-70">{{ $loc->kos_count }}</span>
                            @if($locActive)
                                <i class="ri-close-line text-[11px]"></i>
                            @endif
                        </a>
                    @endforeach
                    @if($selectedLocation && !$locations->contains('address', $selectedLocation))
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-full border bg-primary-500 text-white border-primary-500 shadow-sm shadow-primary-500/30" title="{{ $selectedLocation }}">
                            <i class="ri-map-pin-2-line text-[11px]"></i>
                            <span class="max-w-[12rem] truncate">{{ $selectedLocation }}</span>
                        </span>
                    @endif
                </div>
            </div>
        @endif

        {{-- Filter Drawer (slide-in) --}}
        <div x-show="filterOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50">
            {{-- Overlay: blocks taps on mobile, transparent on desktop so results stay visible --}}
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm lg:bg-black/20 lg:pointer-events-none" x-on:click="filterOpen = false"></div>
            <div class="absolute inset-y-0 right-0 w-full max-w-sm bg-white dark:bg-slate-900 shadow-2xl overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                <form method="GET" action="{{ route('tenant.kos.index') }}" class="flex flex-col h-full">
                    <div class="sticky top-0 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 px-6 py-4 flex items-center justify-between z-10">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Filter & Urutkan</h3>
                        <button type="button" x-on:click="filterOpen = false"
                                class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                aria-label="Tutup filter">
                            <i class="ri-close-line text-lg"></i>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        @if(request('q'))
                            <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif
                        @if(request('loc'))
                            <input type="hidden" name="loc" value="{{ request('loc') }}">
                        @endif

                        {{-- Sort --}}
                        <div>
                            <label class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Urutkan</label>
                            <div class="mt-2 space-y-2">
                                @foreach([
                                    'terbaru' => 'Terbaru',
                                    'harga_terendah' => 'Harga Terendah',
                                    'harga_tertinggi' => 'Harga Tertinggi',
                                    'terbanyak_disewa' => 'Terbanyak Disewa',
                                    'paling_banyak_favorit' => 'Paling Banyak Favorit',
                                ] as $val => $label)
                                    <label class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition border {{ request('sort', 'terbaru') === $val ? 'bg-primary-50 dark:bg-primary-500/10 border-primary-300 dark:border-primary-500/40' : 'bg-white dark:bg-slate-900 border-slate-100 dark:border-slate-700 hover:border-primary-200 dark:hover:border-primary-500/30' }}">
                                        <input type="radio" name="sort" value="{{ $val }}" {{ request('sort', 'terbaru') === $val ? 'checked' : '' }}
                                               class="w-4 h-4 border-slate-300 dark:border-slate-600 text-primary-500 focus:ring-primary-500/20">
                                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Price --}}
                        <div>
                            <label class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Harga per Bulan</label>
                            <div class="mt-2 flex items-center gap-3">
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">Rp</span>
                                    <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="Min" min="0"
                                           class="pl-10 pr-3 py-2.5 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition">
                                </div>
                                <span class="text-slate-400">—</span>
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">Rp</span>
                                    <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="Max" min="0"
                                           class="pl-10 pr-3 py-2.5 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition">
                                </div>
                            </div>
                        </div>

                        {{-- Availability --}}
                        <div>
                            <label class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Ketersediaan</label>
                            <div class="mt-2">
                                <label class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition border {{ request('tersedia_only') ? 'bg-green-50 dark:bg-green-500/10 border-green-300 dark:border-green-500/40' : 'bg-white dark:bg-slate-900 border-slate-100 dark:border-slate-700 hover:border-primary-200 dark:hover:border-primary-500/30' }}">
                                    <input type="checkbox" name="tersedia_only" value="1" {{ request('tersedia_only') ? 'checked' : '' }}
                                           class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-primary-500 focus:ring-primary-500/20">
                                    <div class="flex items-center gap-2">
                                        <i class="ri-door-open-line text-green-500"></i>
                                        <span class="text-sm text-slate-700 dark:text-slate-300">Hanya yang tersedia</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Facilities (categorized accordion) --}}
                        @if($fasilitasList->isNotEmpty())
                            @php
                                $catLabels = [
                                    'room' => 'Fasilitas Kamar',
                                    'bathroom' => 'Kamar Mandi',
                                    'common' => 'Fasilitas Bersama',
                                    'parking' => 'Parkir',
                                    'security' => 'Keamanan',
                                    'service' => 'Layanan',
                                    'lainnya' => 'Fasilitas Lainnya',
                                ];
                            @endphp
                            <div x-data="{ openFacility: @json(array_key_first($facilityCategories) ?: null) }">
                                <label class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Fasilitas</label>
                                <div class="mt-2 space-y-2">
                                    @foreach($facilityCategories as $catKey => $facilities)
                                        @php
                                            $label = $catLabels[$catKey] ?? 'Fasilitas';
                                            $activeInGroup = collect(request('facilities', []))->intersect($facilities->pluck('id'))->isNotEmpty();
                                        @endphp
                                        <div class="rounded-xl border border-slate-100 dark:border-slate-800 overflow-hidden">
                                            <button type="button"
                                                    class="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 text-left bg-slate-50/60 dark:bg-slate-800/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40"
                                                    @click="openFacility = openFacility === '{{ $catKey }}' ? null : '{{ $catKey }}'"
                                                    :aria-expanded="(openFacility === '{{ $catKey }}').toString()">
                                                <span class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-200">
                                                    <i class="ri-checkbox-blank-circle-line text-[11px] text-primary-400"></i> {{ $label }}
                                                    @if($activeInGroup)
                                                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                                    @endif
                                                </span>
                                                <i class="ri-arrow-down-s-line text-slate-400 transition-transform" :class="openFacility === '{{ $catKey }}' ? 'rotate-180' : ''"></i>
                                            </button>
                                            <div x-show="openFacility === '{{ $catKey }}'" x-cloak x-transition
                                                 class="px-3.5 py-3 flex flex-wrap gap-2">
                                                @foreach($facilities as $f)
                                                    @php $checked = in_array($f->id, request('facilities', [])); @endphp
                                                    <label class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg cursor-pointer transition border {{ $checked ? 'bg-primary-50 dark:bg-primary-500/10 border-primary-300 dark:border-primary-500/40 text-primary-700 dark:text-primary-300' : 'bg-white dark:bg-slate-900 border-slate-100 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-primary-300 dark:hover:border-primary-500/50' }}">
                                                        <input type="checkbox" name="facilities[]" value="{{ $f->id }}" {{ $checked ? 'checked' : '' }}
                                                               class="sr-only">
                                                        @if($f->icon)
                                                            <i class="{{ $f->icon }} text-sm"></i>
                                                        @endif
                                                        {{ $f->name }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="sticky bottom-0 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 px-6 py-4 flex items-center gap-3">
                        <a href="{{ route('tenant.kos.index') }}" class="flex-1 text-center text-sm font-semibold py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Reset
                        </a>
                        <button type="submit" x-on:click="filterOpen = false"
                                class="flex-1 text-center text-sm font-bold py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white transition shadow-sm shadow-primary-500/30">
                            Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Active Filter Pills --}}
        @if(request()->hasAny(['q', 'loc', 'price_min', 'price_max', 'facilities', 'tersedia_only']) || request('sort', 'terbaru') !== 'terbaru')
            <div class="flex flex-wrap items-center gap-2">
                @if(request('loc'))
                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-full">
                        <i class="ri-map-pin-2-line text-[10px]"></i>
                        <span class="max-w-[14rem] truncate">{{ request('loc') }}</span>
                        <a href="{{ request()->fullUrlWithQuery(['loc' => null]) }}" class="ml-0.5 hover:text-slate-900 dark:hover:text-white" aria-label="Hapus lokasi">&times;</a>
                    </span>
                @endif
                @if(request('q'))
                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-500/30 px-2.5 py-1 rounded-full">
                        {{ request('q') }}
                        <a href="{{ request()->fullUrlWithQuery(['q' => null]) }}" class="ml-0.5 hover:text-primary-900 dark:hover:text-primary-100" aria-label="Hapus pencarian">&times;</a>
                    </span>
                @endif
                @if(request('price_min') || request('price_max'))
                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-full">
                        @if(request('price_min') && request('price_max'))
                            Rp {{ number_format(request('price_min'), 0, ',', '.') }} - {{ number_format(request('price_max'), 0, ',', '.') }}
                        @elseif(request('price_min'))
                            &ge; Rp {{ number_format(request('price_min'), 0, ',', '.') }}
                        @else
                            &le; Rp {{ number_format(request('price_max'), 0, ',', '.') }}
                        @endif
                        <a href="{{ request()->fullUrlWithQuery(['price_min' => null, 'price_max' => null]) }}" class="ml-0.5 hover:text-slate-900 dark:hover:text-white" aria-label="Hapus harga">&times;</a>
                    </span>
                @endif
                @foreach(request('facilities', []) as $fid)
                    @php $fac = $fasilitasList->firstWhere('id', $fid); @endphp
                    @if($fac)
                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-full">
                            {{ $fac->name }}
                            <a href="{{ request()->fullUrlWithQuery(['facilities' => collect(request('facilities', []))->filter(fn ($v) => $v != $fid)->values()->all() ?: null]) }}" class="ml-0.5 hover:text-slate-900 dark:hover:text-white" aria-label="Hapus {{ $fac->name }}">&times;</a>
                        </span>
                    @endif
                @endforeach
                @if(request('tersedia_only'))
                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/30 px-2.5 py-1 rounded-full">
                        <i class="ri-door-open-line text-[10px]"></i> Tersedia
                        <a href="{{ request()->fullUrlWithQuery(['tersedia_only' => null]) }}" class="ml-0.5 hover:text-green-900 dark:hover:text-green-100" aria-label="Hapus filter tersedia">&times;</a>
                    </span>
                @endif
                @if(request('sort', 'terbaru') !== 'terbaru')
                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-full">
                        @switch(request('sort'))
                            @case('harga_terendah') Harga Terendah @break
                            @case('harga_tertinggi') Harga Tertinggi @break
                            @case('terbanyak_disewa') Terbanyak Disewa @break
                            @case('paling_banyak_favorit') Paling Banyak Favorit @break
                        @endswitch
                        <a href="{{ request()->fullUrlWithQuery(['sort' => null]) }}" class="ml-0.5 hover:text-slate-900 dark:hover:text-white" aria-label="Hapus urutan">&times;</a>
                    </span>
                @endif
                <a href="{{ route('tenant.kos.index') }}" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-primary-500 transition">
                    <i class="ri-close-circle-line"></i> Reset
                </a>
            </div>
        @endif

        {{-- Result Count --}}
        @if(!$kosList->isEmpty())
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p role="status" class="text-sm text-slate-500 dark:text-slate-400">
                    {{ $kosList->total() }} kos ditemukan
                    @if(request('q'))
                        — hasil pencarian untuk "<span class="font-semibold text-primary-600 dark:text-primary-300">{{ request('q') }}</span>"
                    @endif
                </p>

                {{-- Sort dropdown --}}
                <form method="GET" action="{{ route('tenant.kos.index') }}" class="hidden sm:flex items-center gap-2">
                    @foreach(['q', 'loc', 'price_min', 'price_max', 'tersedia_only'] as $param)
                        @if(request($param))
                            <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                        @endif
                    @endforeach
                    @foreach(request('facilities', []) as $fid)
                        <input type="hidden" name="facilities[]" value="{{ $fid }}">
                    @endforeach
                    <label for="sort-top" class="text-xs font-semibold text-slate-400 dark:text-slate-500">Urutkan</label>
                    <select id="sort-top" name="sort" onchange="this.form.submit()"
                            class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 py-2 pl-3 pr-8 focus:border-primary-500 focus:ring-primary-500/10">
                        @foreach([
                            'terbaru' => 'Terbaru',
                            'harga_terendah' => 'Harga Terendah',
                            'harga_tertinggi' => 'Harga Tertinggi',
                            'terbanyak_disewa' => 'Terbanyak Disewa',
                            'paling_banyak_favorit' => 'Paling Banyak Favorit',
                        ] as $val => $label)
                            <option value="{{ $val }}" {{ request('sort', 'terbaru') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif

        @if($kosList->isEmpty())
            {{-- Empty State --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto">
                    @if(request('q'))
                        <i class="ri-search-line text-3xl text-slate-300 dark:text-slate-600"></i>
                    @else
                        <i class="ri-home-search-line text-3xl text-slate-300 dark:text-slate-600"></i>
                    @endif
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                    @if(request('q'))
                        Kos tidak ditemukan
                    @elseif(request('loc'))
                        Kos tidak ditemukan di lokasi ini
                    @else
                        Kos belum tersedia
                    @endif
                </h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    @if(request('q'))
                        Pencarian untuk "{{ request('q') }}" tidak menemukan kos yang sesuai.
                    @elseif(request('loc'))
                        Tidak ada kos yang tersedia di alamat "{{ request('loc') }}". Coba lokasi lain atau hapus filter lokasi.
                    @else
                        Belum ada kos yang terdaftar di sistem saat ini.
                    @endif
                </p>
                <div class="mt-6 flex items-center justify-center gap-3">
                    @if(request('q'))
                        <a href="{{ route('tenant.kos.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary-500 hover:text-primary-600 border border-primary-200 dark:border-primary-500/30 hover:bg-primary-50 dark:hover:bg-primary-500/10 px-4 py-2.5 rounded-xl transition">
                            <i class="ri-close-circle-line"></i> Hapus Pencarian
                        </a>
                    @elseif(request('loc') && !request()->hasAny(['q', 'price_min', 'price_max', 'facilities', 'tersedia_only']))
                        <a href="{{ route('tenant.kos.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary-500 hover:text-primary-600 border border-primary-200 dark:border-primary-500/30 hover:bg-primary-50 dark:hover:bg-primary-500/10 px-4 py-2.5 rounded-xl transition">
                            <i class="ri-close-circle-line"></i> Hapus Lokasi
                        </a>
                    @endif
                    <a href="{{ route('tenant.kos.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 px-4 py-2.5 rounded-xl transition">
                        <i class="ri-eye-line"></i> Lihat Semua Kos
                    </a>
                </div>
            </div>
        @else
            {{-- Kos Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($kosList as $kos)
                    @include('tenant.partials.kos-card', ['kos' => $kos, 'favoritedIds' => $favoritedIds])
                @endforeach
            </div>

            <div class="flex justify-center">{{ $kosList->links() }}</div>
        @endif
    </div>
</x-app-layout>
