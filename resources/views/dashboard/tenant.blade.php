<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-10">
        <x-alert />

        {{-- ============================================================
             WELCOME HEADER (compact — bukan hero marketplace)
             ============================================================ --}}
        <header class="flex items-center justify-between gap-4" aria-label="Selamat datang">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                    Halo, <span class="text-primary-600 dark:text-primary-400">{{ $user->name }}</span> 👋
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola tempat tinggal dan aktivitas kos kamu dengan mudah.</p>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 dark:text-primary-300 bg-primary-50 dark:bg-primary-500/10 px-3 py-1.5 rounded-full">
                    <i class="ri-shield-check-line"></i> Akun aman
                </span>
            </div>
        </header>

        {{-- ============================================================
             ADVERTISING — Promoted Partner (advertiser pihak ketiga)
             Langsung di bawah greeting; iklan jelas terpisah dari kos
             organik, transparan & berlabel.
             ============================================================ --}}
        @if($partnerAds->isNotEmpty())
            <section aria-label="Partner untuk penghuni kos" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($partnerAds as $ad)
                    @include('tenant.partials.promoted-partner', ['ad' => $ad, 'placement' => 'homepage', 'variant' => 'banner'])
                @endforeach
            </section>
        @endif

        {{-- ============================================================
             KOS SAYA (Residence) — hanya untuk tenant yang sudah check-in
             ============================================================ --}}
        @if($penghuni)
            <section aria-label="Kos saya" class="space-y-6">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                            <i class="ri-home-heart-line text-lg text-emerald-500"></i>
                        </span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Kos Saya</h2>
                            <p class="text-xs text-slate-400 dark:text-slate-500">{{ $stats['kos_name'] ?? '' }} · Kamar {{ $stats['kamar_number'] ?? '-' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('tenant.kontrak.index') }}"
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-500 hover:text-primary-600 transition shrink-0">
                        Lihat Kontrak <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>

                {{-- Primary residence card --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                        {{-- Kos / Kamar --}}
                        <div class="flex items-center gap-4 min-w-0 flex-1">
                            <div class="relative shrink-0 w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden bg-gradient-to-br from-primary-50 via-primary-100 to-emerald-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 flex items-center justify-center">
                                @if(!empty($penghuni->kos->photo))
                                    <img src="{{ asset('storage/' . $penghuni->kos->photo) }}" alt="Foto {{ $stats['kos_name'] ?? 'Kos' }}" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                                @else
                                    <i class="ri-building-2-line text-2xl text-primary-400 dark:text-primary-300/60"></i>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-400 dark:text-slate-500">Residence</p>
                                <p class="text-lg font-black text-slate-900 dark:text-white truncate">{{ $stats['kos_name'] ?? '-' }}</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-500 dark:text-slate-400 inline-flex items-center gap-1.5">
                                    <i class="ri-door-open-line text-primary-500"></i> Kamar {{ $stats['kamar_number'] ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="hidden sm:block w-px h-14 bg-slate-100 dark:bg-slate-800 shrink-0"></div>

                        {{-- Kontrak --}}
                        <div class="min-w-0 shrink-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kontrak</p>
                            @php
                                $kStatus = $stats['kontrak_status'] ?? '-';
                                $kColor = match($kStatus) {
                                    'active' => 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300',
                                    'expired' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                                    'completed' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300',
                                    default => 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold mt-1.5 {{ $kColor }}">
                                {{ \StatusLabels::kontrakLabel($kStatus) }}
                            </span>
                            @if(!empty($stats['kontrak_end']))
                                <p class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500">sd {{ $stats['kontrak_end']->translatedFormat('d M Y') }}</p>
                            @endif
                        </div>

                        <div class="hidden sm:block w-px h-14 bg-slate-100 dark:bg-slate-800 shrink-0"></div>

                        {{-- Jatuh tempo --}}
                        <div class="min-w-0 shrink-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jatuh Tempo</p>
                            <p class="text-base font-bold mt-1.5 {{ ($stats['tagihan_pending'] ?? 0) > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }}">
                                @if(!empty($stats['nearest_due']))
                                    {{ $stats['nearest_due']->translatedFormat('d M Y') }}
                                @else
                                    Tidak ada
                                @endif
                            </p>
                            @if(($stats['tagihan_pending'] ?? 0) > 0)
                                <a href="{{ route('tenant.tagihan.index') }}" class="mt-1 inline-flex items-center gap-0.5 text-[11px] font-semibold text-amber-600 hover:text-amber-700 transition">
                                    {{ $stats['tagihan_pending'] }} tagihan · Bayar <i class="ri-arrow-right-s-line"></i>
                                </a>
                            @endif
                        </div>

                        <div class="hidden sm:block w-px h-14 bg-slate-100 dark:bg-slate-800 shrink-0"></div>

                        {{-- Pembayaran terakhir --}}
                        <div class="min-w-0 shrink-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Pembayaran Terakhir</p>
                            @if(!empty($stats['last_payment_amount']))
                                <p class="text-base font-bold text-slate-900 dark:text-white mt-1.5">Rp {{ number_format((float) $stats['last_payment_amount'], 0, ',', '.') }}</p>
                                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">{{ $stats['last_payment_date']->translatedFormat('d M Y') }}</p>
                            @else
                                <p class="text-base font-bold text-slate-400 dark:text-slate-500 mt-1.5">Belum ada</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Compact payment summary strip (data dari $stats) --}}
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center shrink-0">
                            <i class="ri-file-list-3-line text-lg text-amber-600 dark:text-amber-400"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Tagihan Belum Bayar</p>
                            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stats['tagihan_pending'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-yellow-50 dark:bg-yellow-500/10 flex items-center justify-center shrink-0">
                            <i class="ri-time-line text-lg text-yellow-600 dark:text-yellow-400"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Menunggu Verifikasi</p>
                            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stats['tagihan_pending_verification'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 flex items-center gap-3 col-span-2 lg:col-span-1">
                        <span class="w-9 h-9 rounded-xl bg-green-50 dark:bg-green-500/10 flex items-center justify-center shrink-0">
                            <i class="ri-money-dollar-circle-line text-lg text-green-600 dark:text-green-400"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Belum Dibayar</p>
                            <p class="text-lg font-bold text-slate-900 dark:text-white">
                                @if(($stats['total_belum_dibayar'] ?? 0) > 0)
                                    Rp {{ number_format((float) $stats['total_belum_dibayar'], 0, ',', '.') }}
                                @else
                                    Rp 0
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <hr class="border-slate-200/70 dark:border-slate-800">
        @endif

        {{-- ============================================================
             ATTENTION / STATUS PENTING (data existing, no dummy data)
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
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                        <i class="ri-notification-3-line text-lg text-primary-600 dark:text-primary-400"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Perlu Perhatian</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Hal yang perlu kamu tindak lanjuti hari ini.</p>
                    </div>
                </div>

                @if(!empty($attention))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($attention as $item)
                            <a href="{{ $item['route'] }}"
                               class="group flex items-start gap-3 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 hover:shadow-md hover:border-primary-200 dark:hover:border-primary-500/30 transition-all duration-200">
                                <span class="shrink-0 w-10 h-10 rounded-xl {{ $item['iconClass'] }} flex items-center justify-center">
                                    <i class="{{ $item['icon'] }} text-lg"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-bold text-slate-900 dark:text-white leading-snug">{{ $item['title'] }}</span>
                                    <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ $item['desc'] }}</span>
                                </span>
                                <span class="shrink-0 inline-flex items-center gap-0.5 text-xs font-semibold text-primary-500 group-hover:text-primary-600 transition">
                                    {{ $item['cta'] }} <i class="ri-arrow-right-s-line"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center gap-3 rounded-2xl border border-green-100 dark:border-green-500/20 bg-green-50/60 dark:bg-green-500/[0.06] p-4">
                        <span class="shrink-0 w-10 h-10 rounded-xl bg-green-100 dark:bg-green-500/10 flex items-center justify-center">
                            <i class="ri-shield-check-line text-lg text-green-600 dark:text-green-400"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-green-700 dark:text-green-300">Semua pembayaran aman</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tidak ada tagihan yang perlu kamu bayar saat ini.</p>
                        </div>
                    </div>
                @endif
            </section>

            <hr class="border-slate-200/70 dark:border-slate-800">
        @endif

        {{-- ============================================================
             QUICK ACTIONS (hierarchy: Tagihan & Bayar primary,
             Booking/Kontrak secondary, Check-Out destructive/special)
             Rendered untuk semua tenant; sinyal check-out hanya bila ada penghuni.
             ============================================================ --}}
        <section aria-label="Aksi cepat" class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                    <i class="ri-thunderstorms-line text-lg text-primary-600 dark:text-primary-400"></i>
                </span>
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Aksi Cepat</h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $penghuni ? 'Aktivitas utama tempat tinggalmu.' : 'Kelola aktivitas sewa dan pembayaranmu.' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    {{-- Primary: Tagihan --}}
                    <a href="{{ route('tenant.tagihan.index') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 hover:shadow-md hover:-translate-y-0.5 hover:border-amber-200 dark:hover:border-amber-500/30 transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center shrink-0 group-hover:bg-amber-100 dark:group-hover:bg-amber-500/20 transition"><i class="ri-file-list-3-line text-lg text-amber-600 dark:text-amber-400"></i></div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">Tagihan</p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate">Cek & bayar tagihan</p>
                        </div>
                    </a>

                    {{-- Primary: Pembayaran --}}
                    <a href="{{ route('tenant.pembayaran.index') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 hover:shadow-md hover:-translate-y-0.5 hover:border-green-200 dark:hover:border-green-500/30 transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-green-50 dark:bg-green-500/10 flex items-center justify-center shrink-0 group-hover:bg-green-100 dark:group-hover:bg-green-500/20 transition"><i class="ri-money-dollar-circle-line text-lg text-green-600 dark:text-green-400"></i></div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">Pembayaran</p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate">Riwayat pembayaran</p>
                        </div>
                    </a>

                    {{-- Secondary: Booking Saya --}}
                    <a href="{{ route('tenant.booking.index') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 hover:shadow-md hover:-translate-y-0.5 hover:border-blue-200 dark:hover:border-blue-500/30 transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0 group-hover:bg-blue-100 dark:group-hover:bg-blue-500/20 transition"><i class="ri-calendar-check-line text-lg text-blue-600 dark:text-blue-400"></i></div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">Booking Saya</p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate">Riwayat booking</p>
                        </div>
                    </a>

                    {{-- Secondary: Kontrak --}}
                    <a href="{{ route('tenant.kontrak.index') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 hover:shadow-md hover:-translate-y-0.5 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center shrink-0 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-500/20 transition"><i class="ri-file-text-line text-lg text-indigo-600 dark:text-indigo-400"></i></div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">Kontrak</p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate">Lihat kontrak sewa</p>
                        </div>
                    </a>
                </div>

                {{-- Check-Out: destructive/special action, emphasis only when needed.
                     Hanya untuk tenant yang punya penghuni aktif. --}}
                @if($penghuni)
                    @if($pendingCheckout)
                        <a href="{{ route('tenant.kontrak.index') }}"
                           class="inline-flex items-center gap-2.5 rounded-2xl border border-amber-200 dark:border-amber-500/30 bg-amber-50/70 dark:bg-amber-500/10 px-4 py-3 text-sm font-semibold text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-500/20 transition">
                            <i class="ri-time-line text-lg"></i> Check-Out Diproses
                            <span class="text-xs font-medium text-amber-600/80 dark:text-amber-400/80">Menunggu persetujuan</span>
                        </a>
                    @else
                        <div class="inline-flex">
                            <x-confirm-dialog title="Ajukan Check-Out?" description="Ajukan check-out dari kamar {{ $stats['kamar_number'] ?? '' }}? Tindakan ini akan mengirim permintaan ke pemilik kos."
                                               confirmText="Ya, Ajukan" confirmClass="bg-red-600 hover:bg-red-700 text-white"
                                               triggerClass="contents" aria-label="Ajukan check-out">
                                <x-slot name="slot">
                                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-red-100 dark:border-red-500/20 p-4 hover:shadow-md hover:border-red-200 transition flex items-center gap-3 text-left w-full group">
                                        <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-500/10 flex items-center justify-center shrink-0 group-hover:bg-red-100 dark:group-hover:bg-red-500/20 transition"><i class="ri-logout-box-r-line text-lg text-red-600 dark:text-red-400"></i></div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">Ajukan Check-Out</p>
                                            <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate">Keluar dari kamar saat ini</p>
                                        </div>
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
             CARI KOS (marketplace — compact, secondary, bukan hero)
             ============================================================ --}}
        <section aria-label="Cari kos" class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                    <i class="ri-search-eye-line text-lg text-primary-600 dark:text-primary-400"></i>
                </span>
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Cari Kos</h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Temukan tempat tinggal yang sesuai kebutuhanmu.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('tenant.kos.index') }}" class="mt-5" role="search">
                <div class="flex flex-col sm:flex-row gap-2.5">
                    <div class="relative flex-1">
                        <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <label for="dashboard-search-kos" class="sr-only">Cari kos, lokasi, atau fasilitas</label>
                        <input type="text" name="q" id="dashboard-search-kos" value="{{ request('q') }}"
                               placeholder="Cari nama kos, lokasi, atau fasilitas..."
                               autocomplete="off"
                               aria-label="Cari kos"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-white text-sm placeholder-slate-400 dark:placeholder-slate-500 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500/40 transition">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold rounded-xl transition shadow-sm shadow-primary-500/30 active:scale-95">
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
                <span class="text-slate-400 dark:text-slate-500 font-medium">Atau cari cepat:</span>
                @foreach($filters as $f)
                    <a href="{{ $f['url'] }}"
                       class="inline-flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/60 hover:bg-primary-50 dark:hover:bg-primary-500/10 px-3 py-1.5 rounded-full transition border border-slate-200 dark:border-slate-700 hover:border-primary-200 dark:hover:border-primary-500/30">
                        <i class="{{ $f['icon'] }} text-primary-500"></i> {{ $f['label'] }}
                        @if(!empty($f['badge']) && $f['badge'] > 0)
                            <span class="font-bold text-primary-500">({{ $f['badge'] }})</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Promosi kos OWNER ber-placement homepage (is_homepage, kos_id TIDAK
             NULL). Jelas berlabel "Promosi Beranda" — pemilik kos yang membayar
             mendapat ruang tayang, tanpa memengaruhi rekomendasi organik. --}}
        @if(($kosPromosHome ?? collect())->isNotEmpty())
            <section aria-label="Promosi kos di beranda" class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-[11px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">
                        <i class="ri-star-fill text-[11px]"></i> Promosi Kos
                    </span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Iklan promo kos — rekomendasi di bawah tetap organik</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($kosPromosHome as $promo)
                        @include('tenant.partials.promoted-kos', ['campaign' => $promo, 'placement' => 'homepage'])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ============================================================
             BELUM MEMILIKI KAMAR (callout)
             ============================================================ --}}
        @if(!$penghuni)
            <section aria-label="Belum memiliki kamar" class="flex flex-col sm:flex-row sm:items-center gap-4 rounded-2xl border border-primary-100 dark:border-primary-500/20 bg-primary-50/60 dark:bg-primary-500/[0.06] px-5 py-4">
                <div class="w-11 h-11 rounded-xl bg-primary-100 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                    <i class="ri-door-open-line text-xl text-primary-600 dark:text-primary-300"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-slate-900 dark:text-white">Belum Memiliki Kamar</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Belum punya kamar? Temukan kos yang sesuai kebutuhanmu.</p>
                </div>
                <a href="{{ route('tenant.kos.index') }}"
                   class="inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30 shrink-0">
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
                <div class="flex items-end justify-between mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Jelajahi berdasarkan lokasi</h2>
                        <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">Temukan kos di alamat-alamat yang tersedia.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($locations->take(9) as $loc)
                        <a href="{{ route('tenant.kos.index', ['loc' => $loc->address]) }}"
                           class="group flex items-center gap-3 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 hover:shadow-lg hover:-translate-y-0.5 hover:border-primary-200 dark:hover:border-primary-500/30 transition-all duration-200">
                            <span class="shrink-0 w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center text-primary-500 dark:text-primary-300">
                                <i class="ri-map-pin-2-fill text-lg"></i>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-slate-900 dark:text-white leading-snug line-clamp-2" title="{{ $loc->address }}">{{ $loc->address }}</span>
                                <span class="block text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $loc->kos_count }} kos</span>
                            </span>
                            <i class="ri-arrow-right-s-line text-slate-300 group-hover:text-primary-500 transition shrink-0"></i>
                        </a>
                    @endforeach
                </div>
            </section>
        @else
            <section aria-label="Jelajahi berdasarkan lokasi">
                <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-8 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto">
                        <i class="ri-map-pin-line text-xl text-slate-400 dark:text-slate-500"></i>
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
                <div class="flex items-end justify-between mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Rekomendasi untukmu</h2>
                        <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">Pilihan kos yang mungkin cocok untuk kebutuhanmu.</p>
                    </div>
                    <a href="{{ route('tenant.kos.index') }}" class="text-xs font-semibold text-primary-500 hover:text-primary-600 transition flex items-center gap-0.5 shrink-0">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($recommendations as $rk)
                        @include('tenant.partials.kos-card', ['kos' => $rk, 'favoritedIds' => $favoritedIds])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ============================================================
             DISCOVERY BERDASARKAN BUDGET (dari harga nyata)
             ============================================================ --}}
        @foreach($budgetDiscovery ?? [] as $bSection)
            <section aria-label="{{ $bSection['title'] }}">
                <div class="flex items-end justify-between mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $bSection['title'] }}</h2>
                        <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">{{ $bSection['subtitle'] }}</p>
                    </div>
                    <a href="{{ route('tenant.kos.index', ['sort' => 'harga_terendah']) }}"
                       class="text-xs font-semibold text-primary-500 hover:text-primary-600 transition flex items-center gap-0.5 shrink-0">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
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
                <div class="flex items-end justify-between mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $fSection['title'] }}</h2>
                        <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">{{ $fSection['subtitle'] }}</p>
                    </div>
                    <a href="{{ route('tenant.kos.index', ['facilities' => [$fSection['id']]]) }}"
                       class="text-xs font-semibold text-primary-500 hover:text-primary-600 transition flex items-center gap-0.5 shrink-0">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
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
                <div class="flex items-end justify-between mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Kos Terbaru</h2>
                        <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">Temukan pilihan kos yang baru ditambahkan.</p>
                    </div>
                    <a href="{{ route('tenant.kos.index') }}" class="text-xs font-semibold text-primary-500 hover:text-primary-600 transition flex items-center gap-0.5 shrink-0">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($latestKos as $lk)
                        @include('tenant.partials.kos-card', ['kos' => $lk, 'favoritedIds' => $favoritedIds])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ============================================================
             FAVORIT (Kos yang kamu simpan)
             ============================================================ --}}
        <section aria-label="Kos yang kamu simpan" class="{{ $favoriteKos->isEmpty() ? '' : '' }}">
            <div class="flex items-end justify-between mb-5">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Kos yang Kamu Simpan ❤️</h2>
                    <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">Kembali lagi ke kos favoritmu kapan saja.</p>
                </div>
                @if($favoriteKos->isNotEmpty())
                    <a href="{{ route('tenant.favorites.index') }}" class="text-xs font-semibold text-primary-500 hover:text-primary-600 transition flex items-center gap-0.5 shrink-0">
                        Lihat Semua Favorit <i class="ri-arrow-right-s-line"></i>
                    </a>
                @endif
            </div>

            @if($favoriteKos->isEmpty())
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 flex flex-col items-center text-center">
                    <div class="w-11 h-11 rounded-2xl bg-red-50 dark:bg-red-500/10 flex items-center justify-center">
                        <i class="ri-heart-line text-xl text-red-400"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada kos yang disimpan.</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Temukan kos yang kamu suka dan simpan di sini.</p>
                    <a href="{{ route('tenant.kos.index') }}"
                       class="mt-4 inline-flex items-center gap-2 bg-primary-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-primary-600 transition shadow-sm shadow-primary-500/30">
                        <i class="ri-search-eye-line"></i> Cari Kos
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($favoriteKos as $fk)
                        @include('tenant.partials.kos-card', ['kos' => $fk, 'favoritedIds' => $favoritedIds])
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ============================================================
             BARU KAMU LIHAT (localStorage) — otomatis disembunyikan jika kosong
             ============================================================ --}}
        <section aria-label="Baru kamu lihat" x-data="recentlyViewed()"
                 x-show="!loading && recent.length > 0" x-cloak>
            <div class="flex items-end justify-between mb-5">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Baru Kamu Lihat</h2>
                    <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">Lanjutkan dari kos yang baru kamu kunjungi.</p>
                </div>
            </div>

            <div class="flex gap-4 overflow-x-auto pb-2 -mx-1 px-1 snap-x snap-mandatory">
                <template x-for="item in recent" :key="item.id">
                    <a :href="item.url"
                       class="relative shrink-0 w-56 snap-start block bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden hover:shadow-lg hover:-translate-y-0.5 hover:border-primary-200 dark:hover:border-primary-500/30 transition-all duration-200 group">
                        <span class="block text-sm font-bold text-slate-900 dark:text-white px-4 pt-4 truncate" x-text="item.name"></span>
                        <span class="block text-xs text-slate-400 dark:text-slate-500 px-4 mt-1 line-clamp-1" x-text="item.address"></span>
                        <span class="block px-4 pb-4 pt-2.5 text-sm font-black text-primary-600 dark:text-primary-300" x-text="item.price"></span>
                    </a>
                </template>
            </div>

            <div class="mt-3">
                <button type="button" @click="clearRecent()"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 dark:text-slate-500 hover:text-red-500 transition">
                    <i class="ri-delete-bin-line"></i> Hapus riwayat
                </button>
            </div>
        </section>

        {{-- ============================================================
             TRUST — Kenapa mencari kos di KosManager?
             ============================================================ --}}
        <section class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 sm:p-7" aria-label="Kenapa mencari kos di KosManager">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white text-center">Kenapa mencari kos di KosManager?</h2>
            <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-500/10 flex items-center justify-center shrink-0"><i class="ri-flashlight-line text-green-600 dark:text-green-400"></i></span>
                    <p class="text-[11px] font-semibold text-slate-700 dark:text-slate-200 leading-tight">Booking langsung terkonfirmasi</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0"><i class="ri-price-tag-3-line text-blue-600 dark:text-blue-400"></i></span>
                    <p class="text-[11px] font-semibold text-slate-700 dark:text-slate-200 leading-tight">Informasi kamar jelas</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center shrink-0"><i class="ri-wallet-3-line text-amber-600 dark:text-amber-400"></i></span>
                    <p class="text-[11px] font-semibold text-slate-700 dark:text-slate-200 leading-tight">Pembayaran tercatat</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center shrink-0"><i class="ri-map-pin-2-line text-rose-600 dark:text-rose-400"></i></span>
                    <p class="text-[11px] font-semibold text-slate-700 dark:text-slate-200 leading-tight">Lokasi kos tersedia</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center shrink-0"><i class="ri-shield-check-line text-indigo-600 dark:text-indigo-400"></i></span>
                    <p class="text-[11px] font-semibold text-slate-700 dark:text-slate-200 leading-tight">Data booking aman</p>
                </div>
            </div>
        </section>

        {{-- ============================================================
             BANTUAN (accordion)
             ============================================================ --}}
        <section aria-label="Bantuan" x-data="{ open: null }">
            <div class="flex items-end justify-between mb-5">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Masih bingung?</h2>
                    <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">Temukan jawaban seputar booking, pembayaran, dan kos.</p>
                </div>
            </div>
            <div class="max-w-3xl mx-auto space-y-3">
                @php
                    $faqs = [
                        ['q' => 'Bagaimana cara booking?', 'a' => 'Cari kos yang kamu suka, buka halaman detailnya, pilih kamar yang tersedia, lalu klik "Booking Sekarang". Lengkapi periode sewa dan konfirmasi. Booking langsung terkonfirmasi.'],
                        ['q' => 'Bagaimana cara membayar?', 'a' => 'Setelah booking, kamu akan memiliki tagihan. Buka menu Tagihan, lalu pilih metode pembayaran (transfer bank, e-wallet/QRIS, atau tunai) dan unggah bukti pembayaran. Bukti akan diverifikasi oleh pengelola.'],
                        ['q' => 'Bagaimana cara check-in?', 'a' => 'Setelah kamu datang dan bertemu pengelola, pengelola akan memproses check-in kamarmu. Setelah check-in, kamar akan tampil di bagian "Kos Saya" pada beranda.'],
                        ['q' => 'Bagaimana jika ingin membatalkan booking?', 'a' => 'Buka menu Booking Saya, pilih booking yang ingin dibatalkan, lalu klik "Batalkan Booking". Kamar akan tersedia kembali untuk disewa orang lain.'],
                    ];
                @endphp
                @foreach($faqs as $i => $faq)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
                        <button type="button"
                                class="w-full flex items-center justify-between gap-3 px-5 py-4 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40"
                                @click="open = open === {{ $i }} ? null : {{ $i }}"
                                :aria-expanded="(open === {{ $i }}).toString()">
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $faq['q'] }}</span>
                            <i class="ri-arrow-down-s-line text-slate-400 transition-transform"
                               :class="open === {{ $i }} ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open === {{ $i }}" x-cloak x-transition
                             class="px-5 pb-4 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ============================================================
             FINAL CTA
             ============================================================ --}}
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 text-white p-8 sm:p-12 text-center">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-56 h-56 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/2 -translate-x-1/4"></div>
            </div>
            <div class="relative max-w-xl mx-auto">
                <h2 class="text-xl sm:text-2xl font-bold">Sudah menemukan kos yang cocok?</h2>
                <p class="mt-2 text-primary-100/80 text-sm">Pesan kamar pilihanmu sekarang.</p>
                <a href="{{ route('tenant.kos.index') }}"
                   class="mt-6 inline-flex items-center gap-2 bg-white text-primary-700 text-sm font-bold px-7 py-3.5 rounded-2xl hover:bg-primary-50 transition shadow-xl shadow-black/10 active:scale-95">
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