<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route($prefix.'.booking.index'); @endphp
    <x-slot name="header">
        <x-page-header title="Booking {{ $booking->booking_code }}" description="Detail booking dan proses check-in.">
            <x-button href="{{ $backUrl }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="max-w-3xl space-y-6">
            {{-- Ringkasan status --}}
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kode Booking</p>
                        <p class="mt-1 text-xl font-bold font-mono text-slate-900 dark:text-slate-50">{{ $booking->booking_code }}</p>
                    </div>
                    <x-status-badge :status="$booking->status" context="booking" />
                </div>
            </div>

            {{-- Detail penyewa & kamar --}}
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 px-6 py-4 border-b border-slate-100 dark:border-white/10">
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
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tanggal Booking</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}</dd>
                        </div>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 px-6 py-4 border-b border-slate-100 dark:border-white/10">
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
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/10">
                    <i class="ri-money-dollar-circle-line text-primary-500"></i> Rincian Biaya
                </h3>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Total harga sewa ({{ \StatusLabels::rentalTypeLabel($booking->rental_type) }})</span>
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($booking->price, 0, ',', '.') }}</span>
                </div>
                @if($booking->usesDownPayment())
                    <div class="mt-3 flex items-center justify-between rounded-xl bg-primary-50/70 dark:bg-primary-500/[0.06] border border-primary-100 dark:border-primary-500/20 px-3 py-2">
                        <span class="text-xs font-semibold text-primary-800 dark:text-primary-300">DP booking (dibayar tenant)</span>
                        <span class="text-sm font-bold text-primary-700 dark:text-primary-300">Rp {{ number_format($booking->expectedBookingPaymentAmount(), 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            @if($booking->notes)
                <div class="rounded-2xl border border-primary-100 dark:border-primary-500/20 p-6 bg-primary-50/50 dark:bg-primary-500/[0.06]">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-primary-900 dark:text-primary-200">
                        <i class="ri-chat-quote-line"></i> Catatan Penyewa
                    </h3>
                    <p class="mt-2.5 text-sm text-primary-800/90 dark:text-primary-300/90 leading-relaxed whitespace-pre-line">{{ $booking->notes }}</p>
                </div>
            @endif

            @if($booking->status === 'approved')
                <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/10">
                        <i class="ri-user-shield-line text-primary-500"></i> Status Pengajuan Check-In
                    </h3>
                    @php $identityStatus = $booking->identityStatus(); @endphp
                    @if($identityStatus === 'confirmed')
                        <div class="flex items-start gap-3 rounded-2xl border border-green-100 dark:border-green-500/20 bg-green-50/70 dark:bg-green-500/[0.06] px-4 py-4">
                            <span class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-500/15 flex items-center justify-center shrink-0">
                                <i class="ri-check-line text-lg text-green-600 dark:text-green-400"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-green-800 dark:text-green-300">✓ Check-In Dikonfirmasi</p>
                                <p class="mt-1 text-sm text-green-700/90 dark:text-green-300/90">Anda telah mengonfirmasi pengajuan check-in tenant. Lanjutkan ke proses check-in final di menu Check-In.</p>
                                <p class="mt-2 text-xs text-green-600/80 dark:text-green-400/80 flex items-center gap-1.5">
                                    <i class="ri-time-line"></i> Dikonfirmasi: {{ $booking->identity_confirmed_at->translatedFormat('d F Y, H:i') }}
                                </p>
                                <a href="{{ route("$prefix.checkin.index") }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-green-700 dark:text-green-300 hover:text-green-800 transition"><i class="ri-login-box-line"></i> Buka Check-In →</a>
                            </div>
                        </div>
                    @elseif($identityStatus === 'pending')
                        <div class="flex items-start gap-3 rounded-2xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] px-4 py-4">
                            <span class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-500/15 flex items-center justify-center shrink-0">
                                <i class="ri-time-line text-lg text-amber-600 dark:text-amber-400"></i>
                            </span>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Menunggu Konfirmasi Owner</p>
                                <p class="mt-1 text-sm text-amber-700/90 dark:text-amber-300/90">Tenant telah mengirim data — WA: {{ $booking->phone }}, Alamat: {{ $booking->address }}. Silakan periksa di menu Check-In dan konfirmasi atau tolak.</p>
                                <a href="{{ route("$prefix.checkin.index") }}" class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 dark:text-amber-300 hover:text-amber-800 transition"><i class="ri-shield-check-line"></i> Review di Check-In →</a>
                            </div>
                        </div>
                    @elseif($identityStatus === 'rejected')
                        <div class="flex items-start gap-3 rounded-2xl border border-red-100 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] px-4 py-4">
                            <span class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-500/15 flex items-center justify-center shrink-0">
                                <i class="ri-close-circle-line text-lg text-red-600 dark:text-red-400"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-red-800 dark:text-red-300">Check-In Ditolak</p>
                                <p class="mt-1 text-sm text-red-700/90 dark:text-red-300/90">Alasan: {{ $booking->identity_rejection_reason ?? '-' }}</p>
                                @if($booking->identity_rejected_at)
                                    <p class="mt-1 text-xs text-red-600/70 dark:text-red-400/70">Ditolak: {{ $booking->identity_rejected_at->translatedFormat('d F Y, H:i') }}</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="flex items-start gap-3 rounded-2xl border border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-surface-elevated/40 px-4 py-4">
                            <span class="w-10 h-10 rounded-full bg-slate-100 dark:bg-surface-elevated flex items-center justify-center shrink-0">
                                <i class="ri-information-line text-slate-500"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Belum Mengisi</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tenant belum mengajukan check-in.</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Status pembayaran booking (prerequisite konfirmasi) --}}
            @if($booking->status === 'pending')
                @php
                    $bookingPayment = \App\Models\Pembayaran::where('booking_id', $booking->id)
                        ->whereIn('verification_status', ['pending', 'approved', 'rejected'])
                        ->orderByDesc('id')
                        ->first();
                    $bookingDistribution = \App\Models\PaymentDistribution::where('booking_id', $booking->id)->first();
                    $ownerBank = \App\Models\OwnerBankAccount::where('owner_id', auth()->id())->first();
                @endphp
                <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/10">
                        <i class="ri-wallet-3-line text-primary-500"></i> Status Pembayaran Booking
                    </h3>
                    @if(! $bookingPayment)
                        <div class="rounded-xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] px-4 py-3">
                            <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Belum ada pembayaran</p>
                            <p class="text-sm text-amber-800 dark:text-amber-300/90">Booking belum dapat dikonfirmasi. Tenant harus membayar terlebih dahulu.</p>
                        </div>
                    @else
                        <div class="rounded-xl border border-slate-100 dark:border-white/10 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</p>
                                    <p class="mt-1 text-sm font-bold {{ $bookingPayment->verification_status === 'approved' ? 'text-green-600' : ($bookingPayment->verification_status === 'pending' ? 'text-amber-600' : 'text-red-600') }}">
                                        {{ \PaymentLabels::verificationLabel($bookingPayment->verification_status) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nominal</p>
                                    <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">Rp {{ number_format((float) $bookingPayment->amount, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Metode</p>
                                    <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ \PaymentLabels::paymentMethod($bookingPayment->payment_method) }}</p>
                                </div>
                            </div>
                            @if($bookingPayment->verification_status === 'pending')
                                <div class="mt-4 flex justify-end">
                                    <a href="{{ route(($prefix ?? 'owner').'.pembayaran.show', $bookingPayment) }}"
                                       class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-primary-600 dark:text-primary-400 border border-primary-200 dark:border-primary-500/30 rounded-xl hover:bg-primary-50 dark:hover:bg-primary-500/10 transition">
                                        <i class="ri-eye-line"></i> Review Pembayaran
                                    </a>
                                </div>
                            @elseif($bookingPayment->verification_status === 'approved')
                                <div class="mt-3 rounded-xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/70 dark:bg-blue-500/[0.06] px-4 py-3">
                                    <p class="text-sm font-bold text-blue-800 dark:text-blue-300">Pembayaran terverifikasi</p>
                                    <p class="text-sm text-blue-800 dark:text-blue-300/90">Booking dapat dikonfirmasi. Klik Approve Booking di bawah.</p>
                                    @if($bookingDistribution)
                                        <p class="mt-2 text-xs text-blue-700/80 dark:text-blue-300/80">
                                            Distribusi: <strong>{{ $bookingDistribution->status === 'pending' ? 'Menunggu konfirmasi booking' : 'Siap pencairan' }}</strong>
                                            · Owner: Rp {{ number_format((float) $bookingDistribution->owner_amount, 0, ',', '.') }}
                                            @if($ownerBank)
                                                · {{ $ownerBank->bank_name }} {{ $ownerBank->maskedAccountNumber() }}
                                            @else
                                                · <a href="{{ route(($prefix ?? 'owner').'.bank-account.edit') }}" class="underline">Atur rekening</a>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            {{-- Cancellation request dari tenant --}}
            @if(!empty($cancellationRequest))
                <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/10">
                        <i class="ri-forbid-line text-amber-500"></i> Pengajuan Pembatalan Tenant
                    </h3>
                    <div class="rounded-xl border border-slate-100 dark:border-white/10 p-4 mb-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status Pengajuan</p>
                                <p class="mt-1 text-sm font-bold {{ $cancellationRequest->status === 'pending' ? 'text-amber-600' : ($cancellationRequest->status === 'approved' ? 'text-green-600' : 'text-red-600') }}">
                                    {{ $cancellationRequest->status === 'pending' ? 'Menunggu Review' : ($cancellationRequest->status === 'approved' ? 'Disetujui' : 'Ditolak') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status Booking</p>
                                <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ \StatusLabels::bookingLabel($booking->status) }}</p>
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                            Alasan: {{ $cancellationRequest->reason ?? '-' }}
                        </p>
                        @if($cancellationRequest->rejection_reason)
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">Alasan penolakan: {{ $cancellationRequest->rejection_reason }}</p>
                        @endif
                    </div>
                    @can('approve', $cancellationRequest)
                        @if($cancellationRequest->isPending())
                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <form method="POST" action="{{ route($prefix.'.booking.cancellation.reject', $cancellationRequest) }}" class="contents">
                                    @csrf
                                    <input type="hidden" name="reason" value="Pengajuan pembatalan ditolak oleh pengelola kos.">
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition">
                                        <i class="ri-close-circle-line"></i> Tolak Pembatalan
                                    </button>
                                </form>
                                <form method="POST" action="{{ route($prefix.'.booking.cancellation.approve', $cancellationRequest) }}" class="contents">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-red-500 hover:bg-red-600 active:bg-red-700 rounded-xl transition shadow-sm">
                                        <i class="ri-check-line"></i> Setujui Pembatalan
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endcan
                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                        Saat pengajuan pending, booking &amp; reservasi kamar <strong>tidak berubah</strong>. Booking hanya dibatalkan setelah Anda menyetujui.
                    </p>
                </div>
            @endif

            {{-- Aksi booking status --}}
            @can('approve', $booking)
                @if($booking->status === 'pending')
                    <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8" x-data="{ rejecting: false }">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/10">
                            <i class="ri-task-line text-primary-500"></i> Keputusan Booking
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                            Booking ini masih <strong>pending</strong>. Konfirmasi hanya akan mengubah status booking menjadi approved — <strong>tenant belum dianggap aktif</strong> sampai proses check-in selesai.
                        </p>
                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <form method="POST" action="{{ route("$prefix.booking.reject", $booking) }}" class="contents" x-show="rejecting" x-cloak>
                                @csrf
                                <input type="hidden" name="reason" value="Booking ditolak oleh pengelola kos.">
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-red-500 hover:bg-red-600 active:bg-red-700 rounded-xl transition shadow-sm">
                                    <i class="ri-close-circle-line"></i> Konfirmasi Tolak
                                </button>
                            </form>
                            <button type="button" x-show="!rejecting" x-on:click="rejecting = true"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition">
                                <i class="ri-close-circle-line"></i> Tolak Booking
                            </button>
                            <form method="POST" action="{{ route("$prefix.booking.approve", $booking) }}" class="contents">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 rounded-xl transition shadow-sm shadow-primary-500/30">
                                    <i class="ri-check-line"></i> Approve Booking
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endcan

            {{-- Aksi check-in --}}
            @can('update', $booking)
                @if($booking->status === 'approved')
                    <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8" x-data="{ submitting: false }">
                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <a href="{{ route("$prefix.checkin.index") }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 rounded-xl transition shadow-sm shadow-primary-500/30">
                                <i class="ri-login-box-line"></i> Proses Check-In
                            </a>
                        </div>
                    </div>
                @endif
            @endcan
        </div>
    </div>
</x-app-layout>
