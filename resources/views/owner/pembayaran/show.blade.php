<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Detail Pembayaran" description="Verifikasi bukti pembayaran penghuni.">
            <x-button href="{{ route($prefix.'.pembayaran.index') }}" type="secondary">Kembali</x-button>
        </x-page-header>
    </x-slot>
    <x-alert />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Informasi Pembayaran</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \PaymentLabels::verificationBadge($pembayaran->verification_status) }}">{{ \PaymentLabels::verificationLabel($pembayaran->verification_status) }}</span>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">No. Pembayaran</span><span class="font-mono">{{ $pembayaran->payment_number }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Penghuni</span><span>{{ $pembayaran->penghuni->user->name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Kos / Kamar</span><span>{{ $pembayaran->penghuni->kos->name }} · {{ $pembayaran->penghuni->kamar->room_number }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Tagihan</span>
                    <a href="{{ route($prefix.'.tagihan.show', $pembayaran->tagihan) }}" class="font-mono text-blue-600 hover:text-blue-800">{{ $pembayaran->tagihan->bill_number }}</a>
                </div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Periode Tagihan</span><span>{{ $pembayaran->tagihan->period_start->translatedFormat('d M Y') }} – {{ $pembayaran->tagihan->period_end->translatedFormat('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Jatuh Tempo</span><span>{{ $pembayaran->tagihan->due_date->translatedFormat('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Nominal Bayar</span><span class="font-bold">Rp {{ number_format($pembayaran->amount, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Total Tagihan</span><span>Rp {{ number_format($pembayaran->tagihan->total, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Metode</span><span>{{ \PaymentLabels::paymentMethod($pembayaran->payment_method) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Tanggal Bayar</span><span>{{ $pembayaran->payment_date->translatedFormat('d M Y') }}</span></div>
                @if($pembayaran->verified_at)
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Diverifikasi</span>
                        <span>{{ $pembayaran->verified_at->translatedFormat('d M Y H:i') }}{{ $pembayaran->verifier ? ' oleh '.$pembayaran->verifier->name : '' }}</span>
                    </div>
                @endif
                @if($pembayaran->admin_notes)
                    <div class="pt-2">
                        <span class="text-gray-500 dark:text-slate-400 block mb-1">Catatan Admin</span>
                        <p class="p-3 bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20 rounded-lg text-xs text-red-700 dark:text-red-300 whitespace-pre-line">{{ $pembayaran->admin_notes }}</p>
                    </div>
                @endif
            </div>
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
                    <p class="text-sm text-gray-400 dark:text-slate-500">Tidak ada bukti pembayaran (mungkin metode tunai).</p>
                </div>
            @endif

            @if($pembayaran->verification_status === 'pending')
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-700 grid grid-cols-2 gap-3"
                     x-data="{ reason: '', open: false }">
                    <form method="POST" action="{{ route($prefix.'.pembayaran.verify', $pembayaran) }}" x-show="!open"
                          onsubmit="return confirm('Verifikasi pembayaran ini sebagai LUNAS?')" class="col-span-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2.5 text-sm font-bold text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-sm">
                            <i class="ri-check-double-line mr-1"></i> Verifikasi Lunas
                        </button>
                    </form>
                    <button type="button" x-show="!open" @click="open = true"
                            class="w-full px-4 py-2.5 text-sm font-bold text-red-600 border border-red-200 dark:border-red-500/30 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                        <i class="ri-close-circle-line mr-1"></i> Tolak
                    </button>
                    <form method="POST" action="{{ route($prefix.'.pembayaran.reject', $pembayaran) }}" x-show="open" x-cloak class="col-span-2 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                            <textarea name="reason" rows="3" required x-model="reason" placeholder="Wajib diisi — akan dilihat oleh penghuni."
                                      class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm"></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="open = false; reason = ''" class="px-4 py-2 text-sm font-medium bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200">Batal</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-red-600 rounded-lg hover:bg-red-700">Konfirmasi Penolakan</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
