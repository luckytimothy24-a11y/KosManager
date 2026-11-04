@php
    $dpPayment = $booking->activeBookingPayment;
    $dpStatus = $dpPayment?->verification_status;
@endphp

@if($booking->usesDownPayment())
    <div class="mt-3 rounded-xl border border-slate-200/70 dark:border-white/[0.08] bg-slate-50/70 dark:bg-surface-elevated/50 px-4 py-3 text-sm">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">DP Booking</p>
                <p class="mt-0.5 font-bold text-slate-900 dark:text-slate-50">Rp {{ number_format($booking->expectedBookingPaymentAmount(), 0, ',', '.') }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</p>
                <span class="mt-1 inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                    {{ $dpStatus === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300' : ($dpStatus === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-white/[0.06] dark:text-slate-300') }}">
                    {{ $dpPayment ? \PaymentLabels::verificationLabel($dpPayment->verification_status) : 'Belum dibayar' }}
                </span>
            </div>
        </div>
        @if($dpPayment)
            <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">
                Metode: {{ \PaymentLabels::paymentMethod($dpPayment->payment_method) }}
                · Nominal: Rp {{ number_format((float) $dpPayment->amount, 0, ',', '.') }}
            </p>
            <a href="{{ route($prefix.'.pembayaran.show', $dpPayment) }}"
               class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700">
                <i class="ri-eye-line"></i> Lihat / verifikasi pembayaran DP
            </a>
        @endif
        <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">DP dipotongkan dari tagihan sewa pertama dan non-refundable jika booking ditolak/dibatalkan.</p>
    </div>
@endif
