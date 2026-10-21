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
            <div class="card-surface p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-label">Kode Booking</p>
                        <p class="mt-1 text-xl font-bold font-mono text-slate-900 dark:text-slate-50">{{ $booking->booking_code }}</p>
                    </div>
                    <x-status-badge :status="$booking->status" context="booking" />
                </div>

                @if($booking->status === 'approved')
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-green-100 dark:border-green-500/20 bg-green-50/70 dark:bg-green-500/[0.06] px-4 py-3">
                        <i class="ri-check-double-line text-green-600 dark:text-green-400 mt-0.5"></i>
                        <p class="text-sm text-green-800 dark:text-green-300/90">Booking dikonfirmasi. Kamar telah dipesan untuk Anda.</p>
                    </div>
                @elseif($booking->status === 'pending')
                    @php $paymentStatus = isset($bookingPayment) ? ($bookingPayment->verification_status ?? 'none') : 'none'; @endphp
                    @if($paymentStatus === 'none')
                        <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-yellow-100 dark:border-yellow-500/20 bg-yellow-50/70 dark:bg-yellow-500/[0.06] px-4 py-3">
                            <i class="ri-time-line text-yellow-600 dark:text-yellow-400 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-bold text-yellow-800 dark:text-yellow-300">Booking berhasil dikirim — Status: Menunggu pembayaran</p>
                                <p class="text-sm text-yellow-800 dark:text-yellow-300/90 mt-1">Silakan lakukan pembayaran untuk melanjutkan. Booking belum dikonfirmasi sampai pembayaran diverifikasi pemilik kos.</p>
                            </div>
                        </div>
                    @elseif($paymentStatus === 'pending')
                        <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] px-4 py-3">
                            <i class="ri-bank-card-line text-amber-600 dark:text-amber-400 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Status pembayaran: Menunggu verifikasi Owner</p>
                                <p class="text-sm text-amber-800 dark:text-amber-300/90 mt-1">Pembayaran sudah dikirim. Booking masih menunggu konfirmasi pemilik kos.</p>
                            </div>
                        </div>
                    @elseif($paymentStatus === 'approved')
                        <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/70 dark:bg-blue-500/[0.06] px-4 py-3">
                            <i class="ri-shield-check-line text-blue-600 dark:text-blue-400 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-bold text-blue-800 dark:text-blue-300">Pembayaran terverifikasi</p>
                                <p class="text-sm text-blue-800 dark:text-blue-300/90 mt-1">Status booking: Menunggu konfirmasi Owner</p>
                            </div>
                        </div>
                    @elseif($paymentStatus === 'rejected')
                        <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-red-100 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] px-4 py-3">
                            <i class="ri-close-circle-line text-red-600 dark:text-red-400 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-bold text-red-800 dark:text-red-300">Pembayaran ditolak</p>
                                <p class="text-sm text-red-800 dark:text-red-300/90 mt-1">Silakan periksa alasan penolakan dan lakukan pembayaran kembali. Booking masih berstatus pending.</p>
                            </div>
                        </div>
                    @endif
                @elseif($booking->status === 'completed')
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/70 dark:bg-blue-500/[0.06] px-4 py-3">
                        <i class="ri-door-open-line text-blue-600 dark:text-blue-400 mt-0.5"></i>
                        <p class="text-sm text-blue-800 dark:text-blue-300/90">Booking selesai. Anda telah terdaftar sebagai penghuni dan proses check-in sudah dilakukan.</p>
                    </div>
                @elseif($booking->status === 'expired')
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-surface-elevated/50 px-4 py-3">
                        <i class="ri-time-zone-line text-slate-500 dark:text-slate-400 mt-0.5"></i>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Periode sewa booking ini telah berakhir tanpa proses check-in, sehingga statusnya menjadi kedaluwarsa.</p>
                    </div>
                @elseif($booking->status === 'rejected')
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-red-100 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] px-4 py-3">
                        <i class="ri-close-circle-line text-red-600 dark:text-red-400 mt-0.5"></i>
                        <p class="text-sm text-red-800 dark:text-red-300/90">Booking ditolak oleh pemilik kos.</p>
                    </div>
                @elseif($booking->status === 'cancelled')
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-surface-elevated/50 px-4 py-3">
                        <i class="ri-forbid-line text-slate-500 dark:text-slate-400 mt-0.5"></i>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Booking ini telah dibatalkan.</p>
                    </div>
                @endif

                @if($booking->nextAction())
                    <div class="mt-3 flex items-start gap-2.5 rounded-xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] px-4 py-3">
                        <i class="ri-lightbulb-flash-line text-amber-500 dark:text-amber-400 mt-0.5"></i>
                        <div>
                            <p class="text-label text-amber-700 dark:text-amber-400">Langkah Selanjutnya</p>
                            <p class="mt-0.5 text-sm text-amber-800 dark:text-amber-300/90">{{ $booking->nextAction() }}</p>
                        </div>
                    </div>
                @endif

                @if(in_array($booking->status, ['rejected', 'cancelled', 'expired']))
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <a href="{{ route('tenant.kos.index') }}"
                           class="btn-primary">
                            <i class="ri-search-line"></i> Cari Kos
                        </a>
                        @if(in_array($booking->status, ['cancelled', 'rejected']))
                            <a href="{{ route('tenant.booking.create') }}"
                               class="btn-secondary">
                                <i class="ri-add-line"></i> Booking Baru
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Booking Timeline / Terminal State --}}
            @php
                $isCompleted = $booking->status === 'completed';
                $isApproved = $booking->status === 'approved';
                $isTerminal = $booking->isTerminal();
            @endphp
            <div class="card-surface p-6 sm:p-8">
                @if($isTerminal)
                    {{-- Status terminal (dibatalkan/ditolak/kedaluwarsa) tidak lagi berupa progres --}}
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 mb-5">
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
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 mb-5">
                        <i class="ri-road-map-line text-primary-500"></i> Status Booking
                    </h3>
                    @php
                        $steps = [
                            ['label' => 'Booking Dibuat', 'done' => true, 'current' => false],
                            ['label' => 'Pembayaran', 'done' => isset($bookingPayment) && in_array($bookingPayment->verification_status ?? null, ['pending', 'approved'], true), 'current' => $booking->status === 'pending' && (! isset($bookingPayment) || ($bookingPayment->verification_status ?? 'none') === 'none')],
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
                                $lineColor = $step['done'] && ($i < count($steps) - 1 && $steps[$i+1]['done']) ? 'bg-green-500' : 'bg-slate-200 dark:bg-surface-elevated';
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
                    <div class="mt-5 pt-4 border-t border-slate-100 dark:border-white/[0.08] flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span><i class="ri-calendar-line mr-1"></i> Mulai: {{ $booking->start_date->format('d M Y') }}</span>
                        <span>Selesai: {{ $booking->end_date->format('d M Y') }} <i class="ri-calendar-line ml-1"></i></span>
                    </div>
                @endif
            </div>

            @if($booking->status === 'approved')
                @php $identityStatus = $booking->identityStatus(); @endphp
                <div class="card-surface p-6 sm:p-8 border-l-4 {{ $identityStatus === 'confirmed' ? 'border-green-500' : ($identityStatus === 'pending' ? 'border-amber-400' : ($identityStatus === 'rejected' ? 'border-red-400' : 'border-primary-500')) }}">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50">
                        <i class="{{ $identityStatus === 'pending' ? 'ri-time-line text-amber-500' : ($identityStatus === 'confirmed' ? 'ri-check-double-line text-green-500' : ($identityStatus === 'rejected' ? 'ri-close-circle-line text-red-500' : 'ri-login-box-line text-primary-500')) }}"></i>
                        Check-In
                        @if($identityStatus === 'pending')
                            <span class="ml-1 inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 px-2 py-0.5 text-[11px] font-bold">Menunggu Review Owner</span>
                        @elseif($identityStatus === 'confirmed')
                            <span class="ml-1 inline-flex items-center rounded-full bg-green-100 dark:bg-green-500/15 text-green-700 dark:text-green-300 px-2 py-0.5 text-[11px] font-bold">Menunggu Proses Owner</span>
                        @elseif($identityStatus === 'rejected')
                            <span class="ml-1 inline-flex items-center rounded-full bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-300 px-2 py-0.5 text-[11px] font-bold">Ditolak — Perlu Perbaikan</span>
                        @endif
                    </h3>
                    @if($identityStatus === 'not_submitted')
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Booking telah dikonfirmasi. Lengkapi identitas untuk mengajukan check-in.</p>
                        <a href="{{ route('tenant.checkin.identity', $booking) }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-bold text-white shadow-soft hover:bg-primary-700 transition">
                            <i class="ri-send-plane-line"></i> Lengkapi Identitas — Ajukan Check-In
                        </a>
                    @elseif($identityStatus === 'pending')
                        <p class="mt-2 text-sm text-amber-800 dark:text-amber-200">Pengajuan telah dikirim pada {{ $booking->identity_data_submitted_at?->translatedFormat('d F Y, H:i') }}. Menunggu konfirmasi owner — tidak perlu kirim ulang.</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Jika ada kesalahan data, Anda dapat memperbaikinya di halaman pengajuan.</p>
                        <a href="{{ route('tenant.checkin.identity', $booking) }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-amber-700 dark:text-amber-300 hover:text-amber-800">
                            <i class="ri-eye-line"></i> Lihat Status Pengajuan <i class="ri-arrow-right-s-line"></i>
                        </a>
                    @elseif($identityStatus === 'rejected')
                        <p class="mt-2 text-sm text-red-700 dark:text-red-300">Pengajuan ditolak owner.</p>
                        @if($booking->identity_rejection_reason)
                            <p class="mt-2 rounded-lg border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/[0.06] px-3 py-2 text-sm text-slate-700 dark:text-slate-200">Alasan: {{ $booking->identity_rejection_reason }}</p>
                        @endif
                        <a href="{{ route('tenant.checkin.identity', $booking) }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700 transition">
                            <i class="ri-edit-line"></i> Perbaiki &amp; Ajukan Ulang
                        </a>
                    @elseif($identityStatus === 'confirmed')
                        <p class="mt-2 text-sm text-green-800 dark:text-green-300">Identitas dikonfirmasi owner pada {{ $booking->identity_confirmed_at?->translatedFormat('d F Y, H:i') }}. Silakan datang sesuai periode sewa — owner akan memproses check-in final.</p>
                        <a href="{{ route('tenant.checkin.identity', $booking) }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-green-700 dark:text-green-300 hover:text-green-800">
                            <i class="ri-shield-check-line"></i> Lihat Detail <i class="ri-arrow-right-s-line"></i>
                        </a>
                    @endif
                </div>
            @endif

            {{-- Jembatan Kontrak & Tagihan untuk booking selesai (G3) --}}
            @if($booking->status === 'completed' && ($kontrak || $tagihan))
                <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-blue-100 dark:border-blue-500/20 p-6 sm:p-8">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/[0.08]">
                        <i class="ri-file-list-3-line text-primary-500"></i> Kontrak &amp; Tagihan
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Booking Anda telah menjadi kontrak aktif. Kelola tagihan sewa Anda di bawah ini.</p>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5 text-sm">
                        @if($kontrak)
                            <div class="rounded-xl border border-slate-100 dark:border-white/[0.08] p-3">
                                <dt class="text-label">Nomor Kontrak</dt>
                                <dd class="mt-0.5 font-mono font-bold text-slate-900 dark:text-slate-50">{{ $kontrak->contract_number }}</dd>
                            </div>
                        @endif
                        @if($kontrak)
                            <div class="rounded-xl border border-slate-100 dark:border-white/[0.08] p-3">
                                <dt class="text-label">Status Kontrak</dt>
                                <dd class="mt-0.5 font-semibold text-slate-700 dark:text-slate-200">{{ \StatusLabels::kontrakLabel($kontrak->status) }}</dd>
                            </div>
                        @endif
                    </dl>
                    @if($tagihan)
                        <a href="{{ route('tenant.tagihan.show', $tagihan) }}"
                           class="btn-primary">
                            <i class="ri-wallet-3-line"></i> Lihat Tagihan Terbaru
                        </a>
                    @endif
                </div>
            @endif

            {{-- Detail Penyewa --}}
            <div class="card-surface overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 px-6 py-4 border-b border-slate-100 dark:border-white/[0.08]">
                    <i class="ri-user-heart-line text-primary-500"></i> Informasi Penyewa
                </h3>
                <dl class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-sm font-bold shrink-0">{{ strtoupper(substr($booking->user->name, 0, 1)) }}</span>
                        <div class="min-w-0">
                            <dt class="sr-only">Nama penyewa</dt>
                            <dd class="font-semibold text-slate-900 dark:text-slate-50 truncate">{{ $booking->user->name }}</dd>
                            <dd class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $booking->user->email }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-calendar-close-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-label">Tanggal Booking</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $booking->booking_date->translatedFormat('d F Y') }}</dd>
                        </div>
                    </div>
                </dl>
            </div>

            {{-- Kamar & Periode --}}
            <div class="card-surface overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 px-6 py-4 border-b border-slate-100 dark:border-white/[0.08]">
                    <i class="ri-door-open-line text-primary-500"></i> Kamar &amp; Periode Sewa
                </h3>
                <dl class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div class="flex items-start gap-2.5">
                        <i class="ri-building-2-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-label">Kos</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $booking->kos->name }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-price-tag-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-label">Kamar</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $booking->kamar->room_number }} · {{ $booking->kamar->room_type }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-play-circle-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-label">Mulai</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $booking->start_date->translatedFormat('d F Y') }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-flag-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-label">Selesai</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $booking->end_date->translatedFormat('d F Y') }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-repeat-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-label">Tipe Sewa</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ \StatusLabels::rentalTypeLabel($booking->rental_type) }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-user-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-label">Penghuni</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">1 orang (satu kamar untuk satu penghuni)</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-wallet-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-label">Pembayaran</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">Tagihan pribadi</dd>
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Seluruh biaya kamar menjadi tanggunganmu.</p>
                        </div>
                    </div>
                </dl>
            </div>

            {{-- Rincian Biaya --}}
            <div class="card-surface p-6 sm:p-8">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/[0.08]">
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

            {{-- Foto Kos & Kamar --}}
            <div class="card-surface overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 px-6 py-4 border-b border-slate-100 dark:border-white/[0.08]">
                    <i class="ri-image-line text-primary-500"></i> Foto Kos &amp; Kamar
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5">
                    <div>
                        <p class="text-label mb-2">Kos</p>
                        <div class="relative rounded-xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-surface-elevated dark:via-surface-elevated dark:to-surface-elevated aspect-[16/9]">
                            @if($booking->kos->photo)
                                <img src="{{ asset('storage/' . $booking->kos->photo) }}" alt="Foto {{ $booking->kos->name }}"
                                     class="absolute inset-0 w-full h-full object-cover"
                                     loading="lazy" onerror="this.style.display='none'">
                            @endif
                            <span class="absolute inset-0 flex items-center justify-center text-[4rem] leading-none font-bold uppercase text-primary-900/[0.08] dark:text-slate-50/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($booking->kos->name, 0, 1) }}</span>
                        </div>
                        <p class="mt-2 text-xs font-medium text-slate-600 dark:text-slate-300">{{ $booking->kos->name }}</p>
                    </div>
                    <div>
                        <p class="text-label mb-2">Kamar</p>
                        <div class="relative rounded-xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-surface-elevated dark:via-surface-elevated dark:to-surface-elevated aspect-[16/9]">
                            @if($booking->kamar->photo)
                                <img src="{{ asset('storage/' . $booking->kamar->photo) }}" alt="Foto Kamar {{ $booking->kamar->room_number }}"
                                     class="absolute inset-0 w-full h-full object-cover"
                                     loading="lazy" onerror="this.style.display='none'">
                            @endif
                            <span class="absolute inset-0 flex items-center justify-center text-[4rem] leading-none font-bold uppercase text-primary-900/[0.08] dark:text-slate-50/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($booking->kamar->room_number, 0, 2) }}</span>
                        </div>
                        <p class="mt-2 text-xs font-medium text-slate-600 dark:text-slate-300">Kamar {{ $booking->kamar->room_number }} · {{ $booking->kamar->room_type }}</p>
                    </div>
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

            {{-- Pembayaran booking (prerequisite konfirmasi) --}}
            @if($booking->status === 'pending')
                @php
                    $paymentStatus = isset($bookingPayment) ? ($bookingPayment->verification_status ?? 'none') : 'none';
                @endphp
                <div id="pembayaran" class="card-surface p-6 sm:p-8 scroll-mt-24">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/[0.08]">
                        <i class="ri-wallet-3-line text-primary-500"></i> Pembayaran Booking
                    </h3>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Nominal (dihitung server)</span>
                        <span class="text-lg font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format((float) $booking->price, 0, ',', '.') }}</span>
                    </div>
                    @if($bookingPayment)
                        <div class="rounded-xl border border-slate-100 dark:border-white/[0.08] p-4 mb-4">
                            <p class="text-label">Status Pembayaran</p>
                            <p class="mt-1 text-sm font-bold {{ $bookingPayment->verification_status === 'approved' ? 'text-green-600' : ($bookingPayment->verification_status === 'pending' ? 'text-amber-600' : 'text-red-600') }}">
                                {{ \PaymentLabels::verificationLabel($bookingPayment->verification_status) }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Metode: {{ \PaymentLabels::paymentMethod($bookingPayment->payment_method) }}</p>
                        </div>
                    @endif

                    @if($paymentStatus === 'none' || $paymentStatus === 'rejected')
                        {{-- Rekening tujuan pembayaran Owner (server-side: booking → kos → owner) --}}
                        @include('tenant.partials.owner-bank-account', [
                            'bankAccount' => $booking->kos->owner?->bankAccount,
                            'paymentInfo' => $booking->kos->payment_info,
                            'title' => 'Bayar ke Pemilik Kos',
                        ])
                        <form method="POST" action="{{ route('tenant.booking.payment.store', $booking) }}" enctype="multipart/form-data"
                              x-data="{
                                  method: @js(old('payment_method', 'cash')),
                                  submitting: false,
                                  proof: { name: '', oversize: false },
                                  handleProofChange(event) {
                                      const file = event.target.files && event.target.files[0];
                                      if (!file) { this.proof = { name: '', oversize: false }; return; }
                                      const sizeMb = file.size / (1024 * 1024);
                                      this.proof = { name: file.name, oversize: sizeMb > 5 };
                                  },
                                  clearProof() {
                                      this.proof = { name: '', oversize: false };
                                      this.$refs.proofInput.value = '';
                                  }
                              }"
                              x-on:submit="submitting = true"
                              class="space-y-5">
                            @csrf
                            <div>
                                <p class="field-label mb-2">Metode Pembayaran</p>
                                <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="Metode pembayaran">
                                    <label for="method-transfer_bank"
                                           class="flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] px-3 text-sm font-semibold transition select-none"
                                           :class="method === 'transfer_bank'
                                               ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-200'
                                               : 'border-slate-200 text-slate-500 hover:border-slate-300 dark:border-white/[0.08] dark:text-slate-400'">
                                        <input type="radio" id="method-transfer_bank" name="payment_method" value="transfer_bank" x-model="method" class="sr-only">
                                        <i class="ri-bank-line text-base"></i> Transfer Bank
                                    </label>
                                    <label for="method-cash"
                                           class="flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] px-3 text-sm font-semibold transition select-none"
                                           :class="method === 'cash'
                                               ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-200'
                                               : 'border-slate-200 text-slate-500 hover:border-slate-300 dark:border-white/[0.08] dark:text-slate-400'">
                                        <input type="radio" id="method-cash" name="payment_method" value="cash" x-model="method" class="sr-only">
                                        <i class="ri-hand-coin-line text-base"></i> Tunai
                                    </label>
                                </div>
                                @error('payment_method') <p class="text-red-500 text-xs mt-1.5" aria-live="polite">{{ $message }}</p> @enderror
                            </div>

                            {{-- Transfer detail --}}
                            <div x-show="method === 'transfer_bank'" x-cloak
                                 class="rounded-xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/60 dark:bg-blue-500/[0.06] p-4" aria-live="polite">
                                <p class="flex items-center gap-1.5 text-sm font-semibold text-blue-800 dark:text-blue-200">
                                    <i class="ri-bank-line text-blue-600 dark:text-blue-300"></i> Transfer Bank
                                </p>
                                <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300">
                                    Transfer sebesar nominal di atas ke rekening pemilik kos yang tercantum di atas, lalu unggah bukti transfer di bawah.
                                </p>
                                <p class="mt-2 text-[11px] font-medium text-blue-700 dark:text-blue-300">Wajib unggah bukti transfer (JPG/PNG/PDF, maks 5MB).</p>
                            </div>

                            {{-- Cash detail --}}
                            <div x-show="method === 'cash'" x-cloak
                                 class="rounded-xl border border-emerald-100 dark:border-emerald-500/20 bg-emerald-50/60 dark:bg-emerald-500/[0.06] p-4" aria-live="polite">
                                <p class="flex items-center gap-1.5 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                    <i class="ri-hand-coin-line text-emerald-600 dark:text-emerald-300"></i> Pembayaran Tunai
                                </p>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                    Bayar sejumlah <strong class="text-slate-700 dark:text-slate-200">Rp {{ number_format((float) $booking->price, 0, ',', '.') }}</strong> langsung kepada pemilik/admin kos,
                                    lalu kirim konfirmasi di bawah. Pemilik kos akan memverifikasi pembayaran Anda.
                                </p>
                                <p class="mt-2 text-[11px] text-slate-400 dark:text-slate-500">Bukti pembayaran opsional untuk metode tunai.</p>
                            </div>

                            <div>
                                <label for="proof_file" class="field-label">
                                    Bukti Pembayaran
                                    <span class="font-normal" x-text="method === 'transfer_bank' ? '(wajib — JPG/PNG/PDF, maks 5MB)' : '(opsional — JPG/PNG/PDF, maks 5MB)'"></span>
                                </label>
                                <input type="file" id="proof_file" name="proof_file" x-ref="proofInput" accept=".jpg,.jpeg,.png,.pdf"
                                       x-bind:required="method === 'transfer_bank'"
                                       @change="handleProofChange($event)"
                                       class="field-input file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                                <div x-show="proof.name" x-cloak class="mt-2 flex items-center gap-3 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/70 dark:bg-surface-elevated/50 p-3">
                                    <span class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-500/10 flex items-center justify-center shrink-0"><i class="ri-file-line text-lg text-red-500"></i></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate" x-text="proof.name"></p>
                                        <p x-show="proof.oversize" class="text-xs text-red-600 dark:text-red-400 font-semibold">Melebihi 5MB</p>
                                    </div>
                                    <button type="button" @click="clearProof()" class="text-slate-400 hover:text-red-500 transition shrink-0" aria-label="Hapus bukti terpilih">
                                        <i class="ri-close-circle-line text-lg"></i>
                                    </button>
                                </div>
                                @error('proof_file') <p class="text-red-500 text-xs mt-1.5" aria-live="polite">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="notes" class="field-label">Catatan <span class="font-normal">(opsional)</span></label>
                                <textarea id="notes" name="notes" rows="2" maxlength="2000"
                                          class="field-input resize-none" placeholder="Contoh: Saya sudah transfer, mohon dicek..."></textarea>
                                @error('notes') <p class="text-red-500 text-xs mt-1" aria-live="polite">{{ $message }}</p> @enderror
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Nominal mengikuti harga booking di server. Booking belum dikonfirmasi sampai pemilik kos memverifikasi pembayaran.
                            </p>
                            <div class="flex justify-end">
                                <button type="submit" x-bind:disabled="submitting" class="btn-primary">
                                    <i class="ri-upload-2-line"></i> {{ $paymentStatus === 'rejected' ? 'Bayar Ulang' : 'Bayar Sekarang' }}
                                </button>
                            </div>
                        </form>
                    @elseif($paymentStatus === 'pending')
                        <div class="rounded-xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] px-4 py-3">
                            <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Menunggu Verifikasi</p>
                            <p class="text-sm text-amber-800 dark:text-amber-300/90">Pembayaran sudah dikirim. Pemilik kos perlu memverifikasi terlebih dahulu.</p>
                        </div>
                    @elseif($paymentStatus === 'approved')
                        <div class="rounded-xl border border-green-100 dark:border-green-500/20 bg-green-50/70 dark:bg-green-500/[0.06] px-4 py-3">
                            <p class="text-sm font-bold text-green-800 dark:text-green-300">Pembayaran Terverifikasi</p>
                            <p class="text-sm text-green-800 dark:text-green-300/90">Menunggu konfirmasi pemilik kos. Booking belum dikonfirmasi.</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Aksi Pengajuan Pembatalan --}}
            @if($booking->isCancellable())
                <div class="card-surface p-6 sm:p-8">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                        Mengajukan pembatalan <strong>tidak langsung membatalkan booking</strong>. Pemilik kos perlu menyetujui terlebih dahulu.
                    </p>
                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <x-confirm-dialog
                            title="Ajukan pembatalan booking?"
                            description="Pengajuan booking &ldquo;{{ $booking->booking_code }}&rdquo; akan dikirim ke pemilik kos. Booking tetap berstatus {{ \StatusLabels::bookingLabel($booking->status) }} sampai disetujui."
                            confirmText="Kirim Pengajuan"
                            triggerClass="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 rounded-xl hover:bg-red-100 dark:hover:bg-red-500/20 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500"
                            aria-label="Ajukan pembatalan booking {{ $booking->booking_code }}"
                        >
                            <x-slot name="slot"><i class="ri-close-circle-line"></i> Ajukan Pembatalan</x-slot>
                            <x-slot name="actions">
                                <form method="POST" action="{{ route('tenant.booking.cancel', $booking) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                    @csrf
                                    <input type="hidden" name="reason" value="Diajukan oleh tenant melalui halaman booking.">
                                    <button type="submit" :disabled="submitting"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition shadow-sm shadow-red-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span x-show="!submitting">Ya, Ajukan</span>
                                        <span x-show="submitting" x-cloak>Mengirim...</span>
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
