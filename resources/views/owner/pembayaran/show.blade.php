<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route($prefix.'.pembayaran.index'); @endphp
    <x-slot name="header">
        <x-page-header title="Pembayaran {{ $pembayaran->payment_number }}" description="Verifikasi bukti pembayaran penghuni.">
            <div class="flex items-center gap-2">
                <x-button href="{{ $backUrl }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
                <a href="{{ route($prefix.'.tagihan.show', $pembayaran->tagihan) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i class="ri-file-list-3-line"></i> Lihat Tagihan
                </a>
            </div>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Ringkasan --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 flex flex-wrap items-center justify-between gap-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nominal Pembayaran</p>
                <p class="mt-1 text-3xl font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">Rp {{ number_format($pembayaran->amount, 0, ',', '.') }}</p>
                <p class="mt-1.5 flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                    <i class="{{ ['transfer_bank' => 'ri-bank-line', 'cash' => 'ri-cash-line', 'e_wallet' => 'ri-smartphone-line'][$pembayaran->payment_method] ?? 'ri-wallet-3-line' }}"></i>
                    {{ \PaymentLabels::paymentMethod($pembayaran->payment_method) }} · {{ $pembayaran->payment_date->translatedFormat('d F Y') }}
                </p>
            </div>
            <x-status-badge :status="$pembayaran->verification_status" context="verification" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Informasi pembayaran --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden h-fit">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-money-dollar-circle-line text-primary-500"></i> Informasi Pembayaran
                </h3>
                <dl class="px-6 py-5 divide-y divide-slate-100 dark:divide-slate-800 text-sm [&>div]:flex [&>div]:items-center [&>div]:justify-between [&>div]:gap-6 [&>div]:py-2.5 first:[&>div]:pt-0 last:[&>div]:pb-0">
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">No. Pembayaran</dt><dd class="font-mono font-semibold text-slate-900 dark:text-white">{{ $pembayaran->payment_number }}</dd></div>
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Penghuni</dt><dd class="font-medium text-slate-900 dark:text-white truncate">{{ $pembayaran->penghuni->user->name }}</dd></div>
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Kos / Kamar</dt><dd class="font-medium text-slate-900 dark:text-white truncate">{{ $pembayaran->penghuni->kos->name }} · {{ $pembayaran->penghuni->kamar->room_number }}</dd></div>
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Tagihan</dt>
                        <dd><a href="{{ route($prefix.'.tagihan.show', $pembayaran->tagihan) }}" class="font-mono font-semibold text-primary-600 hover:text-primary-700 dark:hover:text-primary-400 transition">{{ $pembayaran->tagihan->bill_number }}</a></dd>
                    </div>
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Periode Tagihan</dt><dd class="text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ $pembayaran->tagihan->period_start->translatedFormat('d M Y') }} – {{ $pembayaran->tagihan->period_end->translatedFormat('d M Y') }}</dd></div>
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Total Tagihan</dt><dd class="font-medium text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($pembayaran->tagihan->total, 0, ',', '.') }}</dd></div>
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Jatuh Tempo</dt><dd class="text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ $pembayaran->tagihan->due_date->translatedFormat('d M Y') }}</dd></div>
                    @if($pembayaran->verified_at)
                        <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Diverifikasi</dt>
                            <dd class="text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ $pembayaran->verified_at->translatedFormat('d M Y H:i') }}{{ $pembayaran->verifier ? ' oleh '.$pembayaran->verifier->name : '' }}</dd>
                        </div>
                    @endif
                </dl>

                @if($pembayaran->admin_notes)
                    <div class="mx-6 mb-6 p-4 bg-red-50/70 dark:bg-red-500/[0.06] border border-red-100 dark:border-red-500/20 rounded-xl">
                        <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-400 mb-1.5"><i class="ri-chat-quote-line"></i> Alasan Penolakan</p>
                        <p class="text-xs text-red-700 dark:text-red-300/90 leading-relaxed whitespace-pre-line">{{ $pembayaran->admin_notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Bukti + aksi verifikasi --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 flex flex-col">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-attachment-2 text-primary-500"></i> Bukti Pembayaran
                </h3>

                @if($pembayaran->proof_file && \Storage::exists($pembayaran->proof_file))
                    @php $ext = strtolower(pathinfo($pembayaran->proof_file, PATHINFO_EXTENSION)); @endphp
                    @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                        <a href="{{ route('pembayaran.proof', $pembayaran) }}" target="_blank"
                           class="block rounded-xl overflow-hidden border border-slate-100 dark:border-slate-800 bg-white">
                            <img src="{{ route('pembayaran.proof', $pembayaran) }}" alt="Bukti pembayaran" class="w-full max-h-[380px] object-contain bg-white">
                        </a>
                    @else
                        <div class="flex flex-col items-center justify-center gap-3 p-8 text-center rounded-xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40">
                            <span class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-500/10 flex items-center justify-center"><i class="ri-file-pdf-line text-2xl text-red-500"></i></span>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Bukti berupa berkas PDF.</p>
                        </div>
                    @endif
                    <a href="{{ route('pembayaran.proof', $pembayaran) }}" target="_blank"
                       class="mt-4 inline-flex justify-center items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                        <i class="ri-download-2-line"></i> Buka / Unduh Bukti
                    </a>
                @else
                    <div class="flex flex-1 flex-col items-center justify-center gap-2.5 p-8 text-center rounded-xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 min-h-[12rem]">
                        <span class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center"><i class="ri-image-off-line text-xl text-slate-300 dark:text-slate-600"></i></span>
                        <p class="text-sm text-slate-400 dark:text-slate-500">Tidak ada bukti pembayaran<br>(mungkin metode tunai).</p>
                    </div>
                @endif

                @if($pembayaran->verification_status === 'pending')
                    <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-2 gap-3"
                         x-data="{ reason: '', open: false }">
                        <form method="POST" action="{{ route($prefix.'.pembayaran.verify', $pembayaran) }}" x-show="!open"
                              onsubmit="return confirm('Verifikasi pembayaran ini sebagai LUNAS?')" class="sm:col-span-1">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-2.5 text-sm font-bold text-white bg-green-600 rounded-xl hover:bg-green-700 active:bg-green-800 transition shadow-sm shadow-green-600/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500">
                                <i class="ri-check-double-line mr-1"></i> Verifikasi Lunas
                            </button>
                        </form>
                        <button type="button" x-show="!open" @click="open = true"
                                class="w-full px-4 py-2.5 text-sm font-bold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                            <i class="ri-close-circle-line mr-1"></i> Tolak
                        </button>
                        <form method="POST" action="{{ route($prefix.'.pembayaran.reject', $pembayaran) }}" x-show="open" x-cloak class="sm:col-span-2 space-y-3">
                            @csrf
                            <div>
                                <label for="reject-reason" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Alasan Penolakan <span class="text-red-500">*</span></label>
                                <textarea id="reject-reason" name="reason" rows="3" required x-model="reason" placeholder="Wajib diisi — akan dilihat oleh penghuni."
                                          class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="open = false; reason = ''"
                                        class="px-4 py-2 text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</button>
                                <button type="submit"
                                        class="px-4 py-2 text-sm font-bold text-white bg-red-600 rounded-xl hover:bg-red-700 transition shadow-sm shadow-red-600/30">Konfirmasi Penolakan</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
