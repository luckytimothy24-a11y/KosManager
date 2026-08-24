<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail Tagihan" description="Informasi tagihan kos Anda." />
    </x-slot>
    <x-alert />
    @php $canPay = in_array($tagihan->status, ['unpaid', 'overdue']); @endphp
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs text-gray-400 dark:text-slate-500 font-mono">{{ $tagihan->bill_number }}</p>
                <h3 class="text-lg font-black text-gray-900 dark:text-white">Tagihan {{ $tagihan->period_start->translatedFormat('F Y') }}</h3>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ \PaymentLabels::tagihanBadge($tagihan->status) }}">
                {{ \PaymentLabels::tagihanLabel($tagihan->status) }}
            </span>
        </div>

        <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 mb-1">Kamar</h3>
                <p class="text-sm">{{ $tagihan->kamar->room_number }} - {{ $tagihan->kamar->room_name }} ({{ $tagihan->kamar->kos->name }})</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 mb-1">Tipe Tagihan</h3>
                <p class="text-sm">{{ ucfirst($tagihan->bill_type) }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 mb-1">Periode</h3>
                <p class="text-sm">{{ $tagihan->period_start->translatedFormat('d M Y') }} s/d {{ $tagihan->period_end->translatedFormat('d M Y') }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 mb-1">Jatuh Tempo</h3>
                <p class="text-sm {{ in_array($tagihan->status, ['unpaid', 'overdue']) ? 'font-semibold text-red-600 dark:text-red-400' : '' }}">{{ $tagihan->due_date->translatedFormat('d M Y') }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 mb-1">Subtotal</h3>
                <p class="text-sm">Rp {{ number_format($tagihan->subtotal, 0, ',', '.') }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 mb-1">Diskon</h3>
                <p class="text-sm">Rp {{ number_format($tagihan->discount, 0, ',', '.') }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 mb-1">Denda</h3>
                <p class="text-sm">Rp {{ number_format($tagihan->penalty, 0, ',', '.') }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 mb-1">Total</h3>
                <p class="text-lg font-black text-primary-600 dark:text-primary-300">Rp {{ number_format($tagihan->total, 0, ',', '.') }}</p>
            </div>
        </div>

        @if($tagihan->pembayarans->isNotEmpty())
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-700">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-2">Riwayat Pembayaran</h4>
                <ul class="space-y-2">
                    @foreach($tagihan->pembayarans->sortByDesc('created_at') as $p)
                        <li class="flex flex-wrap items-center justify-between gap-2 p-3 bg-gray-50 dark:bg-slate-800/60 rounded-lg text-sm">
                            <div class="min-w-0">
                                <span class="font-mono text-xs">{{ $p->payment_number }}</span>
                                <span class="text-gray-400 mx-2">·</span>
                                <span class="font-semibold">Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
                                <span class="text-gray-400 mx-2">·</span>
                                <span>{{ \PaymentLabels::paymentMethod($p->payment_method) }} · {{ $p->payment_date->translatedFormat('d M Y') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \PaymentLabels::verificationBadge($p->verification_status) }}">{{ \PaymentLabels::verificationLabel($p->verification_status) }}</span>
                                <a href="{{ route('tenant.pembayaran.show', $p) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Detail</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($canPay)
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-700">
                <div class="p-4 bg-gray-50 dark:bg-slate-800/60 rounded-lg">
                    <h4 class="font-bold text-sm mb-3 text-gray-900 dark:text-white"><i class="ri-upload-cloud-2-line mr-1"></i>Upload Bukti Pembayaran</h4>

                    {{-- Instruksi pembayaran dari pengelola --}}
                    @if($tagihan->kamar->kos->payment_info)
                        <div class="mb-4 p-3 rounded-lg border border-blue-100 dark:border-primary-500/20 bg-blue-50/60 dark:bg-primary-500/10 text-xs text-gray-600 dark:text-slate-300 whitespace-pre-line">
                            <p class="font-bold text-gray-800 dark:text-white mb-1"><i class="ri-bank-card-line mr-1"></i>Informasi Pembayaran — {{ $tagihan->kamar->kos->name }}</p>
                            {{ $tagihan->kamar->kos->payment_info }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tenant.pembayaran.store') }}" enctype="multipart/form-data"
                          x-data="{ method: '{{ old('payment_method', 'transfer_bank') }}' }">
                        @csrf
                        <input type="hidden" name="tagihan_id" value="{{ $tagihan->id }}">
                        <input type="hidden" name="amount" value="{{ $tagihan->total }}">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Nominal Pembayaran</label>
                                <input type="text" value="Rp {{ number_format($tagihan->total, 0, ',', '.') }}" readonly disabled
                                       class="w-full rounded-lg border-gray-200 dark:border-slate-700 bg-gray-100 dark:bg-slate-900/60 text-sm font-bold text-gray-700 dark:text-slate-200 cursor-not-allowed">
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1"><i class="ri-lock-line"></i> Nominal mengikuti total tagihan dan tidak dapat diubah.</p>
                            </div>
                            <div>
                                <label for="payment_method" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                                <select id="payment_method" name="payment_method" required x-model="method"
                                        class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                                    <option value="transfer_bank">Transfer Bank</option>
                                    <option value="e_wallet">QRIS / E-Wallet</option>
                                    <option value="cash">Tunai</option>
                                </select>
                            </div>

                            {{-- Instruksi dinamis per metode --}}
                            <div class="p-3 rounded-lg border border-dashed border-gray-300 dark:border-slate-600 text-xs text-gray-500 dark:text-slate-400 leading-relaxed">
                                <template x-if="method === 'transfer_bank'">
                                    <p><i class="ri-bank-line mr-1 text-primary-500"></i>Transfer sesuai nominal ke rekening pengelola di atas, lalu unggah bukti transfer.</p>
                                </template>
                                <template x-if="method === 'e_wallet'">
                                    <p><i class="ri-qr-code-line mr-1 text-primary-500"></i>Pindai QRIS yang tersedia di loket / dari pengelola, lalu unggah tangkapan layar bukti pembayaran.</p>
                                </template>
                                <template x-if="method === 'cash'">
                                    <p><i class="ri-hand-coin-line mr-1 text-primary-500"></i>Silakan lakukan pembayaran langsung kepada pengelola/admin kos. Bukti pembayaran opsional untuk metode tunai.</p>
                                </template>
                            </div>

                            <div>
                                <label for="proof_file" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                                    Bukti Pembayaran <span class="text-red-500">*</span> <span class="font-normal">(JPG/PNG/PDF, maks 5MB)</span>
                                </label>
                                <input type="file" id="proof_file" name="proof_file" accept="image/jpeg,image/png,application/pdf" :required="method !== 'cash'"
                                       class="w-full text-sm text-gray-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @error('proof_file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            @error('amount') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                            <button type="submit" class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-lg hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30">
                                Kirim &amp; Tunggu Verifikasi
                            </button>
                            <p class="text-[11px] text-center text-slate-400 dark:text-slate-500">Setelah dikirim, status menjadi <strong>Menunggu Verifikasi</strong> oleh pengelola.</p>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($tagihan->status === 'pending_verification')
            <div class="mt-6 p-4 rounded-lg bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-100 dark:border-yellow-500/20 text-sm text-yellow-800 dark:text-yellow-300">
                <i class="ri-time-line mr-1"></i> Bukti pembayaran Anda sedang <strong>diperiksa pengelola</strong>. Anda akan menerima notifikasi setelah diverifikasi.
            </div>
        @elseif($tagihan->status === 'paid')
            <div class="mt-6 p-4 rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-100 dark:border-green-500/20 text-sm text-green-800 dark:text-green-300">
                <i class="ri-checkbox-circle-line mr-1"></i> Tagihan ini sudah <strong>Lunas</strong>. Terima kasih.
            </div>
        @endif
    </div>
</x-app-layout>
