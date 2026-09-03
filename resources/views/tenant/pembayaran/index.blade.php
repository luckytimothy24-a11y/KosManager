<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pembayaran Saya" description="Riwayat pembayaran &amp; status verifikasinya." />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Filter --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor pembayaran..." aria-label="Cari pembayaran"
                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <select name="status" aria-label="Filter status verifikasi"
                        class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Semua Status</option>
                    @foreach(['pending','approved','rejected'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \PaymentLabels::verificationLabel($s) }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>
        </div>

        @if($pembayarans->isEmpty())
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <x-empty-state icon="ri-bank-card-line" title="Belum ada pembayaran"
                               description="Riwayat pembayaran tagihan Anda akan tampil di sini." />
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($pembayarans as $p)
                    <a href="{{ route('tenant.pembayaran.show', $p) }}"
                       class="group bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden hover:shadow-lg hover:-translate-y-0.5 hover:border-primary-200 dark:hover:border-primary-500/30 transition-all duration-200 block">
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-mono font-semibold text-slate-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">{{ $p->payment_number }}</p>
                                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                                        <i class="ri-bank-card-line text-slate-400"></i> {{ \PaymentLabels::paymentMethod($p->payment_method) }}
                                        @if($p->isFromGateway())
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-300"><i class="ri-global-line"></i> Online</span>
                                        @endif
                                    </p>
                                    @if($p->tagihan)
                                        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400 truncate">
                                            <i class="ri-file-list-3-line text-slate-400"></i>
                                            <span class="font-mono font-medium text-slate-600 dark:text-slate-300">{{ $p->tagihan->bill_number }}</span>
                                            <span class="text-slate-300 dark:text-slate-600">·</span>
                                            Periode {{ $p->tagihan->period_start->translatedFormat('d M Y') }} – {{ $p->tagihan->period_end->translatedFormat('d M Y') }}
                                        </p>
                                    @endif
                                </div>
                                <x-status-badge :status="$p->verification_status" context="verification" />
                            </div>

                            <div class="mt-4 flex items-end justify-between gap-3 pt-3.5 border-t border-slate-100 dark:border-slate-800">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tanggal Bayar</p>
                                    <p class="mt-0.5 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $p->payment_date->translatedFormat('d M Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nominal</p>
                                    <p class="mt-0.5 text-base font-black text-slate-900 dark:text-white">Rp {{ number_format($p->amount, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            @if($pembayarans->hasPages())
                <div class="flex justify-center">{{ $pembayarans->links() }}</div>
            @endif
        @endif
    </div>
</x-app-layout>
