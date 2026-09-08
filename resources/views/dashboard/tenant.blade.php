<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-8 pb-6">
        <x-alert />

        {{-- ============================================================
             HEADER — GREETING & RENTAL STATUS
             ============================================================ --}}
        @php
            $isActiveRent = $penghuni && ($stats['kontrak_status'] ?? '-') === 'active';
        @endphp
        <header class="flex items-start justify-between gap-4 pt-1" aria-label="Selamat datang">
            <div class="min-w-0">
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                    Halo, {{ $user->name }} 👋
                </h1>
                @if($penghuni)
                    @if($isActiveRent)
                        <span class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                            Sewa Aktif
                            @if(!empty($stats['kontrak_end']))
                                · Berakhir {{ $stats['kontrak_end']->translatedFormat('d M Y') }}
                            @endif
                        </span>
                    @else
                        <span class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                            Sewa Tidak Aktif
                        </span>
                    @endif
                @else
                    <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">Temukan tempat tinggal yang cocok untukmu.</p>
                @endif
            </div>
            <a href="{{ route('notifications.index') }}"
               class="relative inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-100 bg-white text-slate-400 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-500 hover:text-primary-500 transition"
               aria-label="Notifikasi">
                <i class="ri-notification-3-line text-xl"></i>
                @if(($sidebarBadges['unreadNotif'] ?? 0) > 0)
                    <span class="absolute right-3 top-3 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-slate-900"></span>
                @endif
            </a>
        </header>

        {{-- ============================================================
             PROMO & PARTNER PILIHAN
             Menggunakan data iklan partner homepage yang sudah ada di
             backend (controller melakukan impression tracking). Iklan
             dirender lewat partial partner-ad (premium, data-driven).
             Jika tidak ada iklan live, tampilkan slide layanan statis yang
             sepenuhnya terisolasi dari backend (visual semata).
             ============================================================ --}}
        @php
            $promoSlides = [];
            if (($partnerAds ?? collect())->isNotEmpty()) {
                foreach ($partnerAds as $ad) {
                    $promoSlides[] = [
                        'type' => 'ad',
                        'campaign' => $ad,
                        'headline' => $ad->displayHeadline(),
                    ];
                }
            }
            if (empty($promoSlides)) {
                $promoSlides = [
                    ['type' => 'static', 'label' => 'Layanan', 'headline' => 'Laundry Antar Jemput', 'desc' => 'Praktis, bersih, langsung dari kos.', 'cta' => 'Pesan Sekarang', 'tone' => 'bg-sky-50', 'icon' => 'ri-shirt-line'],
                    ['type' => 'static', 'label' => 'Layanan', 'headline' => 'Cleaning Service Kamar', 'desc' => 'Freskan kamar kamu dalam 1 jam.', 'cta' => 'Jadwalkan', 'tone' => 'bg-emerald-50', 'icon' => 'ri-plant-line'],
                    ['type' => 'static', 'label' => 'Info', 'headline' => 'Promo Pembayaran Tagihan', 'desc' => 'Bayar tunai langsung ke pengelola, praktis tanpa ribet.', 'cta' => 'Bayar Sekarang', 'tone' => 'bg-amber-50', 'icon' => 'ri-wallet-3-line'],
                ];
            }
        @endphp
        <section aria-label="Promo & Partner Pilihan"
                 class="select-none">
            <div class="mb-3">
                <h2 class="text-base font-black text-slate-900 dark:text-white">Promo &amp; Partner Pilihan</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Temukan penawaran menarik untuk menemani kebutuhan harianmu.</p>
            </div>
                 <div x-data="{
                     index: 0,
                     total: {{ count($promoSlides) }},
                     touchX: 0,
                     go(i) { this.index = Math.max(0, Math.min(this.total - 1, i)); },
                     prev() { this.go(this.index - 1); },
                     next() { this.go(this.index + 1); },
                     onTouchStart(e) { this.touchX = e.changedTouches[0].clientX; },
                     onTouchEnd(e) {
                         const dx = e.changedTouches[0].clientX - this.touchX;
                         if (Math.abs(dx) > 40) { dx < 0 ? this.next() : this.prev(); }
                     }
                 }">
            <div class="relative overflow-hidden rounded-[1.25rem]"
                 @touchstart.passive="onTouchStart($event)"
                 @touchend.passive="onTouchEnd($event)">
                <div class="flex transition-transform duration-500 ease-out"
                     :style="'transform: translateX(-' + (index * 100) + '%)'">
                    @foreach($promoSlides as $slide)
                        @if($slide['type'] === 'ad')
                            <div class="w-full shrink-0 px-1 sm:px-1.5">
                                @include('tenant.partials.partner-ad', ['ad' => $slide['campaign'], 'placement' => 'homepage', 'variant' => 'banner'])
                            </div>
                        @else
                            <div class="w-full shrink-0 px-1 sm:px-1.5">
                                <div class="relative flex min-h-[12rem] sm:min-h-[12.5rem] items-center justify-between gap-4 rounded-[1.25rem] {{ $slide['tone'] }} p-5"
                                     role="group" aria-label="{{ $slide['headline'] }}">
                                    <div class="min-w-0">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $slide['label'] }}</span>
                                        <h2 class="mt-1 text-lg font-black leading-snug text-slate-900">{{ $slide['headline'] }}</h2>
                                        <p class="mt-1 text-xs text-slate-500">{{ $slide['desc'] }}</p>
                                    </div>
                                    <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-white/80 text-3xl text-primary-500 shadow-sm">
                                        <i class="{{ $slide['icon'] }}"></i>
                                    </span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                @if(count($promoSlides) > 1)
                    <button type="button" @click="prev()"
                            class="absolute left-3 top-1/2 hidden -translate-y-1/2 rounded-full bg-white/90 p-1.5 text-slate-600 shadow-md transition hover:bg-white active:scale-95 sm:block"
                            aria-label="Slide sebelumnya">
                        <i class="ri-arrow-left-s-line text-lg"></i>
                    </button>
                    <button type="button" @click="next()"
                            class="absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-full bg-white/90 p-1.5 text-slate-600 shadow-md transition hover:bg-white active:scale-95 sm:block"
                            aria-label="Slide berikutnya">
                        <i class="ri-arrow-right-s-line text-lg"></i>
                    </button>
                @endif
            </div>

            @if(count($promoSlides) > 1)
                <div class="mt-3 flex items-center justify-center gap-1.5" role="tablist" aria-label="Pilih slide">
                    @foreach($promoSlides as $i => $slide)
                        <button type="button"
                                @click="go({{ $i }})"
                                :class="index === {{ $i }} ? 'w-5 bg-primary-500' : 'w-1.5 bg-slate-300 dark:bg-slate-700 hover:bg-slate-400'"
                                class="h-1.5 rounded-full transition-all duration-300"
                                :aria-label="'Slide ' + ({{ $i }} + 1)"
                                :aria-current="index === {{ $i }}"></button>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ============================================================
             KOS SAYA — RESIDENCE (hanya untuk penghuni aktif)
             ============================================================ --}}
        @if($penghuni)
            @php
                $kos = $penghuni->kos;
                $mapsUrl = $kos?->googleMapsDirectionsUrl();
            @endphp
            <section aria-label="Kos saya" class="space-y-5">
                <div class="overflow-hidden rounded-[1.25rem] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    {{-- Full-width property photo --}}
                    <div class="relative h-44 w-full overflow-hidden bg-gradient-to-br from-primary-50 via-primary-100 to-emerald-100 sm:h-52 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700">
                        @if(!empty($kos->photo))
                            <img src="{{ asset('storage/'.$kos->photo) }}" alt="Foto {{ $stats['kos_name'] ?? 'Kos' }}" loading="lazy"
                                 class="absolute inset-0 h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center">
                                <i class="ri-building-2-line text-5xl text-primary-300 dark:text-primary-500/40"></i>
                            </div>
                        @endif
                    </div>

                    <div class="p-5">
                        {{-- Kos name (ditampilkan sekali) + kamar badge --}}
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="min-w-0 truncate text-xl font-black tracking-tight text-slate-900 dark:text-white">{{ $stats['kos_name'] ?? '-' }}</h2>
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-primary-50 px-2.5 py-1 text-xs font-bold text-primary-600 dark:bg-primary-500/10 dark:text-primary-300">
                                <i class="ri-door-open-line"></i> Kamar {{ $stats['kamar_number'] ?? '-' }}
                            </span>
                        </div>

                        {{-- Sewa aktif --}}
                        <div class="mt-4 flex items-center gap-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-lg text-emerald-500 dark:bg-emerald-500/10">
                                <i class="ri-calendar-check-line"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-900 dark:text-white">
                                    @if($isActiveRent)
                                        Sewa Aktif
                                    @else
                                        {{ \StatusLabels::kontrakLabel($stats['kontrak_status'] ?? '-') }}
                                    @endif
                                </p>
                                <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">
                                    @if(!empty($stats['kontrak_end']))
                                        Berakhir {{ $stats['kontrak_end']->translatedFormat('d M Y') }}
                                    @else
                                        Kontrak aktif berjalan
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Quick actions --}}
                        <div class="mt-4 grid grid-cols-3 gap-2.5">
                            @if($mapsUrl)
                                <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="flex flex-col items-center gap-1.5 rounded-2xl bg-primary-50 px-2 py-3 text-[11px] font-semibold text-primary-600 transition active:scale-95 dark:bg-primary-500/10 dark:text-primary-300"
                                   aria-label="Buka petunjuk arah menuju {{ $kos->name }} di Google Maps">
                                    <i class="ri-navigation-line text-xl"></i>
                                    Arah ke Kos
                                </a>
                            @endif
                            <a href="{{ route('tenant.kontrak.index') }}"
                               class="flex flex-col items-center gap-1.5 rounded-2xl bg-slate-50 px-2 py-3 text-[11px] font-semibold text-slate-600 transition active:scale-95 dark:bg-slate-800/80 dark:text-slate-300"
                               aria-label="Lihat kontrak sewa">
                                <i class="ri-file-text-line text-xl text-slate-500 dark:text-slate-400"></i>
                                Kontrak Sewa
                            </a>
                            <a href="{{ route('dashboard') }}#bantuan"
                               class="flex flex-col items-center gap-1.5 rounded-2xl bg-slate-50 px-2 py-3 text-[11px] font-semibold text-slate-600 transition active:scale-95 dark:bg-slate-800/80 dark:text-slate-300"
                               aria-label="Lapor kerusakan kamar melalui bantuan">
                                <i class="ri-tools-line text-xl text-slate-500 dark:text-slate-400"></i>
                                Lapor Kerusakan
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan pembayaran (data dari $stats) --}}
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400"><i class="ri-file-list-3-line text-amber-500"></i> Tagihan Belum Bayar</p>
                        <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $stats['tagihan_pending'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400"><i class="ri-time-line text-yellow-500"></i> Menunggu Verifikasi</p>
                        <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $stats['tagihan_pending_verification'] ?? 0 }}</p>
                    </div>
                    <div class="col-span-2 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm lg:col-span-1 dark:border-slate-800 dark:bg-slate-900">
                        <p class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400"><i class="ri-money-dollar-circle-line text-green-500"></i> Total Belum Dibayar</p>
                        <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                            @if(($stats['total_belum_dibayar'] ?? 0) > 0)
                                Rp {{ number_format((float) $stats['total_belum_dibayar'], 0, ',', '.') }}
                            @else
                                Rp 0
                            @endif
                        </p>
                    </div>
                </div>
            </section>

            {{-- ============================================================
                 TAGIHAN BERIKUTNYA — BILLING CARD
                 ============================================================ --}}
            <section aria-label="Tagihan berikutnya">
                <div class="rounded-[1.25rem] border border-slate-100 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-black tracking-tight text-slate-900 dark:text-white">Tagihan Berikutnya</h2>
                        @if(($stats['total_belum_dibayar'] ?? 0) > 0)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                                <i class="ri-error-warning-line"></i> Belum Dibayar
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <i class="ri-check-double-line"></i> Lunas
                            </span>
                        @endif
                    </div>

                    @if(($stats['total_belum_dibayar'] ?? 0) > 0)
                        <p class="mt-4 text-xs font-semibold text-slate-400 dark:text-slate-500">
                            {{ $stats['tagihan_pending'] ?? 0 }} tagihan belum dibayar
                        </p>
                        <p class="mt-1 text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                            Rp {{ number_format((float) $stats['total_belum_dibayar'], 0, ',', '.') }}
                        </p>
                        <p class="mt-2 flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500">
                            <i class="ri-calendar-line"></i>
                            @if(!empty($stats['nearest_due']))
                                Jatuh tempo {{ $stats['nearest_due']->translatedFormat('d M Y') }}
                            @else
                                Jadwal tagihan segera tersedia
                            @endif
                        </p>

                        <a href="{{ route('tenant.tagihan.index') }}"
                           class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-primary-500 px-5 py-3.5 text-sm font-bold text-white shadow-sm shadow-primary-500/30 transition hover:bg-primary-600 active:scale-[0.98]">
                            <i class="ri-wallet-3-line text-lg"></i> Bayar Tagihan
                        </a>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 dark:text-slate-500">
                                <i class="ri-secure-payment-line"></i> Metode pembayaran
                            </span>
                            <span class="rounded-full border border-slate-100 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-300">Tunai ke Pengelola</span>
                            <span class="rounded-full border border-slate-100 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-300">Diverifikasi Pengelola</span>
                        </div>
                    @else
                        <div class="mt-4 flex items-center gap-3 rounded-2xl bg-emerald-50/70 px-4 py-3.5 dark:bg-emerald-500/[0.08]">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-100 text-xl text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <i class="ri-shield-check-line"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">Semua tagihanmu lunas</p>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Tidak ada tagihan yang perlu dibayar saat ini.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- ============================================================
             PERLU PERHATIAN (data existing, no dummy data)
             ============================================================ --}}
        @php
            $attention = [];
            if ($penghuni) {
                if (($stats['tagihan_pending'] ?? 0) > 0) {
                    $attention[] = [
                        'icon' => 'ri-bill-line',
                        'iconClass' => 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400',
                        'title' => $stats['tagihan_pending'].' tagihan belum dibayar',
                        'desc' => 'Cek tagihan dan lakukan pembayaran sebelum jatuh tempo.',
                        'route' => route('tenant.tagihan.index'),
                        'cta' => 'Bayar Sekarang',
                    ];
                }
                if (($stats['tagihan_pending_verification'] ?? 0) > 0) {
                    $attention[] = [
                        'icon' => 'ri-time-line',
                        'iconClass' => 'bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
                        'title' => 'Pembayaran menunggu verifikasi',
                        'desc' => 'Bukti pembayaranmu sedang diverifikasi pengelola.',
                        'route' => route('tenant.pembayaran.index'),
                        'cta' => 'Lihat Status',
                    ];
                }
                if ($pendingCheckout) {
                    $attention[] = [
                        'icon' => 'ri-logout-box-line',
                        'iconClass' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400',
                        'title' => 'Check-out sedang diproses',
                        'desc' => 'Pengajuan check-out kamu menunggu persetujuan pemilik kos.',
                        'route' => route('tenant.kontrak.index'),
                        'cta' => 'Lihat Status',
                    ];
                }
            }
        @endphp

        @if($penghuni)
            <section aria-label="Perlu Perhatian" class="space-y-4">
                <h2 class="flex items-center gap-2 text-base font-black tracking-tight text-slate-900 dark:text-white">
                    <span class="grid h-8 w-8 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-500/10 dark:text-primary-300">
                        <i class="ri-notification-3-line"></i>
                    </span>
                    Perlu Perhatian
                </h2>

                @if(!empty($attention))
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($attention as $item)
                            <a href="{{ $item['route'] }}"
                               class="group flex items-start gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition hover:border-primary-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-500/30">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $item['iconClass'] }}">
                                    <i class="{{ $item['icon'] }} text-lg"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-bold leading-snug text-slate-900 dark:text-white">{{ $item['title'] }}</span>
                                    <span class="mt-0.5 block text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $item['desc'] }}</span>
                                </span>
                                <span class="inline-flex shrink-0 items-center gap-0.5 text-xs font-semibold text-primary-500 transition group-hover:text-primary-600">
                                    {{ $item['cta'] }} <i class="ri-arrow-right-s-line"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center gap-3 rounded-2xl border border-green-100 bg-green-50/60 p-4 dark:border-green-500/20 dark:bg-green-500/[0.06]">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-green-100 text-lg text-green-600 dark:bg-green-500/10 dark:text-green-400">
                            <i class="ri-shield-check-line"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-green-700 dark:text-green-300">Semua pembayaran aman</p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Tidak ada tagihan yang perlu kamu bayar saat ini.</p>
                        </div>
                    </div>
                @endif
            </section>
        @endif

        {{-- ============================================================
             AKSI CEPAT
             ============================================================ --}}
        <section aria-label="Aksi cepat" class="space-y-4">
            <h2 class="flex items-center gap-2 text-base font-black tracking-tight text-slate-900 dark:text-white">
                <span class="grid h-8 w-8 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-500/10 dark:text-primary-300">
                    <i class="ri-thunderstorms-line"></i>
                </span>
                Aksi Cepat
            </h2>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <a href="{{ route('tenant.tagihan.index') }}" class="group flex items-center gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition hover:border-amber-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-amber-500/30">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-50 text-lg text-amber-600 dark:bg-amber-500/10 dark:text-amber-400"><i class="ri-file-list-3-line"></i></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-bold text-slate-900 dark:text-white">Tagihan</span>
                        <span class="block truncate text-[11px] text-slate-400 dark:text-slate-500">Cek & bayar tagihan</span>
                    </span>
                </a>
                <a href="{{ route('tenant.pembayaran.index') }}" class="group flex items-center gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition hover:border-green-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-green-500/30">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-green-50 text-lg text-green-600 dark:bg-green-500/10 dark:text-green-400"><i class="ri-money-dollar-circle-line"></i></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-bold text-slate-900 dark:text-white">Pembayaran</span>
                        <span class="block truncate text-[11px] text-slate-400 dark:text-slate-500">Riwayat pembayaran</span>
                    </span>
                </a>
                <a href="{{ route('tenant.booking.index') }}" class="group flex items-center gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-500/30">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-lg text-blue-600 dark:bg-blue-500/10 dark:text-blue-400"><i class="ri-calendar-check-line"></i></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-bold text-slate-900 dark:text-white">Booking Saya</span>
                        <span class="block truncate text-[11px] text-slate-400 dark:text-slate-500">Riwayat booking</span>
                    </span>
                </a>
                <a href="{{ route('tenant.kontrak.index') }}" class="group flex items-center gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition hover:border-indigo-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-500/30">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-indigo-50 text-lg text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400"><i class="ri-file-text-line"></i></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-bold text-slate-900 dark:text-white">Kontrak</span>
                        <span class="block truncate text-[11px] text-slate-400 dark:text-slate-500">Lihat kontrak sewa</span>
                    </span>
                </a>
            </div>

            @if($penghuni)
                @if($pendingCheckout)
                    <a href="{{ route('tenant.kontrak.index') }}"
                       class="inline-flex items-center gap-2.5 rounded-2xl border border-amber-200 bg-amber-50/70 px-4 py-3 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20">
                        <i class="ri-time-line text-lg"></i> Check-Out Diproses
                        <span class="text-xs font-medium text-amber-600/80 dark:text-amber-400/80">Menunggu persetujuan</span>
                    </a>
                @else
                    <div class="inline-flex">
                        <x-confirm-dialog title="Ajukan Check-Out?" description="Ajukan check-out dari kamar {{ $stats['kamar_number'] ?? '' }}? Tindakan ini akan mengirim permintaan ke pemilik kos."
                                           confirmText="Ya, Ajukan" confirmClass="bg-red-600 hover:bg-red-700 text-white"
                                           triggerClass="contents" aria-label="Ajukan check-out">
                            <x-slot name="slot">
                                <div class="flex w-full items-center gap-3 rounded-2xl border border-red-100 bg-white p-4 text-left shadow-sm transition hover:border-red-200 hover:shadow-md dark:border-red-500/20 dark:bg-slate-900 group">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-red-50 text-lg text-red-600 transition dark:bg-red-500/10 dark:text-red-400 group-hover:bg-red-100 dark:group-hover:bg-red-500/20"><i class="ri-logout-box-r-line"></i></span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold text-slate-900 dark:text-white">Ajukan Check-Out</span>
                                        <span class="block truncate text-[11px] text-slate-400 dark:text-slate-500">Keluar dari kamar saat ini</span>
                                    </span>
                                </div>
                            </x-slot>
                            <x-slot name="actions">
                                <form method="POST" action="{{ route('tenant.checkout.request', $penghuni) }}" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                    @csrf
                                    <button type="submit" :disabled="submitting"
                                            class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition shadow-sm shadow-red-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span x-show="!submitting">Ya, Ajukan</span>
                                        <span x-show="submitting" x-cloak>Mengirim...</span>
                                    </button>
                                </form>
                            </x-slot>
                        </x-confirm-dialog>
                    </div>
                @endif
            @endif
        </section>

        <hr class="border-slate-200/70 dark:border-slate-800">

        {{-- ============================================================
             CARI KOS (marketplace compact)
             ============================================================ --}}
        <section aria-label="Cari kos" class="rounded-[1.25rem] border border-slate-100 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="flex items-center gap-2 text-base font-black tracking-tight text-slate-900 dark:text-white">
                <span class="grid h-8 w-8 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-500/10 dark:text-primary-300">
                    <i class="ri-search-eye-line"></i>
                </span>
                Cari Kos
            </h2>

            <form method="GET" action="{{ route('tenant.kos.index') }}" class="mt-4" role="search">
                <div class="flex flex-col gap-2.5 sm:flex-row">
                    <div class="relative flex-1">
                        <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <label for="dashboard-search-kos" class="sr-only">Cari kos, lokasi, atau fasilitas</label>
                        <input type="text" name="q" id="dashboard-search-kos" value="{{ request('q') }}"
                               placeholder="Cari nama kos, lokasi, atau fasilitas..."
                               autocomplete="off"
                               aria-label="Cari kos"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 transition focus:border-primary-500/40 focus:bg-white focus:ring-2 focus:ring-primary-500/40 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:placeholder-slate-500">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-primary-500/30 transition hover:bg-primary-600 active:scale-95">
                        <i class="ri-search-line"></i> Cari Kos
                    </button>
                </div>
            </form>

            @php
                $filters = [
                    ['label' => 'Semua Kos', 'icon' => 'ri-building-2-line', 'url' => route('tenant.kos.index')],
                    ['label' => 'Tersedia', 'icon' => 'ri-door-open-line', 'url' => route('tenant.kos.index', ['tersedia_only' => 1])],
                    ['label' => 'Harga Terjangkau', 'icon' => 'ri-money-dollar-circle-line', 'url' => route('tenant.kos.index', ['sort' => 'harga_terendah'])],
                    ['label' => 'Favorit', 'icon' => 'ri-heart-line', 'url' => route('tenant.favorites.index'), 'badge' => (int) ($favoriteCount ?? 0)],
                ];
            @endphp
            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                <span class="font-medium text-slate-400 dark:text-slate-500">Atau cari cepat:</span>
                @foreach($filters as $f)
                    <a href="{{ $f['url'] }}"
                       class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 font-semibold text-slate-600 transition hover:border-primary-200 hover:bg-primary-50 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300 dark:hover:border-primary-500/30 dark:hover:bg-primary-500/10">
                        <i class="{{ $f['icon'] }} text-primary-500"></i> {{ $f['label'] }}
                        @if(!empty($f['badge']) && $f['badge'] > 0)
                            <span class="font-bold text-primary-500">({{ $f['badge'] }})</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Promosi kos OWNER ber-placement homepage (is_homepage, kos_id TIDAK NULL) --}}
        @if(($kosPromosHome ?? collect())->isNotEmpty())
            <section aria-label="Promosi kos di beranda" class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-500/10 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-amber-700 dark:border-amber-500/30 dark:text-amber-400">
                        <i class="ri-star-fill text-[11px]"></i> Promosi Kos
                    </span>
                    <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">Iklan promo kos — rekomendasi di bawah tetap organik</span>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach($kosPromosHome as $promo)
                        @include('tenant.partials.promoted-kos', ['campaign' => $promo, 'placement' => 'homepage'])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ============================================================
             BELUM MEMILIKI KAMAR (callout untuk tenant baru)
             ============================================================ --}}
        @if(!$penghuni)
            <section aria-label="Belum memiliki kamar" class="flex flex-col items-start gap-4 rounded-[1.25rem] border border-primary-100 bg-primary-50/60 px-5 py-4 sm:flex-row sm:items-center dark:border-primary-500/20 dark:bg-primary-500/[0.06]">
                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-primary-100 text-xl text-primary-600 dark:bg-primary-500/10 dark:text-primary-300">
                    <i class="ri-door-open-line"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-slate-900 dark:text-white">Belum Memiliki Kamar</p>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Belum punya kamar? Temukan kos yang sesuai kebutuhanmu.</p>
                </div>
                <a href="{{ route('tenant.kos.index') }}"
                   class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary-500/30 transition hover:bg-primary-600 active:scale-95">
                    <i class="ri-search-eye-line"></i> Cari Kos Sekarang
                </a>
            </section>
        @endif

        {{-- ============================================================
             JELAJAHI BERDASARKAN LOKASI / ALAMAT
             ============================================================ --}}
        @php $locations = $locations ?? collect(); @endphp
        @if($locations->isNotEmpty())
            <section aria-label="Jelajahi berdasarkan lokasi">
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Jelajahi berdasarkan lokasi</h2>
                        <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">Temukan kos di alamat-alamat yang tersedia.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($locations->take(9) as $loc)
                        <a href="{{ route('tenant.kos.index', ['loc' => $loc->address]) }}"
                           class="group flex items-center gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition hover:border-primary-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-500/30">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-primary-50 text-lg text-primary-500 dark:bg-primary-500/10">
                                <i class="ri-map-pin-2-fill"></i>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold leading-snug text-slate-900 line-clamp-2 dark:text-white" title="{{ $loc->address }}">{{ $loc->address }}</span>
                                <span class="mt-0.5 block text-xs text-slate-400 dark:text-slate-500">{{ $loc->kos_count }} kos</span>
                            </span>
                            <i class="shrink-0 text-slate-300 transition group-hover:text-primary-500 ri-arrow-right-s-line"></i>
                        </a>
                    @endforeach
                </div>
            </section>
        @else
            <section aria-label="Jelajahi berdasarkan lokasi">
                <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center dark:border-slate-700">
                    <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                        <i class="text-xl text-slate-400 dark:text-slate-500 ri-map-pin-line"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada lokasi kos untuk dijelajahi.</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Segera hadir kos di berbagai alamat.</p>
                </div>
            </section>
        @endif

        {{-- ============================================================
             REKOMENDASI UNTUKMU
             ============================================================ --}}
        @if($recommendations->isNotEmpty())
            <section aria-label="Rekomendasi untukmu">
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Rekomendasi untukmu</h2>
                        <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">Pilihan kos yang mungkin cocok untuk kebutuhanmu.</p>
                    </div>
                    <a href="{{ route('tenant.kos.index') }}" class="flex shrink-0 items-center gap-0.5 text-xs font-semibold text-primary-500 transition hover:text-primary-600">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($recommendations as $rk)
                        @include('tenant.partials.kos-card', ['kos' => $rk, 'favoritedIds' => $favoritedIds])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ============================================================
             DISCOVERY BERDASARKAN BUDGET
             ============================================================ --}}
        @foreach($budgetDiscovery ?? [] as $bSection)
            <section aria-label="{{ $bSection['title'] }}">
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">{{ $bSection['title'] }}</h2>
                        <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">{{ $bSection['subtitle'] }}</p>
                    </div>
                    <a href="{{ route('tenant.kos.index', ['sort' => 'harga_terendah']) }}"
                       class="flex shrink-0 items-center gap-0.5 text-xs font-semibold text-primary-500 transition hover:text-primary-600">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($bSection['kos'] as $k)
                        @include('tenant.partials.kos-card', ['kos' => $k, 'favoritedIds' => $favoritedIds])
                    @endforeach
                </div>
            </section>
        @endforeach

        {{-- ============================================================
             DISCOVERY BERDASARKAN FASILITAS NYATA
             ============================================================ --}}
        @foreach($facilityDiscovery ?? [] as $fSection)
            <section aria-label="{{ $fSection['title'] }}">
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">{{ $fSection['title'] }}</h2>
                        <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">{{ $fSection['subtitle'] }}</p>
                    </div>
                    <a href="{{ route('tenant.kos.index', ['facilities' => [$fSection['id']]]) }}"
                       class="flex shrink-0 items-center gap-0.5 text-xs font-semibold text-primary-500 transition hover:text-primary-600">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($fSection['kos'] as $k)
                        @include('tenant.partials.kos-card', ['kos' => $k, 'favoritedIds' => $favoritedIds])
                    @endforeach
                </div>
            </section>
        @endforeach

        {{-- ============================================================
             KOS TERBARU
             ============================================================ --}}
        @if($latestKos->isNotEmpty())
            <section aria-label="Kos terbaru">
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Kos Terbaru</h2>
                        <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">Temukan pilihan kos yang baru ditambahkan.</p>
                    </div>
                    <a href="{{ route('tenant.kos.index') }}" class="flex shrink-0 items-center gap-0.5 text-xs font-semibold text-primary-500 transition hover:text-primary-600">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($latestKos as $lk)
                        @include('tenant.partials.kos-card', ['kos' => $lk, 'favoritedIds' => $favoritedIds])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ============================================================
             IKLAN DASHBOARD TENANT (placement tenant_dashboard) — partner
             B2B (mis. DANA, Shopee, GoPay). JELAS terpisah dari kos organik.
             ============================================================ --}}
        @if(($tenantDashboardAds ?? collect())->isNotEmpty())
            <section aria-label="Penawaran partner untuk penghuni kos" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($tenantDashboardAds as $ad)
                    @include('tenant.partials.partner-ad', [
                        'ad' => $ad,
                        'placement' => 'tenant_dashboard',
                        'variant' => 'card',
                    ])
                @endforeach
            </section>
        @endif

        {{-- ============================================================
             FAVORIT
             ============================================================ --}}
        <section aria-label="Kos yang kamu simpan">
            <div class="mb-4 flex items-end justify-between">
                <div>
                    <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Kos yang Kamu Simpan ❤️</h2>
                    <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">Kembali lagi ke kos favoritmu kapan saja.</p>
                </div>
                @if($favoriteKos->isNotEmpty())
                    <a href="{{ route('tenant.favorites.index') }}" class="flex shrink-0 items-center gap-0.5 text-xs font-semibold text-primary-500 transition hover:text-primary-600">
                        Lihat Semua Favorit <i class="ri-arrow-right-s-line"></i>
                    </a>
                @endif
            </div>

            @if($favoriteKos->isEmpty())
                <div class="flex flex-col items-center rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="grid h-11 w-11 place-items-center rounded-2xl bg-red-50 text-xl text-red-400 dark:bg-red-500/10">
                        <i class="ri-heart-line"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada kos yang disimpan.</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Temukan kos yang kamu suka dan simpan di sini.</p>
                    <a href="{{ route('tenant.kos.index') }}"
                       class="mt-4 inline-flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary-500/30 transition hover:bg-primary-600 active:scale-95">
                        <i class="ri-search-eye-line"></i> Cari Kos
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($favoriteKos as $fk)
                        @include('tenant.partials.kos-card', ['kos' => $fk, 'favoritedIds' => $favoritedIds])
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ============================================================
             BARU KAMU LIHAT (localStorage)
             ============================================================ --}}
        <section aria-label="Baru kamu lihat" x-data="recentlyViewed()"
                 x-show="!loading && recent.length > 0" x-cloak>
            <div class="mb-4 flex items-end justify-between">
                <div>
                    <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Baru Kamu Lihat</h2>
                    <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">Lanjutkan dari kos yang baru kamu kunjungi.</p>
                </div>
            </div>

            <div class="-mx-1 flex gap-4 overflow-x-auto px-1 pb-2 snap-x snap-mandatory">
                <template x-for="item in recent" :key="item.id">
                    <a :href="item.url"
                       class="group relative block w-56 shrink-0 snap-start overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:border-primary-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-500/30">
                        <span class="block truncate px-4 pt-4 text-sm font-bold text-slate-900 dark:text-white" x-text="item.name"></span>
                        <span class="mt-1 block truncate px-4 text-xs text-slate-400 dark:text-slate-500" x-text="item.address"></span>
                        <span class="block px-4 pb-4 pt-2.5 text-sm font-black text-primary-600 dark:text-primary-300" x-text="item.price"></span>
                    </a>
                </template>
            </div>

            <div class="mt-3">
                <button type="button" @click="clearRecent()"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 transition hover:text-red-500 dark:text-slate-500">
                    <i class="ri-delete-bin-line"></i> Hapus riwayat
                </button>
            </div>
        </section>

        {{-- ============================================================
             TRUST
             ============================================================ --}}
        <section class="rounded-[1.25rem] border border-slate-100 bg-white p-6 dark:border-slate-800 dark:bg-slate-900" aria-label="Kenapa mencari kos di KosManager">
            <h2 class="text-center text-lg font-black tracking-tight text-slate-900 dark:text-white">Kenapa mencari kos di KosManager?</h2>
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400"><i class="ri-flashlight-line"></i></span>
                    <p class="text-[11px] font-semibold leading-tight text-slate-700 dark:text-slate-200">Booking langsung terkonfirmasi</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400"><i class="ri-price-tag-3-line"></i></span>
                    <p class="text-[11px] font-semibold leading-tight text-slate-700 dark:text-slate-200">Informasi kamar jelas</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400"><i class="ri-wallet-3-line"></i></span>
                    <p class="text-[11px] font-semibold leading-tight text-slate-700 dark:text-slate-200">Pembayaran tercatat</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400"><i class="ri-map-pin-2-line"></i></span>
                    <p class="text-[11px] font-semibold leading-tight text-slate-700 dark:text-slate-200">Lokasi kos tersedia</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400"><i class="ri-shield-check-line"></i></span>
                    <p class="text-[11px] font-semibold leading-tight text-slate-700 dark:text-slate-200">Data booking aman</p>
                </div>
            </div>
        </section>

        {{-- ============================================================
             BANTUAN (accordion) — id dipakai target menu/aksi "Lapor Kerusakan"
             ============================================================ --}}
        <section id="bantuan" aria-label="Bantuan" x-data="{ open: null }" class="scroll-mt-24">
            <div class="mb-4 flex items-end justify-between">
                <div>
                    <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Masih bingung?</h2>
                    <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">Temukan jawaban seputar booking, pembayaran, dan kos.</p>
                </div>
            </div>
            <div class="mx-auto max-w-3xl space-y-3">
                @php
                    $faqs = [
                        ['q' => 'Bagaimana cara booking?', 'a' => 'Cari kos yang kamu suka, buka halaman detailnya, pilih kamar yang tersedia, lalu klik "Booking Sekarang". Lengkapi periode sewa dan konfirmasi. Booking langsung terkonfirmasi.'],
                        ['q' => 'Bagaimana cara membayar?', 'a' => 'Setelah booking, kamu akan memiliki tagihan. Buka menu Tagihan, lalu kirim konfirmasi pembayaran tunai langsung kepada pengelola kos. Pengelola akan memverifikasi pembayaranmu. Kamu bisa melampirkan bukti pembayaran (opsional) jika diperlukan.'],
                        ['q' => 'Bagaimana cara check-in?', 'a' => 'Setelah kamu datang dan bertemu pengelola, pengelola akan memproses check-in kamarmu. Setelah check-in, kamar akan tampil di bagian "Kos Saya" pada beranda.'],
                        ['q' => 'Bagaimana jika ingin membatalkan booking?', 'a' => 'Buka menu Booking Saya, pilih booking yang ingin dibatalkan, lalu klik "Batalkan Booking". Kamar akan tersedia kembali untuk disewa orang lain.'],
                        ['q' => 'Ada kerusakan di kamar, bagaimana cara melaporkannya?', 'a' => 'Hubungi pengelola kos melalui halaman Kontrak Sewa, atau kirim informasi kerusakan melalui notifikasi/halaman Bantuan. Pengelola akan memproses perbaikan sesegera mungkin.' ],
                    ];
                @endphp
                @foreach($faqs as $i => $faq)
                    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <button type="button"
                                class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40"
                                @click="open = open === {{ $i }} ? null : {{ $i }}"
                                :aria-expanded="(open === {{ $i }}).toString()">
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $faq['q'] }}</span>
                            <i class="ri-arrow-down-s-line text-slate-400 transition-transform"
                               :class="open === {{ $i }} ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open === {{ $i }}" x-cloak x-transition
                             class="px-5 pb-4 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ============================================================
             FINAL CTA
             ============================================================ --}}
        <section class="relative overflow-hidden rounded-[1.25rem] bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 p-8 text-center text-white">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -right-1/4 -top-1/4 h-56 w-56 rounded-full bg-white"></div>
                <div class="absolute -bottom-1/4 -left-1/4 h-48 w-48 rounded-full bg-white"></div>
            </div>
            <div class="relative mx-auto max-w-xl">
                <h2 class="text-xl font-black">Sudah menemukan kos yang cocok?</h2>
                <p class="mt-2 text-sm text-primary-100/80">Pesan kamar pilihanmu sekarang.</p>
                <a href="{{ route('tenant.kos.index') }}"
                   class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-white px-7 py-3.5 text-sm font-bold text-primary-700 shadow-xl shadow-black/10 transition hover:bg-primary-50 active:scale-95">
                    <i class="ri-search-eye-line"></i> Cari Kos
                </a>
            </div>
        </section>
    </div>

    {{-- Recently Viewed — localStorage widget --}}
    <script>
        (function () {
            var STORAGE_KEY = 'kosmanager:recently_viewed';
            var MAX_ITEMS = 6;

            window.recentlyViewed = function () {
                return {
                    recent: [],
                    loading: true,
                    init() {
                        try {
                            var raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
                            this.recent = Array.isArray(raw) ? raw.filter(function (i) { return i && i.url; }).slice(0, MAX_ITEMS) : [];
                        } catch (e) {
                            this.recent = [];
                        }
                        this.loading = false;
                    },
                    clearRecent() {
                        try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
                        this.recent = [];
                    }
                };
            };

            // Record the viewed kos on the detail page
            var record = document.getElementById('record-view');
            if (record) {
                try {
                    var item = JSON.parse(record.textContent);
                    var list = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
                    list = Array.isArray(list) ? list.filter(function (i) { return i.id !== item.id; }) : [];
                    list.unshift(item);
                    list = list.slice(0, MAX_ITEMS);
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
                } catch (e) {}
            }
        })();
    </script>
</x-app-layout>