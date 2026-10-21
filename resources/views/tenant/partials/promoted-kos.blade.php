@php
    // Promosi kos OWNER (kampanye is_featured / is_sponsored / is_homepage dengan
    // kos_id TIDAK NULL). Jelas berlabel iklan, terpisah dari listing organik.
    // Link memakai ad.click agar klik tercatat + atribusi conversion hanya
    // berlaku untuk kos ini (bukan advertiser pihak ketiga).
    // Anatomy card disamakan dengan card organik (foto → nama → alamat →
    // fasilitas → harga + CTA); status iklan tetap di body/footer.
    $campaign = $campaign ?? null;
    $placement = $placement ?? 'marketplace';
    $kos = $campaign?->kos;
    $placementLabel = \App\Support\AdvertisingLabels::placementLabel($campaign?->placement ?? $placement);
    $placementBadge = \App\Support\AdvertisingLabels::placementBadge($campaign?->placement ?? $placement);
    $legacyLabel = $placement === 'homepage' ? 'Promosi Beranda' : ((bool)($campaign->is_featured ?? false) ? 'Kos Featured' : 'Kos Sponsored');
    $availableRooms = (int) ($kos->kamar_tersedia ?? 0);
    $isFav = in_array($kos->id, $favoritedIds ?? [], true);

    // Highlight fasilitas yang sedang difilter (jika ada di card promosi).
    $activeFilterNames = collect();
    if (request()->filled('facilities') && isset($fasilitasList)) {
        $activeFilterNames = $fasilitasList
            ->whereIn('id', array_map('intval', (array) request('facilities', [])))
            ->pluck('name')
            ->map(fn ($n) => mb_strtolower(trim($n)))
            ->filter()
            ->values();
    }

    $promotedFacilityNames = collect();
    if ($kos->fasilitas) {
        $promotedFacilityNames = $promotedFacilityNames->merge($kos->fasilitas->pluck('name'));
    }
    if ($kos->general_facilities) {
        $promotedFacilityNames = $promotedFacilityNames->merge(
            collect(preg_split('/[,;]/', (string) $kos->general_facilities))
                ->map(fn ($f) => trim($f))
                ->filter()
        );
    }
    foreach ($kos->kamar ?? collect() as $kamar) {
        if ($kamar->fasilitas) {
            $promotedFacilityNames = $promotedFacilityNames->merge($kamar->fasilitas->pluck('name'));
        }
    }
    $promotedFacilityNames = $promotedFacilityNames->unique()->values();

    if ($activeFilterNames->isNotEmpty()) {
        $matchedPromo = $promotedFacilityNames->filter(fn ($n) => $activeFilterNames->contains(mb_strtolower($n)));
        $restPromo = $promotedFacilityNames->reject(fn ($n) => $activeFilterNames->contains(mb_strtolower($n)));
        $promotedFacilityNames = $matchedPromo->merge($restPromo)->values();
    }
@endphp

