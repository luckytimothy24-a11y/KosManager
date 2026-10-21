<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tagihan Saya" description="Kelola pembayaran tagihan kos Anda." />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Filter --}}
        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor tagihan..." aria-label="Cari tagihan"
                           class="field-input pl-10">
                </div>
                <select name="status" aria-label="Filter status tagihan"
                        class="field-input sm:w-auto">
                    <option value="">Semua Status</option>
                    @foreach(['unpaid','pending_verification','paid','overdue'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \PaymentLabels::tagihanLabel($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary">
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
                <div class="card-surface p-5 flex flex-col lg:flex-row lg:items-center gap-4 transition-colors {{ $canPay ? 'border-primary-100 dark:border-primary-500/20' : '' }}">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $t->period_start->translatedFormat('F Y') }}</p>
                            <x-status-badge :status="$t->status" context="tagihan" />
                            @if($canPay && $t->due_date->isPast())
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300">
                                    <i class="ri-alarm-warning-line"></i> Terlambat
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 text-meta font-mono">{{ $t->bill_number }} · {{ \PaymentLabels::billType($t->bill_type) }}</p>
                        <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-1.5 text-xs">
                            <div>
                                <span class="block text-label">Kamar</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $t->kamar->room_number }} · {{ $t->kamar->kos->name }}</span>
                            </div>
                            <div>
                                <span class="block text-label">Periode</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ $t->period_start->translatedFormat('d M Y') }} – {{ $t->period_end->translatedFormat('d M Y') }}</span>
                            </div>
                            <div>
                                <span class="block text-label">Dibuat</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $t->created_at->translatedFormat('d M Y') }}</span>
                            </div>
                            <div>
                                <span class="block text-label">Jatuh Tempo</span>
                                <span class="font-semibold whitespace-nowrap {{ $canPay ? ($t->due_date->isPast() ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400') : 'text-slate-700 dark:text-slate-200' }}">{{ $t->due_date->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 lg:text-right lg:pl-6 lg:border-l border-slate-100 dark:border-white/[0.08]">
                        <p class="text-xl font-semibold text-slate-900 dark:text-slate-50 whitespace-nowrap">Rp {{ number_format($t->total, 0, ',', '.') }}</p>
                        <div class="mt-2 flex lg:justify-end">
                            @if($canPay)
                                <a href="{{ route('tenant.tagihan.show', $t) }}" class="btn-primary px-4 py-2 text-xs">
                                    <i class="ri-wallet-3-line"></i> Bayar Sekarang
                                </a>
                            @else
                                <a href="{{ route('tenant.tagihan.show', $t) }}" class="btn-secondary px-4 py-2 text-xs">
                                    Lihat Detail
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="card-surface overflow-hidden">
                    <x-empty-state icon="ri-file-list-3-line" title="Belum ada tagihan"
                                   description="Tagihan sewa dari pengelola akan tampil di sini." />
                </div>
            @endforelse

            @if($tagihans->hasPages())
                <div class="flex justify-center">{{ $tagihans->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
