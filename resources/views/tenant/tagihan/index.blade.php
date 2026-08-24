<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tagihan Saya" description="Kelola pembayaran tagihan kos Anda." />
    </x-slot>
    <x-alert />

    {{-- Filter --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. tagihan..." class="flex-1 rounded-lg border-gray-300 dark:border-slate-600 text-sm">
            <select name="status" class="rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                <option value="">Semua Status</option>
                @foreach(['unpaid','pending_verification','paid','overdue'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \PaymentLabels::tagihanLabel($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 rounded-lg text-sm font-medium">Filter</button>
        </form>
    </div>

    {{-- Daftar Tagihan --}}
    <div class="space-y-4">
        @forelse($tagihans as $t)
            @php
                $canPay = in_array($t->status, ['unpaid', 'overdue']);
                $isPaid = $t->status === 'paid';
            @endphp
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-5 flex flex-col lg:flex-row lg:items-center gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $t->period_start->translatedFormat('F Y') }}</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \PaymentLabels::tagihanBadge($t->status) }}">
                            {{ \PaymentLabels::tagihanLabel($t->status) }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-400 dark:text-slate-500 font-mono">{{ $t->bill_number }}</p>
                    <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-1.5 text-xs">
                        <div>
                            <span class="block text-gray-400 dark:text-slate-500">Kamar</span>
                            <span class="font-semibold text-gray-700 dark:text-slate-200">{{ $t->kamar->room_number }} · {{ $t->kamar->kos->name }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-400 dark:text-slate-500">Periode</span>
                            <span class="font-semibold text-gray-700 dark:text-slate-200">{{ $t->period_start->translatedFormat('d M Y') }} – {{ $t->period_end->translatedFormat('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-400 dark:text-slate-500">Dibuat</span>
                            <span class="font-semibold text-gray-700 dark:text-slate-200">{{ $t->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-400 dark:text-slate-500">Jatuh Tempo</span>
                            <span class="font-semibold {{ $canPay ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-slate-200' }}">{{ $t->due_date->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="shrink-0 lg:text-right">
                    <p class="text-xl font-black text-gray-900 dark:text-white">Rp {{ number_format($t->total, 0, ',', '.') }}</p>
                    <div class="mt-2 flex lg:justify-end">
                        @if($canPay)
                            <a href="{{ route('tenant.tagihan.show', $t) }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-xs font-bold transition shadow-sm shadow-primary-500/30">
                                <i class="ri-wallet-3-line"></i> Bayar Sekarang
                            </a>
                        @else
                            <a href="{{ route('tenant.tagihan.show', $t) }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg border border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 text-xs font-semibold hover:bg-gray-50 dark:hover:bg-slate-800 transition">
                                Lihat Detail
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                <x-empty-state icon="ri-file-list-3-line" title="Belum ada tagihan." />
            </div>
        @endforelse
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">{{ $tagihans->links() }}</div>
</x-app-layout>
