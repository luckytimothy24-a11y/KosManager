<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail Pembayaran" description="Status verifikasi &amp; bukti pembayaran Anda.">
            <x-button href="{{ route('tenant.pembayaran.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    @php
        $isRejected = $pembayaran->verification_status === 'rejected';
        $canRepay = $isRejected && in_array($pembayaran->tagihan->status, ['unpaid', 'overdue']);
    @endphp

    <div class="space-y-6">
        <x-alert />

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            {{-- Informasi pembayaran --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                    <h3 class="flex items-center gap-2 font-bold text-slate-900 dark:text-white"><i class="ri-bank-card-line text-primary-500"></i> Informasi Pembayaran</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \PaymentLabels::verificationBadge($pembayaran->verification_status) }}">{{ \PaymentLabels::verificationLabel($pembayaran->verification_status) }}</span>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <dl class="divide-y divide-slate-100 dark:divide-slate-800 -mx-1">
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">No. Pembayaran</dt>
                            <dd class="font-mono font-semibold text-slate-900 dark:text-white">{{ $pembayaran->payment_number }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">Tagihan</dt>
                            <dd><a href="{{ route('tenant.tagihan.show', $pembayaran->tagihan) }}" class="font-mono font-semibold text-primary-600 dark:text-primary-300 hover:underline">{{ $pembayaran->tagihan->bill_number }}</a></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">Periode Tagihan</dt>
                            <dd class="text-right whitespace-nowrap text-slate-700 dark:text-slate-200">{{ $pembayaran->tagihan->period_start->translatedFormat('d M Y') }} – {{ $pembayaran->tagihan->period_end->translatedFormat('d M Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">Nominal</dt>
                            <dd class="font-bold text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($pembayaran->amount, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">Metode</dt>
                            <dd class="text-slate-700 dark:text-slate-200">{{ \PaymentLabels::paymentMethod($pembayaran->payment_method) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">Tanggal Bayar</dt>
                            <dd class="text-slate-700 dark:text-slate-200">{{ $pembayaran->payment_date->translatedFormat('d M Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">Dikirim</dt>
                            <dd class="text-slate-700 dark:text-slate-200">{{ $pembayaran->created_at->translatedFormat('d M Y H:i') }}</dd>
                        </div>
                    </dl>

                    @if($isRejected && $pembayaran->admin_notes)
                        <div class="p-4 rounded-xl bg-red-50/70 dark:bg-red-500/[0.07] border border-red-100 dark:border-red-500/20">
                            <p class="text-xs font-bold uppercase tracking-wider text-red-600 dark:text-red-300 mb-1.5"><i class="ri-error-warning-line mr-1"></i>Alasan Penolakan</p>
                            <p class="text-sm text-red-700 dark:text-red-200 leading-relaxed whitespace-pre-line">{{ $pembayaran->admin_notes }}</p>
                        </div>
                    @elseif($pembayaran->verification_status === 'pending')
                        <div class="p-4 rounded-xl bg-yellow-50/70 dark:bg-yellow-500/[0.06] border border-yellow-100 dark:border-yellow-500/20 text-sm text-yellow-800 dark:text-yellow-300 flex items-start gap-2">
                            <i class="ri-time-line mt-0.5"></i>
                            <span>Pembayaran sedang <strong>diperiksa pengelola</strong>. Mohon tunggu konfirmasi (maksimal 1&times;24 jam).</span>
                        </div>
                    @elseif($pembayaran->verification_status === 'approved')
                        <div class="p-4 rounded-xl bg-green-50/70 dark:bg-green-500/[0.06] border border-green-100 dark:border-green-500/20 text-sm text-green-800 dark:text-green-300 flex items-start gap-2">
                            <i class="ri-checkbox-circle-line mt-0.5"></i>
                            <span>Pembayaran telah <strong>Terverifikasi</strong> oleh pengelola pada {{ $pembayaran->verified_at?->translatedFormat('d M Y H:i') ?? '-' }}.</span>
                        </div>
                    @endif

                    @if($canRepay)
                        <a href="{{ route('tenant.tagihan.show', $pembayaran->tagihan) }}"
                           class="inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition shadow-sm shadow-primary-500/30">
                            <i class="ri-refresh-line"></i> Bayar Kembali
                        </a>
                    @endif
                </div>
            </div>

            {{-- Bukti pembayaran --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="flex items-center gap-2 font-bold text-slate-900 dark:text-white"><i class="ri-attachment-2 text-primary-500"></i> Bukti Pembayaran</h3>
                </div>
                <div class="p-6 flex flex-col flex-1">
                    @if($pembayaran->proof_file && \Storage::exists($pembayaran->proof_file))
                        @php $ext = strtolower(pathinfo($pembayaran->proof_file, PATHINFO_EXTENSION)); @endphp
                        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                            <a href="{{ route('pembayaran.proof', $pembayaran) }}" target="_blank" class="block group">
                                <img src="{{ route('pembayaran.proof', $pembayaran) }}" alt="Bukti pembayaran"
                                     class="w-full max-h-[420px] object-contain rounded-xl border border-slate-200 dark:border-slate-700 bg-white group-hover:opacity-90 transition-opacity">
                            </a>
                        @else
                            <div class="flex-1 flex flex-col items-center justify-center gap-3 p-10 text-center rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                                <i class="ri-file-pdf-line text-4xl text-red-500"></i>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Bukti berupa berkas PDF.</p>
                            </div>
                        @endif
                        <a href="{{ route('pembayaran.proof', $pembayaran) }}" target="_blank"
                           class="mt-4 inline-flex justify-center items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-medium border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            <i class="ri-download-2-line"></i> Buka / Unduh Bukti
                        </a>
                    @else
                        <div class="flex-1 flex flex-col items-center justify-center gap-2 p-10 text-center rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                            <i class="ri-image-off-line text-3xl text-slate-300 dark:text-slate-600"></i>
                            <p class="text-sm text-slate-400 dark:text-slate-500">Tidak ada bukti pembayaran.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
