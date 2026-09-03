<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <x-alert />

        {{-- Success hero --}}
        <div class="text-center">
            <div class="w-20 h-20 rounded-full bg-green-50 dark:bg-green-500/10 mx-auto flex items-center justify-center animate-bounce">
                <span class="text-4xl">🎉</span>
            </div>

            {{-- Foto kamar yang baru dipesan --}}
            <div class="mt-5 w-full max-w-sm mx-auto relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 aspect-[16/9]">
                @if($booking->kamar->photo)
                    <img src="{{ asset('storage/' . $booking->kamar->photo) }}" alt="Foto Kamar {{ $booking->kamar->room_number }}"
                         class="absolute inset-0 w-full h-full object-cover"
                         loading="lazy" onerror="this.style.display='none'">
                @elseif($booking->kos->photo)
                    <img src="{{ asset('storage/' . $booking->kos->photo) }}" alt="Foto {{ $booking->kos->name }}"
                         class="absolute inset-0 w-full h-full object-cover"
                         loading="lazy" onerror="this.style.display='none'">
                @endif
                <span class="absolute inset-0 flex items-center justify-center text-[5rem] leading-none font-black uppercase text-primary-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($booking->kamar->room_number, 0, 2) }}</span>
            </div>

            <h1 class="mt-5 text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">Booking Berhasil!</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Kamar <span class="font-bold text-slate-700 dark:text-slate-200">Kamar {{ $booking->kamar->room_number }}</span> di
                <span class="font-bold text-slate-700 dark:text-slate-200">{{ $booking->kos->name }}</span> berhasil kamu pesan.
            </p>
        </div>

        {{-- Instant confirmation notice --}}
        <div class="flex items-start gap-3 rounded-2xl border border-green-100 dark:border-green-500/20 bg-green-50/70 dark:bg-green-500/[0.06] px-5 py-4">
            <i class="ri-flashlight-line text-green-600 dark:text-green-400 text-xl mt-0.5"></i>
            <div>
                <p class="text-sm font-bold text-green-800 dark:text-green-300">⚡ Booking langsung terkonfirmasi</p>
                <p class="text-xs text-green-700/80 dark:text-green-300/80 mt-0.5">Kamar telah langsung dipesan untukmu. Tidak perlu menunggu persetujuan.</p>
            </div>
        </div>

        {{-- Confirmation detail card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                    <i class="ri-file-list-3-line text-primary-500"></i> Detail Booking
                </h2>
                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-lg bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-300 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Terkonfirmasi
                </span>
            </div>
            <dl class="px-6 py-5 space-y-4 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-quill-pen-line text-slate-400"></i> Kode Booking</dt>
                    <dd class="font-mono font-bold text-slate-900 dark:text-white">{{ $booking->booking_code }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-building-2-line text-slate-400"></i> Kos</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white text-right">{{ $booking->kos->name }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-door-open-line text-slate-400"></i> Kamar</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white">Kamar {{ $booking->kamar->room_number }} · {{ $booking->kamar->room_type }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-money-dollar-circle-line text-slate-400"></i> Harga</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($booking->price, 0, ',', '.') }}<span class="text-xs text-slate-400">/{{ $booking->rental_type === 'daily' ? 'hari' : 'bulan' }}</span></dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-calendar-line text-slate-400"></i> Periode</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white text-right">{{ $booking->start_date->format('d M Y') }} — {{ $booking->end_date->format('d M Y') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-calendar-check-line text-slate-400"></i> Tanggal Booking</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white">{{ $booking->booking_date->translatedFormat('d F Y') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Next step hint --}}
        <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/50 dark:bg-blue-500/[0.06] px-5 py-4">
            <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">Langkah selanjutnya</p>
            <p class="text-xs text-blue-800/80 dark:text-blue-300/80 mt-1">Bayar sesuai tagihan yang muncul setelah booking, lalu datang ke kos dan minta pengelola untuk memproses check-in.</p>
            <a href="{{ route('tenant.tagihan.index') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100 transition">
                Lihat Tagihan <i class="ri-arrow-right-s-line"></i>
            </a>
        </div>

        {{-- CTA --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('tenant.booking.show', $booking) }}"
               class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold rounded-xl transition shadow-sm shadow-primary-500/30">
                <i class="ri-calendar-check-line"></i> Lihat Booking
            </a>
            <a href="{{ route('tenant.kos.show', $booking->kos) }}"
               class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-building-2-line"></i> Lihat Detail Kos
            </a>
            <a href="{{ route('tenant.kos.index') }}"
               class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-search-line"></i> Kembali Cari Kos
            </a>
        </div>
    </div>
</x-app-layout>
