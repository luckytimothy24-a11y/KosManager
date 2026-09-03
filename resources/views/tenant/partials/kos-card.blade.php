@php
    $isFav = in_array($kos->id, $favoritedIds ?? [], true);
    $availableRooms = (int) ($kos->kamar_tersedia ?? 0);
    $generalFasilitas = $kos->fasilitas->isNotEmpty()
        ? $kos->fasilitas->pluck('name')->values()
        : collect(preg_split('/[,;]/', (string) $kos->general_facilities))
            ->map(fn ($f) => trim($f))
            ->filter()
            ->values();
    $kamarFacilities = $kos->kamar->first()?->fasilitas ?? collect();
@endphp

<div class="relative group bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden hover:shadow-xl hover:-translate-y-1 hover:border-primary-200 dark:hover:border-primary-500/30 transition-all duration-200 flex flex-col">
    <a href="{{ route('tenant.kos.show', $kos) }}" class="block relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700" aria-label="Lihat detail {{ $kos->name }}">
        @if($kos->photo)
            <img src="{{ asset('storage/' . $kos->photo) }}" alt="Foto {{ $kos->name }}"
                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                 loading="lazy"
                 onerror="this.style.display='none'">
        @else
            <span class="absolute inset-0 flex items-center justify-center text-[6rem] leading-none font-black uppercase text-primary-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($kos->name, 0, 1) }}</span>
        @endif

        {{-- Availability badge --}}
        <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1.5 rounded-lg shadow-sm {{ $availableRooms > 0 ? 'bg-white/95 dark:bg-slate-900/95 backdrop-blur text-green-700 dark:text-green-300' : 'bg-slate-800/90 dark:bg-slate-950/90 backdrop-blur text-slate-200' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $availableRooms > 0 ? 'bg-green-500' : 'bg-slate-400' }}"></span>
            {{ $availableRooms > 0 ? 'TERSEDIA · '.$availableRooms.' kamar' : 'PENUH' }}
        </span>
    </a>

    {{-- Favorite toggle (on image) --}}
    @auth
        <form method="POST" action="{{ route('tenant.favorites.toggle') }}" class="absolute top-3 right-3 z-10"
              x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            <input type="hidden" name="kos_id" value="{{ $kos->id }}">
            <button type="submit" :disabled="submitting"
                    class="w-9 h-9 rounded-full flex items-center justify-center shadow-sm backdrop-blur transition transform-gpu active:scale-90 disabled:opacity-60 disabled:cursor-wait {{ $isFav ? 'bg-white/95 text-red-500' : 'bg-black/25 text-white hover:bg-white/90 hover:text-red-500' }}"
                    aria-label="{{ $isFav ? 'Hapus dari favorit' : 'Tambahkan ke favorit' }}"
                    title="{{ $isFav ? 'Hapus dari favorit' : 'Tambahkan ke favorit' }}">
                <i class="{{ $isFav ? 'ri-heart-3-fill' : 'ri-heart-3-line' }} text-lg"></i>
            </button>
        </form>
    @endauth

    <div class="p-4 flex flex-col flex-1">
        <div class="flex items-start justify-between gap-2">
            <a href="{{ route('tenant.kos.show', $kos) }}" class="min-w-0">
                <h3 class="font-bold text-slate-900 dark:text-white leading-snug group-hover:text-primary-500 dark:group-hover:text-primary-300 transition line-clamp-1">{{ $kos->name }}</h3>
            </a>
            @if($kos->favorites_count > 0)
                <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-semibold text-slate-400" title="{{ $kos->favorites_count }} favorit">
                    <i class="ri-heart-fill text-red-400 text-xs"></i> {{ $kos->favorites_count }}
                </span>
            @endif
        </div>

        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 flex items-start gap-1 line-clamp-1">
            <i class="ri-map-pin-2-fill mt-0.5 shrink-0 text-primary-500"></i> {{ $kos->address }}
        </p>

        {{-- Fasilitas (kamar utama + general) — dot separated, +N jika lebih dari 3 --}}
        @php
            $facilityNames = collect();
            if ($generalFasilitas->isNotEmpty()) {
                $facilityNames = $facilityNames->merge($generalFasilitas);
            }
            if ($kamarFacilities->isNotEmpty()) {
                $facilityNames = $facilityNames->merge($kamarFacilities->pluck('name'));
            }
            $facilityNames = $facilityNames->unique()->values();
        @endphp
        @if($facilityNames->isNotEmpty())
            <div class="mt-2.5 text-xs text-slate-500 dark:text-slate-400">
                @foreach($facilityNames->take(3) as $idx => $fn)
                    @if($idx > 0)
                        <span class="mx-1 text-slate-300 dark:text-slate-600">·</span>
                    @endif
                    <span class="inline-flex items-center gap-1">
                        @if($idx === 0 && $generalFasilitas->isNotEmpty())
                            <i class="ri-building-2-line text-primary-400 text-[11px]"></i>
                        @else
                            <i class="ri-check-line text-green-500 text-[11px]"></i>
                        @endif
                        {{ $fn }}
                    </span>
                @endforeach
                @if($facilityNames->count() > 3)
                    <span class="text-primary-500 font-semibold"> +{{ $facilityNames->count() - 3 }} fasilitas</span>
                @endif
            </div>
        @endif

        <div class="mt-auto pt-3 border-t border-slate-100 dark:border-slate-800 flex items-end justify-between gap-3">
            @if(!is_null($kos->harga_mulai))
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mulai dari</p>
                    <p class="text-base font-black text-slate-900 dark:text-white leading-tight">Rp {{ number_format($kos->harga_mulai, 0, ',', '.') }}<span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">/bln</span></p>
                </div>
            @elseif(!is_null($kos->harga_harian_mulai))
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mulai dari</p>
                    <p class="text-base font-black text-slate-900 dark:text-white leading-tight">Rp {{ number_format($kos->harga_harian_mulai, 0, ',', '.') }}<span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">/hr</span></p>
                </div>
            @else
                <p class="text-xs text-slate-400 dark:text-slate-500">Harga hubungi pemilik</p>
            @endif
            <a href="{{ route('tenant.kos.show', $kos) }}"
               class="shrink-0 inline-flex items-center gap-1.5 text-xs font-bold text-white bg-primary-500 hover:bg-primary-600 px-3.5 py-2 rounded-xl transition shadow-sm shadow-primary-500/30 active:scale-95">
                Lihat Detail <i class="ri-arrow-right-line"></i>
            </a>
        </div>
    </div>
</div>
