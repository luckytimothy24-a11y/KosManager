@php
    // Promosi kos OWNER (kampanye is_featured / is_sponsored / is_homepage dengan
    // kos_id TIDAK NULL). Jelas berlabel iklan, terpisah dari listing organik.
    // Link memakai tenant.ad.click agar klik tercatat + atribusi conversion hanya
    // berlaku untuk kos ini (bukan advertiser pihak ketiga).
    $campaign = $campaign ?? null;
    $placement = $placement ?? 'marketplace';
    $kos = $campaign?->kos;
    $featuredBadge = $placement === 'homepage'
        ? false
        : (bool) ($campaign->is_featured ?? false);
    $label = $placement === 'homepage' ? 'Promosi Beranda' : ($featuredBadge ? 'Kos Featured' : 'Kos Sponsored');
    $availableRooms = (int) ($kos->kamar_tersedia ?? 0);
@endphp

@if($campaign && $kos)
    <a href="{{ route('tenant.ad.click', ['campaign' => $campaign->id, 'placement' => $placement]) }}"
       class="group relative block bg-white dark:bg-slate-900 rounded-2xl border border-amber-200/70 dark:border-amber-500/20 overflow-hidden hover:shadow-xl hover:-translate-y-1 hover:border-amber-300 dark:hover:border-amber-500/40 transition-all duration-200">
        <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700">
            @if($kos->photo)
                <img src="{{ asset('storage/' . $kos->photo) }}" alt="Foto {{ $kos->name }}" loading="lazy"
                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                     onerror="this.style.display='none'">
            @else
                <span class="absolute inset-0 flex items-center justify-center text-[6rem] leading-none font-black uppercase text-primary-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($kos->name, 0, 1) }}</span>
            @endif

            <span class="absolute top-3 left-3 inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider px-2.5 py-1.5 rounded-lg shadow-sm {{ $featuredBadge ? 'bg-amber-500 text-white' : 'bg-indigo-600 text-white' }}">
                <i class="ri-{{ $featuredBadge ? 'star-fill' : 'megaphone-fill' }} text-[10px]"></i> {{ $label }}
            </span>
        </div>

        <div class="p-4">
            <h3 class="font-bold text-slate-900 dark:text-white leading-snug line-clamp-1 group-hover:text-primary-500 dark:group-hover:text-primary-300 transition">{{ $kos->name }}</h3>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 flex items-start gap-1 line-clamp-1">
                <i class="ri-map-pin-2-fill mt-0.5 shrink-0 text-primary-500"></i> {{ $kos->address }}
            </p>

            <div class="mt-3 flex items-end justify-between gap-3">
                <div class="min-w-0">
                    @if($availableRooms > 0)
                        @if(!is_null($kos->harga_mulai))
                            <p class="text-sm font-black text-slate-900 dark:text-white leading-tight">Rp {{ number_format($kos->harga_mulai, 0, ',', '.') }}<span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">/bln</span></p>
                        @elseif(!is_null($kos->harga_harian_mulai))
                            <p class="text-sm font-black text-slate-900 dark:text-white leading-tight">Rp {{ number_format($kos->harga_harian_mulai, 0, ',', '.') }}<span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">/hr</span></p>
                        @else
                            <p class="text-xs text-slate-400 dark:text-slate-500">Harga hubungi pemilik</p>
                        @endif
                        <p class="text-[10px] font-semibold text-green-600 dark:text-green-400">{{ $availableRooms }} kamar tersedia</p>
                    @else
                        <p class="text-xs text-slate-400 dark:text-slate-500">Kos penuh</p>
                    @endif
                </div>
                <span class="shrink-0 inline-flex items-center gap-1.5 text-xs font-bold text-white bg-primary-500 group-hover:bg-primary-600 px-3.5 py-2 rounded-xl transition shadow-sm shadow-primary-500/30 active:scale-95">
                    Lihat Detail <i class="ri-arrow-right-line"></i>
                </span>
            </div>

            <p class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 dark:text-slate-500 font-medium">Iklan · Promosi Kos</p>
        </div>
    </a>
@endif