<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tagihan Saya" description="Kelola pembayaran tagihan kos Anda." />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Filter --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor tagihan..." aria-label="Cari tagihan"
                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <select name="status" aria-label="Filter status tagihan"
                        class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Semua Status</option>
                    @foreach(['unpaid','pending_verification','paid','overdue'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \PaymentLabels::tagihanLabel($s) }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>
        </div>

        {{-- Daftar Tagihan --}}
        <div class="space-y-4">
            @forelse($tagihans as $t)
                @php
                    $canPay = in_array($t->status, ['unpaid', 'overdue']);
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border {{ $canPay ? 'border-primary-100 dark:border-primary-500/20' : 'border-slate-100 dark:border-slate-800' }} p-5 flex flex-col lg:flex-row lg:items-center gap-4 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $t->period_start->translatedFormat('F Y') }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \PaymentLabels::tagihanBadge($t->status) }}">
                                {{ \PaymentLabels::tagihanLabel($t->status) }}
                            </span>
                            @if($canPay && $t->due_date->isPast())
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300">
                                    <i class="ri-alarm-warning-line"></i> Terlambat
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 font-mono">{{ $t->bill_number }} · {{ \PaymentLabels::billType($t->bill_type) }}</p>
                        <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-1.5 text-xs">
                            <div>
                                <span class="block text-slate-400 dark:text-slate-500">Kamar</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $t->kamar->room_number }} · {{ $t->kamar->kos->name }}</span>
                            </div>
                            <div>
                                <span class="block text-slate-400 dark:text-slate-500">Periode</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ $t->period_start->translatedFormat('d M Y') }} – {{ $t->period_end->translatedFormat('d M Y') }}</span>
                            </div>
                            <div>
                                <span class="block text-slate-400 dark:text-slate-500">Dibuat</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $t->created_at->translatedFormat('d M Y') }}</span>
                            </div>
                            <div>
                                <span class="block text-slate-400 dark:text-slate-500">Jatuh Tempo</span>
                                <span class="font-semibold whitespace-nowrap {{ $canPay ? ($t->due_date->isPast() ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400') : 'text-slate-700 dark:text-slate-200' }}">{{ $t->due_date->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 lg:text-right lg:pl-6 lg:border-l border-slate-100 dark:border-slate-800">
                        <p class="text-xl font-black text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($t->total, 0, ',', '.') }}</p>
                        <div class="mt-2 flex lg:justify-end">
                            @if($canPay)
                                <a href="{{ route('tenant.tagihan.show', $t) }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-xs font-bold transition shadow-sm shadow-primary-500/30">
                                    <i class="ri-wallet-3-line"></i> Bayar Sekarang
                                </a>
                            @else
                                <a href="{{ route('tenant.tagihan.show', $t) }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                    Lihat Detail
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                    <x-empty-state icon="ri-file-list-3-line" title="Belum ada tagihan"
                                   description="Tagihan sewa dari pengelola akan tampil di sini." />
                </div>
            @endforelse
        </div>

        @if($tagihans->hasPages())
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4">
                {{ $tagihans->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
