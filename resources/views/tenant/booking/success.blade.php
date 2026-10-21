<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <x-alert />

        @php
            $isPending = $booking->status === 'pending';
            $paymentStatus = method_exists($booking, 'paymentStatus') ? $booking->paymentStatus() : 'none';
        @endphp

        {{-- Success hero --}}
        <div class="text-center">
            <div class="w-20 h-20 rounded-full bg-green-50 dark:bg-green-500/10 mx-auto flex items-center justify-center animate-bounce">
                <span class="text-4xl">🎉</span>
            </div>

            <h1 class="mt-5 text-2xl sm:text-3xl font-bold text-slate-900 dark:text-slate-50">Booking Berhasil!</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Kamar <span class="font-bold text-slate-700 dark:text-slate-200">Kamar {{ $booking->kamar->room_number }}</span> di
                <span class="font-bold text-slate-700 dark:text-slate-200">{{ $booking->kos->name }}</span> sudah dipesan untukmu.
            </p>
        </div>

        {{-- Status notice — payment-first for pending bookings --}}
        @if($isPending)
            <div class="flex items-start gap-3 rounded-2xl border border-amber-200 dark:border-amber-500/25 bg-amber-50/70 dark:bg-amber-500/[0.08] px-5 py-4">
                <i class="ri-wallet-3-line text-amber-600 dark:text-amber-400 text-xl mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Booking dibuat — lanjutkan pembayaran</p>
                    <p class="text-xs text-amber-700/90 dark:text-amber-300/80 mt-0.5">
                        Kamar sudah dipesan untukmu (kode <span class="font-semibold">{{ $booking->booking_code }}</span>).
                        Selesaikan pembayaran booking. Booking dikonfirmasi setelah pembayaran diverifikasi pemilik kos.
                    </p>
                </div>
            </div>
        @else
            <div class="flex items-start gap-3 rounded-2xl border border-green-100 dark:border-green-500/20 bg-green-50/70 dark:bg-green-500/[0.06] px-5 py-4">
                <i class="ri-flashlight-line text-green-600 dark:text-green-400 text-xl mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-green-800 dark:text-green-300">Booking dibuat</p>
                    <p class="text-xs text-green-700/80 dark:text-green-300/80 mt-0.5">
                        Kamar {{ $booking->kamar->room_number }} di {{ $booking->kos->name }} sudah dipesan untukmu.
                    </p>
                </div>
            </div>
        @endif

        {{-- Detail booking --}}
        <div class="card-surface overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/[0.08] flex items-center justify-between gap-3">
                <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50">
                    <i class="ri-file-list-3-line text-primary-500"></i> Detail Booking
                </h2>
                <x-status-badge :status="$booking->status" context="booking" />
            </div>
            <dl class="px-6 py-5 space-y-4 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-quill-pen-line text-slate-400"></i> Kode Booking</dt>
                    <dd class="font-mono font-bold text-slate-900 dark:text-slate-50">{{ $booking->booking_code }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-building-2-line text-slate-400"></i> Kos</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50 text-right">{{ $booking->kos->name }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-door-open-line text-slate-400"></i> Kamar</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50">Kamar {{ $booking->kamar->room_number }} · {{ $booking->kamar->room_type }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-team-line text-slate-400"></i> Jumlah penghuni</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50">1 orang (satu kamar untuk satu penghuni)</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-wallet-3-line text-slate-400"></i> Pembayaran</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50">Tagihan pribadi</dd>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 -mt-2">
                    Total harga kamar untuk periode ini. Satu kamar untuk satu penghuni.
                </p>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-money-dollar-circle-line text-slate-400"></i> Total harga kamar</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50">Rp {{ number_format($booking->price, 0, ',', '.') }}<span class="text-xs text-slate-400">/{{ $booking->rental_type === 'daily' ? 'hari' : 'bulan' }}</span></dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-calendar-line text-slate-400"></i> Periode</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50 text-right">{{ $booking->start_date->format('d M Y') }} – {{ $booking->end_date->format('d M Y') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-calendar-check-line text-slate-400"></i> Tanggal Booking</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50">{{ $booking->booking_date->translatedFormat('d F Y') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Foto kamar --}}
        <div class="w-full max-w-sm mx-auto relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-700 dark:via-slate-700 dark:to-slate-700 aspect-[16/9]">
            @if($booking->kamar->photo)
                <img src="{{ asset('storage/' . $booking->kamar->photo) }}" alt="Foto Kamar {{ $booking->kamar->room_number }}"
                     class="absolute inset-0 w-full h-full object-cover"
                     loading="lazy" onerror="this.style.display='none'">
            @elseif($booking->kos->photo)
                <img src="{{ asset('storage/' . $booking->kos->photo) }}" alt="Foto {{ $booking->kos->name }}"
                     class="absolute inset-0 w-full h-full object-cover"
                     loading="lazy" onerror="this.style.display='none'">
            @endif
            <span class="absolute inset-0 flex items-center justify-center text-[5rem] leading-none font-bold uppercase text-primary-900/[0.08] dark:text-slate-50/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($booking->kamar->room_number, 0, 2) }}</span>
        </div>

        {{-- Next step — payment guidance --}}
        @if($isPending)
            <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/50 dark:bg-blue-500/[0.06] px-5 py-4">
                <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">Langkah selanjutnya</p>
                <p class="text-xs text-blue-800/80 dark:text-blue-300/80 mt-1">
                    Selesaikan pembayaran booking <span class="font-semibold">Rp {{ number_format($booking->price, 0, ',', '.') }}</span> di halaman detail booking (tunai / transfer bank).
                    Setelah pembayaran diverifikasi pemilik kos, booking akan dikonfirmasi.
                </p>
                <a href="{{ route('tenant.booking.show', $booking) }}#pembayaran"
                   class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100 transition">
                    Lakukan Pembayaran Sekarang <i class="ri-arrow-right-s-line"></i>
                </a>
            </div>
        @else
            <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/50 dark:bg-blue-500/[0.06] px-5 py-4">
                <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">Langkah selanjutnya</p>
                <p class="text-xs text-blue-800/80 dark:text-blue-300/80 mt-1">Datang ke kos dan minta pengelola untuk memproses check-in sesuai jadwal.</p>
            </div>
        @endif

        {{-- CTA --}}
        <div class="flex flex-col sm:flex-row gap-3">
            @if($isPending)
                <a href="{{ route('tenant.booking.show', $booking) }}#pembayaran"
                   class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition shadow-soft shadow-primary-600/20">
                    <i class="ri-wallet-3-line"></i> Bayar Sekarang
                </a>
            @else
                <a href="{{ route('tenant.booking.show', $booking) }}"
                   class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition shadow-soft shadow-primary-600/20">
                    <i class="ri-calendar-check-line"></i> Lihat Booking
                </a>
            @endif
            <a href="{{ route('tenant.kos.show', $booking->kos) }}"
               class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-building-2-line"></i> Lihat Detail Kos
            </a>
            <a href="{{ route('tenant.kos.index') }}"
               class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-search-line"></i> Kembali Cari Kos
            </a>
        </div>
    </div>
</x-app-layout>
