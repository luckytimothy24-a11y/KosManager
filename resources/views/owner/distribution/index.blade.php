<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Distribusi Pembayaran" description="Pembayaran booking yang menjadi hak Owner.">
            <x-button href="{{ route('owner.bank-account.edit') }}" type="secondary"><i class="ri-bank-line"></i> Rekening Bank</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        @if(! $bankAccount)
            <div class="rounded-2xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 p-5">
                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Rekening bank belum diatur</p>
                <p class="text-sm text-amber-700 dark:text-amber-300/90 mt-1">Simpan rekening tujuan pencairan agar data pembayaran lengkap.</p>
                <a href="{{ route('owner.bank-account.edit') }}" class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition">
                    <i class="ri-bank-line"></i> Atur Rekening
                </a>
            </div>
        @else
            <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Rekening tujuan</p>
                <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ $bankAccount->bank_name }} · {{ $bankAccount->maskedAccountNumber() }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atas nama: {{ $bankAccount->account_holder_name }}</p>
            </div>
        @endif

        @if($summary)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Gross</p>
                    <p class="mt-1 text-lg font-bold text-slate-900 dark:text-slate-50">Rp {{ number_format((float) $summary['total_gross'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Menunggu Pencairan</p>
                    <p class="mt-1 text-lg font-bold text-amber-600">Rp {{ number_format((float) $summary['waiting'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $summary['count_waiting'] }} transaksi</p>
                </div>
                <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Sudah Dicairkan</p>
                    <p class="mt-1 text-lg font-bold text-green-600">Rp {{ number_format((float) $summary['processed'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $summary['count_processed'] }} transaksi</p>
                </div>
                <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Bagian Owner (diproses)</p>
                    <p class="mt-1 text-lg font-bold text-primary-600">Rp {{ number_format((float) $summary['total_owner'], 0, ',', '.') }}</p>
                </div>
            </div>
        @endif

        <div class="table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Riwayat Distribusi</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Payment ≠ Payout — status distribusi terpisah</span>
            </div>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-6 py-3">Booking</th>
                            <th class="px-6 py-3">Kos / Tenant</th>
                            <th class="px-6 py-3 text-right">Gross</th>
                            <th class="px-6 py-3 text-right">Fee</th>
                            <th class="px-6 py-3 text-right">Owner Amount</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                        @forelse($distributions as $d)
                            <tr>
                                <td class="px-6 py-4 font-mono text-xs">{{ $d->booking?->booking_code ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-slate-800 dark:text-slate-100">{{ $d->booking?->kos?->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $d->booking?->user?->name ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-right">Rp {{ number_format((float) $d->gross_amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-slate-500">Rp {{ number_format((float) $d->platform_fee, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-bold text-primary-600">Rp {{ number_format((float) $d->owner_amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $badge = match($d->status) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'ready' => 'bg-blue-100 text-blue-800',
                                            'processed' => 'bg-green-100 text-green-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                        {{ $d->status === 'pending' ? 'Menunggu Konfirmasi Booking' : ($d->status === 'ready' ? 'Menunggu Pencairan' : ($d->status === 'processed' ? 'Sudah Dicairkan' : 'Gagal')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">{{ $d->created_at?->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    @can('markProcessed', $d)
                                        @if($d->status === 'ready')
                                            <form method="POST" action="{{ route('owner.distribution.mark-processed', $d) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
                                                    <i class="ri-check-line"></i> Tandai Dicairkan
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Belum ada distribusi pembayaran booking.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4">
                {{ $distributions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
