<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route($prefix.'.booking.index'); @endphp
    <x-slot name="header">
        <x-page-header title="Booking {{ $booking->booking_code }}" description="Detail permintaan sewa kamar.">
            <x-button href="{{ $backUrl }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="max-w-3xl space-y-6">
            {{-- Ringkasan status --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kode Booking</p>
                        <p class="mt-1 text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $booking->booking_code }}</p>
                    </div>
                    <x-status-badge :status="$booking->status" context="booking" />
                </div>
            </div>

            {{-- Detail penyewa & kamar --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-user-heart-line text-primary-500"></i> Informasi Penyewa
                </h3>
                <dl class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-sm font-bold shrink-0">{{ strtoupper(substr($booking->user->name, 0, 1)) }}</span>
                        <div class="min-w-0">
                            <dt class="sr-only">Nama penyewa</dt>
                            <dd class="font-semibold text-slate-900 dark:text-white truncate">{{ $booking->user->name }}</dd>
                            <dd class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $booking->user->email }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-calendar-close-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tanggal Booking</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}</dd>
                        </div>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-door-open-line text-primary-500"></i> Kamar &amp; Periode Sewa
                </h3>
                <dl class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div class="flex items-start gap-2.5">
                        <i class="ri-building-2-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kos</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $booking->kos->name }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-price-tag-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kamar</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $booking->kamar->room_number }} · {{ $booking->kamar->room_type }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-play-circle-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mulai</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d F Y') }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-flag-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Selesai</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::parse($booking->end_date)->translatedFormat('d F Y') }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-repeat-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tipe Sewa</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ \StatusLabels::rentalTypeLabel($booking->rental_type) }}</dd>
                        </div>
                    </div>
                </dl>
            </div>

            {{-- Rincian biaya + catatan --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-money-dollar-circle-line text-primary-500"></i> Rincian Biaya
                </h3>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Total harga sewa ({{ \StatusLabels::rentalTypeLabel($booking->rental_type) }})</span>
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($booking->price, 0, ',', '.') }}</span>
                </div>
            </div>

            @if($booking->notes)
                <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 p-6 bg-blue-50/50 dark:bg-blue-500/[0.06]">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-blue-900 dark:text-blue-200">
                        <i class="ri-chat-quote-line"></i> Catatan Penyewa
                    </h3>
                    <p class="mt-2.5 text-sm text-blue-800/90 dark:text-blue-300/90 leading-relaxed whitespace-pre-line">{{ $booking->notes }}</p>
                </div>
            @endif

            {{-- Aksi --}}
            @can('update', $booking)
                @if($booking->status === 'approved')
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8" x-data="{ submitting: false }">
                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <a href="{{ route("$prefix.checkin.index") }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 active:bg-primary-700 rounded-xl transition shadow-sm shadow-primary-500/30">
                                <i class="ri-login-box-line"></i> Proses Check-In
                            </a>
                        </div>
                    </div>
                @endif
            @endcan
        </div>
    </div>
</x-app-layout>
