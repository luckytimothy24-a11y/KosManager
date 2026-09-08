<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route(Auth::user()->isTenant() ? 'tenant.tagihan.index' : $prefix.'.tagihan.index'); @endphp
    <x-slot name="header">
        <x-page-header title="Tagihan {{ $tagihan->bill_number }}" description="{{ \PaymentLabels::billType($tagihan->bill_type) }} · {{ $tagihan->period_start->translatedFormat('d M Y') }} — {{ $tagihan->period_end->translatedFormat('d M Y') }}">
            <x-button href="{{ $backUrl }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Ringkasan total --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 flex flex-wrap items-center justify-between gap-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Tagihan</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- Detail tagihan --}}
            <div class="lg:col-span-3 space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <i class="ri-file-list-3-line text-primary-500"></i> Rincian Biaya
                    </h3>

                    <dl class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div class="flex items-start gap-2.5">
                            <i class="ri-user-star-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Penghuni</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $tagihan->penghuni->user->name }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-door-open-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kamar</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $tagihan->kamar->room_number }} · {{ $tagihan->kamar->kos?->name }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-price-tag-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jenis Tagihan</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ \PaymentLabels::billType($tagihan->bill_type) }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-calendar-range-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Periode</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $tagihan->period_start->translatedFormat('d M Y') }} — {{ $tagihan->period_end->translatedFormat('d M Y') }}</dd>
                            </div>
                        </div>
                    </dl>

                    {{-- Breakdown --}}
                    <div class="mx-6 mb-6 rounded-xl border border-slate-100 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800 text-sm">
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
                </div>
            </div>

            {{-- Riwayat pembayaran --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <i class="ri-history-line text-primary-500"></i> Riwayat Pembayaran
                    </h3>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-[24rem] overflow-y-auto scrollbar-thin">
                        @forelse($tagihan->pembayarans as $p)
                            <a href="{{ route("$prefix.pembayaran.show", $p) }}" class="block px-6 py-4 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300 truncate">{{ $p->payment_number }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                                            <i class="{{ ['transfer_bank' => 'ri-bank-line', 'cash' => 'ri-cash-line', 'e_wallet' => 'ri-smartphone-line'][$p->payment_method] ?? 'ri-wallet-3-line' }}"></i>
                                            {{ \PaymentLabels::paymentMethod($p->payment_method) }}
                                        </p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $p->payment_date->translatedFormat('d M Y') }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($p->amount, 0, ',', '.') }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ \PaymentLabels::verificationBadge($p->verification_status) }}">{{ \PaymentLabels::verificationLabel($p->verification_status) }}</span>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="py-8 text-center">
                                <span class="mx-auto w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                    <i class="ri-money-dollar-circle-line text-lg text-slate-300 dark:text-slate-600"></i>
                                </span>
                                <p class="mt-2.5 text-sm text-slate-400 dark:text-slate-500">Belum ada pembayaran.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                @if(Auth::user()->isTenant() && !in_array($tagihan->status, ['paid', 'cancelled']))
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-primary-100 dark:border-primary-500/20 p-6">
                        <h4 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-1">
                            <i class="ri-hand-coin-line text-primary-500"></i> Konfirmasi Pembayaran Tunai
                        </h4>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mb-4">Pembayaran tunai akan diverifikasi oleh pemilik kos dalam 1&times;24 jam.</p>
                        <form method="POST" action="{{ route('tenant.pembayaran.store') }}" enctype="multipart/form-data" x-data="{ submitting: false }" x-on:submit="submitting = true">
                            @csrf
                            <input type="hidden" name="tagihan_id" value="{{ $tagihan->id }}">
                            <input type="hidden" name="amount" value="{{ $tagihan->total }}">
                            <input type="hidden" name="payment_method" value="cash">
                            <div class="space-y-4">
                                <div>
                                    <label for="amount_display" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Nominal</label>
                                    <input id="amount_display" type="text" value="Rp {{ number_format($tagihan->total, 0, ',', '.') }}" readonly disabled
                                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/60 dark:text-slate-100 text-sm font-bold cursor-not-allowed">
                                </div>
                                <div>
                                    <label for="proof_file" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Bukti Pembayaran <span class="font-normal">(opsional)</span></label>
                                    <input id="proof_file" type="file" name="proof_file" accept="image/jpeg,image/png,application/pdf"
                                           class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-500/10 file:text-primary-600 dark:file:text-primary-300 hover:file:bg-primary-100 cursor-pointer">
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">JPG, PNG, atau PDF. Unggah jika diperlukan pengelola.</p>
                                </div>
                                <button type="submit" :disabled="submitting"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition shadow-sm shadow-primary-500/30 disabled:opacity-60 disabled:cursor-not-allowed">
                                    <i class="ri-hand-coin-line"></i> Kirim &amp; Tunggu Verifikasi
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
