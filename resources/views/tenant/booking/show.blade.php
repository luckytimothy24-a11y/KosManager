<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Booking {{ $booking->booking_code }}" description="Detail booking kamar Anda.">
            <x-button href="{{ route('tenant.booking.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Booking Saya', 'url' => route('tenant.booking.index')],
            ['label' => 'Booking ' . $booking->booking_code],
        ]" />
        <x-alert />

        <div class="max-w-3xl space-y-6">
            {{-- Status & Booking Code --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kode Booking</p>
                        <p class="mt-1 text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $booking->booking_code }}</p>
                    </div>
                    <x-status-badge :status="$booking->status" context="booking" />
                </div>

                @if($booking->status === 'approved')
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-green-100 dark:border-green-500/20 bg-green-50/70 dark:bg-green-500/[0.06] px-4 py-3">
                        <i class="ri-check-double-line text-green-600 dark:text-green-400 mt-0.5"></i>
                        <p class="text-sm text-green-800 dark:text-green-300/90">Booking sudah terkonfirmasi. Kamar telah dipesan untuk Anda.</p>
                    </div>
                @elseif($booking->status === 'pending')
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-yellow-100 dark:border-yellow-500/20 bg-yellow-50/70 dark:bg-yellow-500/[0.06] px-4 py-3">
                        <i class="ri-time-line text-yellow-600 dark:text-yellow-400 mt-0.5"></i>
                        <p class="text-sm text-yellow-800 dark:text-yellow-300/90">Booking menunggu konfirmasi.</p>
                    </div>
                @elseif($booking->status === 'completed')
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/70 dark:bg-blue-500/[0.06] px-4 py-3">
                        <i class="ri-door-open-line text-blue-600 dark:text-blue-400 mt-0.5"></i>
                        <p class="text-sm text-blue-800 dark:text-blue-300/90">Booking selesai. Anda telah terdaftar sebagai penghuni dan proses check-in sudah dilakukan.</p>
                    </div>
                @elseif($booking->status === 'expired')
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-4 py-3">
                        <i class="ri-time-zone-line text-slate-500 dark:text-slate-400 mt-0.5"></i>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Periode sewa booking ini telah berakhir tanpa proses check-in, sehingga statusnya menjadi kedaluwarsa.</p>
                    </div>
                @elseif($booking->status === 'rejected')
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-red-100 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] px-4 py-3">
                        <i class="ri-close-circle-line text-red-600 dark:text-red-400 mt-0.5"></i>
                        <p class="text-sm text-red-800 dark:text-red-300/90">Booking ditolak oleh pemilik kos.</p>
                    </div>
                @elseif($booking->status === 'cancelled')
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-4 py-3">
                        <i class="ri-forbid-line text-slate-500 dark:text-slate-400 mt-0.5"></i>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Booking ini telah dibatalkan.</p>
                    </div>
                @endif

                @if($booking->nextAction())
                    <div class="mt-3 flex items-start gap-2.5 rounded-xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] px-4 py-3">
                        <i class="ri-lightbulb-flash-line text-amber-500 dark:text-amber-400 mt-0.5"></i>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Langkah Selanjutnya</p>
                            <p class="mt-0.5 text-sm text-amber-800 dark:text-amber-300/90">{{ $booking->nextAction() }}</p>
                        </div>
                    </div>
                @endif

                @if(in_array($booking->status, ['rejected', 'cancelled', 'expired']))
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <a href="{{ route('tenant.kos.index') }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 rounded-xl transition shadow-sm shadow-primary-500/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                            <i class="ri-search-line"></i> Cari Kos
                        </a>
                        @if(in_array($booking->status, ['cancelled', 'rejected']))
                            <a href="{{ route('tenant.booking.create') }}"
                               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                                <i class="ri-add-line"></i> Booking Baru
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Foto Kos & Kamar --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-image-line text-primary-500"></i> Foto Kos &amp; Kamar
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Kos</p>
                        <div class="relative rounded-xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 aspect-[16/9]">
                            @if($booking->kos->photo)
                                <img src="{{ asset('storage/' . $booking->kos->photo) }}" alt="Foto {{ $booking->kos->name }}"
                                     class="absolute inset-0 w-full h-full object-cover"
                                     loading="lazy" onerror="this.style.display='none'">
                            @endif
                            <span class="absolute inset-0 flex items-center justify-center text-[4rem] leading-none font-black uppercase text-primary-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($booking->kos->name, 0, 1) }}</span>
                        </div>
                        <p class="mt-2 text-xs font-medium text-slate-600 dark:text-slate-300">{{ $booking->kos->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Kamar</p>
                        <div class="relative rounded-xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 aspect-[16/9]">
                            @if($booking->kamar->photo)
                                <img src="{{ asset('storage/' . $booking->kamar->photo) }}" alt="Foto Kamar {{ $booking->kamar->room_number }}"
                                     class="absolute inset-0 w-full h-full object-cover"
                                     loading="lazy" onerror="this.style.display='none'">
                            @endif
                            <span class="absolute inset-0 flex items-center justify-center text-[4rem] leading-none font-black uppercase text-primary-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($booking->kamar->room_number, 0, 2) }}</span>
                        </div>
                        <p class="mt-2 text-xs font-medium text-slate-600 dark:text-slate-300">Kamar {{ $booking->kamar->room_number }} · {{ $booking->kamar->room_type }}</p>
                    </div>
                </div>
            </div>

            {{-- Booking Timeline / Terminal State --}}
            @php
                $isCompleted = $booking->status === 'completed';
                $isApproved = $booking->status === 'approved';
                $isTerminal = $booking->isTerminal();
            @endphp
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
                @if($isTerminal)
                    {{-- Status terminal (dibatalkan/ditolak/kedaluwarsa) tidak lagi berupa progres --}}
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-5">
                        <i class="ri-stop-circle-line text-red-500"></i> Status Booking
                    </h3>
                    <div class="flex items-center gap-4 rounded-xl border border-red-100 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] px-4 py-4">
                        <div class="w-11 h-11 rounded-full bg-red-100 dark:bg-red-500/15 flex items-center justify-center shrink-0">
                            <i class="{{ $booking->status === 'expired' ? 'ri-time-zone-line' : 'ri-close-circle-line' }} text-red-600 dark:text-red-400 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-red-800 dark:text-red-300">{{ \StatusLabels::bookingLabel($booking->status) }}</p>
                            <p class="mt-0.5 text-sm text-red-700/80 dark:text-red-300/80">
                                {{ $booking->status === 'expired'
                                    ? 'Periode sewa berakhir tanpa check-in; booking tidak berlanjut.'
                                    : ($booking->status === 'rejected'
                                        ? 'Booking tidak dilanjutkan oleh pengelola kos.'
                                        : 'Booking dihentikan dan tidak dapat dilanjutkan.') }}
                            </p>
                        </div>
                    </div>
                @else
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-5">
                        <i class="ri-road-map-line text-primary-500"></i> Status Booking
                    </h3>
                    @php
                        $steps = [
                            ['label' => 'Booking Dibuat', 'done' => true, 'current' => false],
                            ['label' => 'Booking Dikonfirmasi', 'done' => $isApproved || $isCompleted, 'current' => false],
                            ['label' => 'Check-In', 'done' => $isCompleted, 'current' => $isApproved && !$isCompleted],
                            ['label' => 'Check-Out', 'done' => $hasCheckOut, 'current' => false],
                        ];
                    @endphp
                    <div class="flex items-center gap-0">
                        @foreach($steps as $i => $step)
                            @php
                                $color = $step['done'] ? 'bg-green-500' : ($step['current'] ? 'bg-blue-500' : 'bg-slate-300 dark:bg-slate-600');
                                $textColor = $step['done'] ? 'text-green-600 dark:text-green-400' : ($step['current'] ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500');
                                $lineColor = $step['done'] && ($i < count($steps) - 1 && $steps[$i+1]['done']) ? 'bg-green-500' : 'bg-slate-200 dark:bg-slate-700';
                            @endphp
                            <div class="flex flex-col items-center flex-1 last:flex-none">
                                <div class="w-7 h-7 rounded-full {{ $color }} flex items-center justify-center shrink-0">
                                    @if($step['done'])
                                        <i class="ri-check-line text-white text-xs"></i>
                                    @elseif($step['current'])
                                        <div class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></div>
                                    @else
                                        <div class="w-2 h-2 rounded-full bg-white/60"></div>
                                    @endif
                                </div>
                                <p class="mt-2 text-[11px] font-semibold {{ $textColor }} text-center leading-tight whitespace-nowrap">{{ $step['label'] }}</p>
                            </div>
                            @if($i < count($steps) - 1)
                                <div class="flex-1 h-0.5 {{ $lineColor }} -mt-4 mx-1"></div>
                            @endif
                        @endforeach
                    </div>
                @endif
                @if($booking->start_date && $booking->end_date)
                    <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span><i class="ri-calendar-line mr-1"></i> Mulai: {{ $booking->start_date->format('d M Y') }}</span>
                        <span>Selesai: {{ $booking->end_date->format('d M Y') }} <i class="ri-calendar-line ml-1"></i></span>
                    </div>
                @endif
            </div>

            {{-- Jembatan Kontrak & Tagihan untuk booking selesai (G3) --}}
            @if($booking->status === 'completed' && ($kontrak || $tagihan))
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-blue-100 dark:border-blue-500/20 p-6 sm:p-8">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                        <i class="ri-file-list-3-line text-primary-500"></i> Kontrak &amp; Tagihan
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Booking Anda telah menjadi kontrak aktif. Kelola tagihan sewa Anda di bawah ini.</p>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5 text-sm">
                        @if($kontrak)
                            <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nomor Kontrak</dt>
                                <dd class="mt-0.5 font-mono font-bold text-slate-900 dark:text-white">{{ $kontrak->contract_number }}</dd>
                            </div>
                        @endif
                        @if($kontrak)
                            <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status Kontrak</dt>
                                <dd class="mt-0.5 font-semibold text-slate-700 dark:text-slate-200">{{ \StatusLabels::kontrakLabel($kontrak->status) }}</dd>
                            </div>
                        @endif
                    </dl>
                    @if($tagihan)
                        <a href="{{ route('tenant.tagihan.show', $tagihan) }}"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 rounded-xl transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                            <i class="ri-wallet-3-line"></i> Lihat Tagihan Terbaru
                        </a>
                    @endif
                </div>
            @endif

            {{-- Detail Penyewa --}}
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
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $booking->booking_date->translatedFormat('d F Y') }}</dd>
                        </div>
                    </div>
                </dl>
            </div>

            {{-- Kamar & Periode --}}
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
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $booking->start_date->translatedFormat('d F Y') }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-flag-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Selesai</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $booking->end_date->translatedFormat('d F Y') }}</dd>
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

            {{-- Rincian Biaya --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-money-dollar-circle-line text-primary-500"></i> Rincian Biaya
                </h3>
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        <p>Harga sewa ({{ \StatusLabels::rentalTypeLabel($booking->rental_type) }})</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Tarif {{ $booking->priceUnitLabel() }} untuk periode yang dipilih.</p>
                    </div>
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($booking->price, 0, ',', '.') }}</span>
                </div>
            </div>

            @if($booking->notes)
                <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 p-6 bg-blue-50/50 dark:bg-blue-500/[0.06]">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-blue-900 dark:text-blue-200">
                        <i class="ri-chat-quote-line"></i> Catatan
                    </h3>
                    <p class="mt-2.5 text-sm text-blue-800/90 dark:text-blue-300/90 leading-relaxed whitespace-pre-line">{{ $booking->notes }}</p>
                </div>
            @endif

            {{-- Aksi Pembatalan --}}
            @if($booking->isCancellable())
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <x-confirm-dialog
                            title="Batalkan booking ini?"
                            description="Booking &ldquo;{{ $booking->booking_code }}&rdquo; akan dibatalkan dan tidak dapat dikembalikan."
                            confirmText="Ya, Batalkan"
                            triggerClass="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 rounded-xl hover:bg-red-100 dark:hover:bg-red-500/20 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500"
                            aria-label="Batalkan booking {{ $booking->booking_code }}"
                        >
                            <x-slot name="slot"><i class="ri-close-circle-line"></i> Batalkan Booking</x-slot>
                            <x-slot name="actions">
                                <form method="POST" action="{{ route('tenant.booking.cancel', $booking) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                    @csrf
                                    <button type="submit" :disabled="submitting"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition shadow-sm shadow-red-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span x-show="!submitting">Ya, Batalkan</span>
                                        <span x-show="submitting" x-cloak>Membatalkan...</span>
                                    </button>
                                </form>
                            </x-slot>
                        </x-confirm-dialog>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
