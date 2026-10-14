<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail Pembayaran" description="Status verifikasi &amp; bukti pembayaran Anda.">
            <x-button href="{{ route('tenant.pembayaran.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    @php
        $isRejected = $pembayaran->verification_status === 'rejected';
        $canRepay = $isRejected && $pembayaran->tagihan && in_array($pembayaran->tagihan->status, ['unpaid', 'overdue']);
    @endphp

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Pembayaran', 'url' => route('tenant.pembayaran.index')],
            ['label' => 'Detail'],
        ]" />
        <x-alert />

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            {{-- Informasi pembayaran --}}
            <div class="card-surface overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-white/[0.08] flex items-center justify-between gap-3">
                    <h3 class="flex items-center gap-2 font-bold text-slate-900 dark:text-slate-50"><i class="ri-bank-card-line text-primary-500"></i> Informasi Pembayaran</h3>
                    <x-status-badge :status="$pembayaran->verification_status" context="verification" />
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <dl class="divide-y divide-slate-100 dark:divide-white/[0.06] -mx-1">
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-label">No. Pembayaran</dt>
                            <dd class="font-mono font-semibold text-slate-900 dark:text-slate-50">{{ $pembayaran->payment_number }}</dd>
                        </div>
                        @if($pembayaran->isBookingPayment() && $pembayaran->booking)
                            <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                                <dt class="text-label">Kode Booking</dt>
                                <dd><a href="{{ route('tenant.booking.show', $pembayaran->booking) }}" class="font-mono font-semibold text-primary-600 dark:text-primary-300 hover:underline">{{ $pembayaran->booking->booking_code }}</a></dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                                <dt class="text-label">Periode Sewa</dt>
                                <dd class="text-right whitespace-nowrap text-slate-700 dark:text-slate-200">{{ $pembayaran->booking->start_date?->translatedFormat('d M Y') ?? '-' }} – {{ $pembayaran->booking->end_date?->translatedFormat('d M Y') ?? '-' }}</dd>
                            </div>
                        @else
                            @if($pembayaran->tagihan)
                                <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                                    <dt class="text-label">Tagihan</dt>
                                    <dd><a href="{{ route('tenant.tagihan.show', $pembayaran->tagihan) }}" class="font-mono font-semibold text-primary-600 dark:text-primary-300 hover:underline">{{ $pembayaran->tagihan->bill_number }}</a></dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                                    <dt class="text-label">Periode Tagihan</dt>
                                    <dd class="text-right whitespace-nowrap text-slate-700 dark:text-slate-200">{{ $pembayaran->tagihan->period_start?->translatedFormat('d M Y') ?? '-' }} – {{ $pembayaran->tagihan->period_end?->translatedFormat('d M Y') ?? '-' }}</dd>
                                </div>
                            @endif
                        @endif
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-label">Nominal</dt>
                            <dd class="font-semibold text-slate-900 dark:text-slate-50 whitespace-nowrap">Rp {{ number_format($pembayaran->amount, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-label">Metode</dt>
                            <dd class="text-slate-700 dark:text-slate-200">{{ \PaymentLabels::paymentMethod($pembayaran->payment_method) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-label">Tanggal Bayar</dt>
                            <dd class="text-slate-700 dark:text-slate-200">{{ $pembayaran->payment_date?->translatedFormat('d M Y') ?? '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-label">Dikirim</dt>
                            <dd class="text-slate-700 dark:text-slate-200">{{ $pembayaran->created_at->translatedFormat('d M Y H:i') }}</dd>
                        </div>
                    </dl>

                    @if($isRejected && $pembayaran->admin_notes)
                        <div class="p-4 rounded-xl bg-red-50/70 dark:bg-red-500/[0.07] border border-red-100 dark:border-red-500/20">
                            <p class="text-label text-red-600 dark:text-red-300 mb-1.5"><i class="ri-error-warning-line mr-1"></i>Alasan Penolakan</p>
                            <p class="text-sm text-red-700 dark:text-red-200 leading-relaxed whitespace-pre-line">{{ $pembayaran->admin_notes }}</p>
                        </div>
                    @endif
                    {{-- Gateway online (verifikasi otomatis) --}}
                    @if($pembayaran->isFromGateway() && $pembayaran->verification_status === 'pending' && $pembayaran->gateway_instructions)
                        <div class="p-4 rounded-xl bg-primary-50/70 dark:bg-primary-500/10 border border-primary-100 dark:border-primary-500/20">
                            <p class="text-label text-primary-700 dark:text-primary-300 mb-1.5"><i class="ri-qr-code-line mr-1"></i>Instruksi Pembayaran Online</p>
                            <p class="text-sm text-slate-700 dark:text-slate-200 leading-relaxed whitespace-pre-line">{{ $pembayaran->gateway_instructions }}</p>
                            @if($pembayaran->gateway_expires_at)
                                <p class="mt-2 text-meta flex items-center gap-1">
                                    <i class="ri-time-line"></i> Berlaku hingga {{ $pembayaran->gateway_expires_at->translatedFormat('d M Y H:i') }} WIB
                                </p>
                            @endif
                            <p class="mt-2 text-meta flex items-center gap-1">
                                <i class="ri-checkbox-circle-line"></i> Setelah Anda membayar, sistem akan <strong>memverifikasi otomatis</strong>.
                            </p>
                        </div>
                    @elseif($pembayaran->isFromGateway() && $pembayaran->verification_status === 'pending')
                        <div class="p-4 rounded-xl bg-yellow-50/70 dark:bg-yellow-500/[0.06] border border-yellow-100 dark:border-yellow-500/20 text-sm text-yellow-800 dark:text-yellow-300 flex items-start gap-2">
                            <i class="ri-time-line mt-0.5 shrink-0"></i>
                            <div>
                                <p class="font-semibold">Pembayaran Online Menunggu</p>
                                <p class="mt-0.5 text-xs opacity-80">Selesaikan pembayaran untuk diverifikasi otomatis.</p>
                            </div>
                        </div>
                    @elseif($pembayaran->verification_status === 'pending')
                        <div class="p-4 rounded-xl bg-yellow-50/70 dark:bg-yellow-500/[0.06] border border-yellow-100 dark:border-yellow-500/20 text-sm text-yellow-800 dark:text-yellow-300 flex items-start gap-2">
                            <i class="ri-time-line mt-0.5 shrink-0"></i>
                            <div>
                                <p class="font-semibold">Bukti Pembayaran Terkirim</p>
                                <p class="mt-0.5 text-xs opacity-80">Pembayaran kamu sedang diperiksa oleh pengelola kos.</p>
                            </div>
                        </div>
                    @elseif($pembayaran->verification_status === 'approved')
                        <div class="p-4 rounded-xl bg-green-50/70 dark:bg-green-500/[0.06] border border-green-100 dark:border-green-500/20 text-sm text-green-800 dark:text-green-300 flex items-start gap-2">
                            <i class="ri-checkbox-circle-line mt-0.5"></i>
                            <span>
                                Pembayaran telah <strong>Terverifikasi</strong>
                                @if($pembayaran->isFromGateway())
                                    secara otomatis
                                @endif
                                pada {{ $pembayaran->verified_at?->translatedFormat('d M Y H:i') ?? '-' }}.
                            </span>
                        </div>
                        <a href="{{ route('pembayaran.receipt', $pembayaran) }}"
                           target="_blank"
                           class="btn-primary w-full bg-emerald-600 hover:bg-emerald-700">
                            <i class="ri-file-pdf-line"></i> Unduh Kuitansi
                        </a>
                    @endif

                    @if($canRepay && $pembayaran->tagihan)
                        <a href="{{ route('tenant.tagihan.show', $pembayaran->tagihan) }}"
                           class="btn-primary w-full">
                            <i class="ri-refresh-line"></i> Bayar Kembali
                        </a>
                    @endif
                </div>
            </div>

            {{-- Bukti pembayaran --}}
            <div class="card-surface overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-white/[0.08]">
                    <h3 class="flex items-center gap-2 font-bold text-slate-900 dark:text-slate-50"><i class="ri-attachment-2 text-primary-500"></i> Bukti Pembayaran</h3>
                </div>
                <div class="p-6 flex flex-col flex-1">
                    @if($pembayaran->proof_file && \Storage::disk('private')->exists($pembayaran->proof_file))
                        @php $ext = strtolower(pathinfo($pembayaran->proof_file, PATHINFO_EXTENSION)); @endphp
                        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                            <a href="{{ route('pembayaran.proof', $pembayaran) }}" target="_blank" class="block group">
                                <img src="{{ route('pembayaran.proof', $pembayaran) }}" alt="Bukti pembayaran"
                                     class="w-full max-h-[420px] object-contain rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white group-hover:opacity-90 transition-opacity">
                            </a>
                        @elseif($ext === 'pdf')
                            <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-white/[0.08] section-tint">
                                <iframe src="{{ route('pembayaran.proof', $pembayaran) }}#toolbar=0&navpanes=0"
                                        class="w-full h-96" title="Pratinjau bukti pembayaran PDF"></iframe>
                            </div>
                            <p class="mt-2 text-meta flex items-center gap-1"><i class="ri-file-pdf-line"></i> Pratinjau PDF — jika tidak tampil, gunakan tombol unduh di bawah.</p>
                        @else
                            <div class="flex-1 flex flex-col items-center justify-center gap-3 p-10 text-center rounded-xl border border-dashed border-slate-200 dark:border-white/[0.08]">
                                <i class="ri-file-pdf-line text-4xl text-red-500"></i>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Bukti berupa berkas PDF.</p>
                            </div>
                        @endif
                        <a href="{{ route('pembayaran.proof', $pembayaran) }}" target="_blank"
                           class="btn-secondary mt-4 w-full">
                            <i class="ri-download-2-line"></i> Buka / Unduh Bukti
                        </a>
                    @else
                        <div class="flex-1 flex flex-col items-center justify-center gap-2 p-10 text-center rounded-xl border border-dashed border-slate-200 dark:border-white/[0.08]">
                            <i class="ri-image-off-line text-3xl text-slate-300 dark:text-slate-600"></i>
                            <p class="text-sm text-slate-400 dark:text-slate-500">Tidak ada bukti pembayaran.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
