<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail Tagihan" description="{{ \PaymentLabels::billType($tagihan->bill_type) }} · {{ $tagihan->period_start->translatedFormat('d M Y') }} — {{ $tagihan->period_end->translatedFormat('d M Y') }}">
            <x-button href="{{ route('tenant.tagihan.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />
        @php $canPay = in_array($tagihan->status, ['unpaid', 'overdue']); @endphp

        {{-- Ringkasan total --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 flex flex-wrap items-center justify-between gap-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 font-mono">{{ $tagihan->bill_number }}</p>
                <p class="mt-1 text-3xl font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">Rp {{ number_format($tagihan->total, 0, ',', '.') }}</p>
                <div class="mt-1.5 flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                    <i class="ri-calendar-deadline-line"></i> Jatuh tempo {{ $tagihan->due_date->translatedFormat('d F Y') }}
                    @if(in_array($tagihan->status, ['unpaid', 'overdue']) && $tagihan->due_date->isPast())
                        · <span class="text-red-500 font-medium">{{ abs($tagihan->due_date->diffInDays(now())) }} hari terlambat</span>
                    @endif
                </div>
            </div>
            <x-status-badge :status="$tagihan->status" context="tagihan" />
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 space-y-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div class="flex items-start gap-2.5 min-w-0">
                    <i class="ri-door-open-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                    <div class="min-w-0">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kamar</dt>
                        <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200 truncate">{{ $tagihan->kamar->room_number }} — {{ $tagihan->kamar->room_name }} ({{ $tagihan->kamar->kos->name }})</dd>
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <i class="ri-price-tag-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tipe Tagihan</dt>
                        <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ \PaymentLabels::billType($tagihan->bill_type) }}</dd>
                    </div>
                </div>
                <div class="flex items-start gap-2.5 sm:col-span-2">
                    <i class="ri-calendar-range-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Periode Sewa</dt>
                        <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $tagihan->period_start->translatedFormat('d F Y') }} — {{ $tagihan->period_end->translatedFormat('d F Y') }}</dd>
                    </div>
                </div>
            </dl>

            {{-- Breakdown --}}
            <div class="rounded-xl border border-slate-100 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                <div class="flex items-center justify-between px-4 py-3">
                    <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                    <span class="font-medium text-slate-900 dark:text-white">Rp {{ number_format($tagihan->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between px-4 py-3">
                    <span class="text-slate-500 dark:text-slate-400">Diskon</span>
                    <span class="font-medium text-green-600 dark:text-green-400">&minus; Rp {{ number_format($tagihan->discount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between px-4 py-3">
                    <span class="text-slate-500 dark:text-slate-400">Denda</span>
                    <span class="font-medium text-red-600 dark:text-red-400">+ Rp {{ number_format($tagihan->penalty, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between px-4 py-3 bg-slate-50/70 dark:bg-slate-800/40 rounded-b-xl">
                    <span class="font-semibold text-slate-900 dark:text-white">Total</span>
                    <span class="font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">Rp {{ number_format($tagihan->total, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Riwayat pembayaran --}}
            @if($tagihan->pembayarans->isNotEmpty())
                <div>
                    <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2.5"><i class="ri-history-line"></i> Riwayat Pembayaran</h4>
                    <ul class="space-y-2">
                        @foreach($tagihan->pembayarans->sortByDesc('created_at') as $p)
                            <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl text-sm">
                                <div class="min-w-0 flex flex-wrap items-center gap-x-2">
                                    <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $p->payment_number }}</span>
                                    <span class="text-slate-300 dark:text-slate-600">·</span>
                                    <span class="font-semibold text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
                                    <span class="text-slate-300 dark:text-slate-600">·</span>
                                    <span class="text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ \PaymentLabels::paymentMethod($p->payment_method) }} · {{ $p->payment_date->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium {{ \PaymentLabels::verificationBadge($p->verification_status) }}">{{ \PaymentLabels::verificationLabel($p->verification_status) }}</span>
                                    <a href="{{ route('tenant.pembayaran.show', $p) }}" aria-label="Detail pembayaran {{ $p->payment_number }}"
                                       class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition">
                                        <i class="ri-arrow-right-up-line"></i>
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Panel bayar / status --}}
        @if($canPay)
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-primary-100 dark:border-primary-500/20 p-6 sm:p-8">
                <h4 class="flex items-center gap-2 font-bold text-base text-slate-900 dark:text-white mb-1"><i class="ri-upload-cloud-2-line text-primary-500"></i> Upload Bukti Pembayaran</h4>
                <p class="text-xs text-slate-400 dark:text-slate-500 mb-5">Pembayaran Anda akan diverifikasi oleh pengelola dalam 1&times;24 jam.</p>

                {{-- Instruksi pembayaran dari pengelola --}}
                @if($tagihan->kamar->kos->payment_info)
                    <div class="mb-5 rounded-xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/60 dark:bg-blue-500/[0.06] p-4">
                        <p class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-300 mb-1.5"><i class="ri-bank-card-line"></i> Informasi Pembayaran — {{ $tagihan->kamar->kos->name }}</p>
                        <p class="text-xs text-blue-800/90 dark:text-blue-200/90 leading-relaxed whitespace-pre-line">{{ $tagihan->kamar->kos->payment_info }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('tenant.pembayaran.store') }}" enctype="multipart/form-data"
                      x-data="{ method: '{{ old('payment_method', 'transfer_bank') }}', submitting: false }"
                      x-on:submit="submitting = true">
                    @csrf
                    <input type="hidden" name="tagihan_id" value="{{ $tagihan->id }}">
                    <input type="hidden" name="amount" value="{{ $tagihan->total }}">
                    <div class="space-y-4 max-w-xl">
                        <div>
                            <label for="amount_display" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Nominal Pembayaran</label>
                            <input id="amount_display" type="text" value="Rp {{ number_format($tagihan->total, 0, ',', '.') }}" readonly disabled
                                   class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 dark:text-slate-200 text-sm font-bold cursor-not-allowed">
                            <p class="mt-1.5 flex items-center gap-1 text-[11px] text-slate-400 dark:text-slate-500"><i class="ri-lock-line"></i> Nominal mengikuti total tagihan dan tidak dapat diubah.</p>
                        </div>

                        <fieldset>
                            <legend class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">Metode Pembayaran <span class="text-red-500">*</span></legend>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                <label class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl border cursor-pointer transition bg-white dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50/60 has-[:checked]:dark:bg-primary-500/10">
                                    <input type="radio" name="payment_method" value="transfer_bank" x-model="method" required class="text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                                    <i class="ri-bank-line text-slate-400"></i><span class="text-xs font-semibold text-slate-700 dark:text-slate-200">Transfer Bank</span>
                                </label>
                                <label class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl border cursor-pointer transition bg-white dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50/60 has-[:checked]:dark:bg-primary-500/10">
                                    <input type="radio" name="payment_method" value="e_wallet" x-model="method" class="text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                                    <i class="ri-qr-code-line text-slate-400"></i><span class="text-xs font-semibold text-slate-700 dark:text-slate-200">QRIS / E-Wallet</span>
                                </label>
                                <label class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl border cursor-pointer transition bg-white dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50/60 has-[:checked]:dark:bg-primary-500/10">
                                    <input type="radio" name="payment_method" value="cash" x-model="method" class="text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                                    <i class="ri-hand-coin-line text-slate-400"></i><span class="text-xs font-semibold text-slate-700 dark:text-slate-200">Tunai</span>
                                </label>
                            </div>
                        </fieldset>

                        {{-- Instruksi dinamis per metode --}}
                        <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-700 px-4 py-3 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            <template x-if="method === 'transfer_bank'">
                                <p><i class="ri-bank-line mr-1.5 text-primary-500"></i>Transfer sesuai nominal ke rekening pengelola di atas, lalu unggah bukti transfer.</p>
                            </template>
                            <template x-if="method === 'e_wallet'">
                                <p><i class="ri-qr-code-line mr-1.5 text-primary-500"></i>Pindai QRIS yang tersedia di loket / dari pengelola, lalu unggah tangkapan layar bukti pembayaran.</p>
                            </template>
                            <template x-if="method === 'cash'">
                                <p><i class="ri-hand-coin-line mr-1.5 text-primary-500"></i>Silakan lakukan pembayaran langsung kepada pengelola/admin kos. Bukti pembayaran opsional untuk metode tunai.</p>
                            </template>
                        </div>

                        <div>
                            <label for="proof_file" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">
                                Bukti Pembayaran <span class="text-red-500">*</span> <span class="font-normal">(JPG/PNG/PDF, maks 5MB)</span>
                            </label>
                            <input id="proof_file" type="file" name="proof_file" accept="image/jpeg,image/png,application/pdf" :required="method !== 'cash'"
                                   class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-500/10 file:text-primary-600 dark:file:text-primary-300 hover:file:bg-primary-100 cursor-pointer">
                            @error('proof_file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        @error('amount') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror

                        <button type="submit" :disabled="submitting"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition shadow-sm shadow-primary-500/30 disabled:opacity-60 disabled:cursor-not-allowed">
                            <i class="ri-send-plane-line"></i> Kirim &amp; Tunggu Verifikasi
                        </button>
                        <p class="text-[11px] text-center text-slate-400 dark:text-slate-500">Setelah dikirim, status menjadi <strong>Menunggu Verifikasi</strong> oleh pengelola.</p>
                    </div>
                </form>
            </div>
        @elseif($tagihan->status === 'pending_verification')
            <div class="rounded-2xl bg-yellow-50 dark:bg-yellow-500/[0.06] border border-yellow-100 dark:border-yellow-500/20 px-6 py-5 text-sm text-yellow-800 dark:text-yellow-300 flex items-start gap-3">
                <i class="ri-time-line text-lg shrink-0"></i>
                <span>Bukti pembayaran Anda sedang <strong>diperiksa pengelola</strong>. Anda akan menerima notifikasi setelah diverifikasi.</span>
            </div>
        @elseif($tagihan->status === 'paid')
            <div class="rounded-2xl bg-green-50 dark:bg-green-500/[0.06] border border-green-100 dark:border-green-500/20 px-6 py-5 text-sm text-green-800 dark:text-green-300 flex items-start gap-3">
                <i class="ri-checkbox-circle-line text-lg shrink-0"></i>
                <span>Tagihan ini sudah <strong>Lunas</strong>. Terima kasih.</span>
            </div>
        @endif
    </div>
</x-app-layout>