@if($campaign && $kos)
    @php
        $adUrl = route('ad.click', ['campaign' => $campaign->id, 'placement' => $placement]);
        $coverPath = method_exists($kos, 'coverPath') ? $kos->coverPath() : $kos->photo;
        $initials = strtoupper(mb_substr(preg_replace('/\s+/', '', $kos->name), 0, 2));
    @endphp
    <div class="group relative flex flex-col card-property border-amber-200/70 dark:border-amber-500/20 hover:border-amber-300 dark:hover:border-amber-500/40">
        {{-- Foto — bagian paling atas card --}}
        <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-amber-100/60 via-primary-50 to-blue-50 dark:from-surface-elevated dark:via-surface-elevated dark:to-surface-elevated">
            <a href="{{ $adUrl }}" class="absolute inset-0 z-[1]" aria-label="Lihat detail {{ $kos->name }}">
                @if($coverPath)
                    <img src="{{ asset('storage/' . $coverPath) }}" alt="Foto {{ $kos->name }}"
                         class="card-property-img absolute inset-0 w-full h-full object-cover"
                         loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="absolute inset-0 hidden items-center justify-center bg-gradient-to-br from-amber-100/60 via-primary-50 to-blue-50 dark:from-surface-elevated dark:via-surface-elevated dark:to-surface-elevated">
                        <span class="text-4xl font-bold text-amber-300/50 dark:text-amber-400/20 select-none">{{ $initials }}</span>
                    </div>
                @else
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="flex flex-col items-center gap-2 group-hover:scale-105 transition-transform duration-500">
                            <span class="text-4xl font-bold text-amber-300/50 dark:text-amber-400/20 select-none">{{ $initials }}</span>
                            <span class="text-[10px] font-medium text-amber-300/50 dark:text-amber-400/30">Foto belum tersedia</span>
                        </div>
                    </div>
                @endif
            </a>

            {{-- Availability badge — top left --}}
            <span class="absolute top-3 left-3 z-10 inline-flex items-center gap-1.5 rounded-full text-[11px] font-semibold px-2.5 py-0.5 shadow-sm backdrop-blur-sm {{ $availableRooms > 0 ? 'bg-white/90 dark:bg-surface-elevated/90 text-green-700 dark:text-green-300' : 'bg-slate-800/90 text-white/90' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $availableRooms > 0 ? 'bg-green-500 badge-pulse' : 'bg-slate-400' }}"></span>
                {{ $availableRooms > 0 ? 'TERSEDIA' : 'PENUH' }}
            </span>

            {{-- Favorite toggle — top right --}}
            @auth
                <form method="POST" action="{{ route('tenant.favorites.toggle') }}" class="absolute top-3 right-3 z-10"
                      x-data="{ submitting: false }" x-on:submit.stop.prevent="submitting = true; $el.submit();">
                    @csrf
                    <input type="hidden" name="kos_id" value="{{ $kos->id }}">
                    <button type="submit" :disabled="submitting"
                            class="w-9 h-9 rounded-full flex items-center justify-center shadow-sm backdrop-blur-sm transition-all duration-300 transform-gpu active:scale-90 disabled:opacity-60 disabled:cursor-wait {{ $isFav ? 'bg-white/95 text-red-500 hover:text-red-600' : 'bg-black/20 text-white/90 hover:bg-white/95 hover:text-red-500' }}"
                            aria-label="{{ $isFav ? 'Hapus dari favorit' : 'Tambahkan ke favorit' }}"
                            title="{{ $isFav ? 'Hapus dari favorit' : 'Tambahkan ke favorit' }}">
                        <i class="{{ $isFav ? 'ri-heart-3-fill' : 'ri-heart-3-line' }} text-base"></i>
                    </button>
                </form>
            @endauth
        </div>

        <div class="p-4 flex flex-col flex-1">
            {{-- Name — tepat di bawah foto --}}
            <a href="{{ $adUrl }}" class="block">
                <h3 class="font-semibold not-italic text-[15px] sm:text-base text-slate-900 dark:text-slate-50 leading-snug line-clamp-2 break-words group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-300">{{ $kos->name }}</h3>
            </a>

            {{-- Location --}}
            <p class="mt-1.5 text-[13px] text-slate-500 dark:text-slate-500 flex items-start gap-1 line-clamp-1">
                <i class="ri-map-pin-2-fill mt-0.5 shrink-0 text-primary-400"></i>
                <span>{{ $kos->address }}</span>
            </p>

            @php
                $promotedDirectionsUrl = $kos->googleMapsDirectionsUrl();
            @endphp
            @if($promotedDirectionsUrl)
                <a href="{{ $promotedDirectionsUrl }}"
                   target="_blank" rel="noopener noreferrer"
                   aria-label="Buka petunjuk arah ke {{ $kos->name }}"
                   title="Buka petunjuk arah ke {{ $kos->name }} di Google Maps"
                   class="mt-2 inline-flex items-center gap-1 text-[10px] font-semibold text-primary-500 hover:text-primary-600 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/50 rounded">
                    <i class="ri-navigation-line text-[10px]"></i> Arah ke Kos
                </a>
            @endif

            {{-- Facilities --}}
            @if($promotedFacilityNames->isNotEmpty())
                <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[10px] leading-relaxed">
                    @foreach($promotedFacilityNames->take(3) as $fname)
                        @php $isFilteredPromoFac = $activeFilterNames->contains(mb_strtolower($fname)); @endphp
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 {{ $isFilteredPromoFac ? 'bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-300 font-semibold ring-1 ring-primary-200 dark:ring-primary-500/30' : 'bg-slate-50/80 dark:bg-white/[0.04] text-slate-500 dark:text-slate-400 font-medium' }}">
                            @if($isFilteredPromoFac)
                                <i class="ri-check-line text-[9px]"></i>
                            @endif
                            {{ $fname }}
                        </span>
                    @endforeach
                    @if($promotedFacilityNames->count() > 3)
                        <span class="text-primary-500 font-semibold">+{{ $promotedFacilityNames->count() - 3 }} fasilitas</span>
                    @endif
                </div>
            @endif

            {{-- Price & CTA — bottom aligned, pola sama dengan card organik --}}
            <div class="mt-auto pt-3 border-t border-slate-100/80 dark:border-white/[0.08] flex items-end justify-between gap-3">
                @if(!is_null($kos->harga_mulai))
                    <div class="min-w-0">
                        <p class="text-meta font-medium uppercase tracking-wide">Mulai dari</p>
                        <p class="text-lg font-semibold tracking-tight text-slate-900 dark:text-slate-50 leading-none mt-0.5">Rp{{ number_format($kos->harga_mulai, 0, ',', '.') }}<span class="text-meta">/bln</span></p>
                    </div>
                @elseif(!is_null($kos->harga_harian_mulai))
                    <div class="min-w-0">
                        <p class="text-meta font-medium uppercase tracking-wide">Mulai dari</p>
                        <p class="text-lg font-semibold tracking-tight text-slate-900 dark:text-slate-50 leading-none mt-0.5">Rp{{ number_format($kos->harga_harian_mulai, 0, ',', '.') }}<span class="text-meta">/hr</span></p>
                    </div>
                @else
                    <p class="text-xs text-slate-400 dark:text-slate-500">Harga hubungi pemilik</p>
                @endif
                <a href="{{ $adUrl }}" class="shrink-0 inline-flex items-center gap-1 text-xs font-semibold text-primary-500 group-hover:text-primary-600 transition-all duration-300">
                    Lihat Detail <i class="ri-arrow-right-line text-[11px] group-hover:translate-x-0.5 transition-transform duration-300"></i>
                </a>
            </div>

            {{-- Ad attribution — status iklan tetap jelas di body --}}
            <p class="mt-3 pt-2 border-t border-slate-100 dark:border-white/[0.08] inline-flex items-center gap-1.5 text-meta">
                <i class="ri-information-line"></i> Iklan · {{ $legacyLabel }}
            </p>
        </div>
    </div>
@endif
