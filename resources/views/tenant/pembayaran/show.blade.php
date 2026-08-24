<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail Pembayaran" description="Status verifikasi pembayaran Anda." />
    </x-slot>
    <x-alert />

    @php $isRejected = $pembayaran->verification_status === 'rejected'; $canRepay = $isRejected && in_array($pembayaran->tagihan->status, ['unpaid', 'overdue']); @endphp
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Informasi Pembayaran</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \PaymentLabels::verificationBadge($pembayaran->verification_status) }}">{{ \PaymentLabels::verificationLabel($pembayaran->verification_status) }}</span>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">No. Pembayaran</span><span class="font-mono">{{ $pembayaran->payment_number }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Tagihan</span><a href="{{ route('tenant.tagihan.show', $pembayaran->tagihan) }}" class="font-mono text-blue-600 hover:text-blue-800">{{ $pembayaran->tagihan->bill_number }}</a></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Periode Tagihan</span><span>{{ $pembayaran->tagihan->period_start->translatedFormat('d M Y') }} – {{ $pembayaran->tagihan->period_end->translatedFormat('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Nominal</span><span class="font-bold">Rp {{ number_format($pembayaran->amount, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Metode</span><span>{{ \PaymentLabels::paymentMethod($pembayaran->payment_method) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Tanggal Bayar</span><span>{{ $pembayaran->payment_date->translatedFormat('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Dikirim</span><span>{{ $pembayaran->created_at->translatedFormat('d M Y H:i') }}</span></div>
            </div>

            {{-- Alasan penolakan --}}
            @if($isRejected && $pembayaran->admin_notes)
                <div class="mt-6 p-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20">
                    <p class="text-xs font-bold uppercase tracking-wider text-red-600 dark:text-red-300 mb-1.5"><i class="ri-error-warning-line mr-1"></i>Alasan Penolakan</p>
                    <p class="text-sm text-red-700 dark:text-red-200 whitespace-pre-line">{{ $pembayaran->admin_notes }}</p>
                </div>
            @elseif($pembayaran->verification_status === 'pending')
                <div class="mt-6 p-4 rounded-lg bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-100 dark:border-yellow-500/20 text-sm text-yellow-800 dark:text-yellow-300">
                    <i class="ri-time-line mr-1"></i> Pembayaran sedang <strong>diperiksa pengelola</strong>. Mohon tunggu konfirmasi.
                </div>
            @elseif($pembayaran->verification_status === 'approved')
                <div class="mt-6 p-4 rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-100 dark:border-green-500/20 text-sm text-green-800 dark:text-green-300">
                    <i class="ri-checkbox-circle-line mr-1"></i> Pembayaran telah <strong>Terverifikasi</strong> oleh pengelola pada {{ $pembayaran->verified_at?->translatedFormat('d M Y H:i') ?? '-' }}.
                </div>
            @endif

            @if($canRepay)
                <a href="{{ route('tenant.tagihan.show', $pembayaran->tagihan) }}"
                   class="mt-6 inline-flex w-full items-center justify-center px-4 py-2.5 text-sm font-bold text-white bg-primary-500 rounded-lg hover:bg-primary-600 active:bg-primary-700 transition shadow-sm shadow-primary-500/30">
                    <i class="ri-refresh-line mr-1.5"></i> Bayar Kembali
                </a>
            @endif
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6 flex flex-col">
            <h3 class="text-lg font-semibold mb-4">Bukti Pembayaran</h3>
            @if($pembayaran->proof_file && \Storage::exists($pembayaran->proof_file))
                @php $ext = strtolower(pathinfo($pembayaran->proof_file, PATHINFO_EXTENSION)); @endphp
                @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                    <img src="{{ route('pembayaran.proof', $pembayaran) }}" alt="Bukti pembayaran"
                         class="w-full max-h-[420px] object-contain rounded-lg border border-gray-200 dark:border-slate-700 bg-white">
                @else
                    <div class="flex-1 flex flex-col items-center justify-center gap-3 p-8 text-center rounded-lg border border-dashed border-gray-300 dark:border-slate-600">
                        <i class="ri-file-pdf-line text-4xl text-red-500"></i>
                        <p class="text-sm text-gray-500 dark:text-slate-400">Bukti berupa berkas PDF.</p>
                    </div>
                @endif
                <a href="{{ route('pembayaran.proof', $pembayaran) }}" target="_blank"
                   class="mt-3 inline-flex justify-center px-4 py-2 text-sm font-medium border border-gray-200 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800">
                    <i class="ri-download-2-line mr-1.5"></i> Buka / Unduh Bukti
                </a>
            @else
                <div class="flex-1 flex flex-col items-center justify-center gap-2 p-8 text-center rounded-lg border border-dashed border-gray-300 dark:border-slate-600">
                    <i class="ri-image-off-line text-3xl text-gray-300 dark:text-slate-600"></i>
                    <p class="text-sm text-gray-400 dark:text-slate-500">Tidak ada bukti pembayaran.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
