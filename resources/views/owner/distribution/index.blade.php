<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dana Booking Masuk ke Pengelola" description="Tahap 1 dari alur dana: pembayaran tenant diterima dan diproses oleh Pengelola Kos.">
            <x-button href="{{ route('owner.payouts.index') }}" type="primary"><i class="ri-send-plane-line"></i> Transfer ke Pemilik Kos</x-button>
            <x-button href="{{ route('owner.bank-account.edit') }}" type="secondary"><i class="ri-bank-line"></i> Rekening Pengelola</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <section class="rounded-2xl border border-primary-100 bg-primary-50/70 p-5 dark:border-primary-500/20 dark:bg-primary-500/10">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="flex items-center gap-2 text-sm font-bold text-primary-900 dark:text-primary-100">
                        <i class="ri-route-line text-lg"></i> Pahami alur dana Anda
                    </h2>
                    <p class="mt-1 text-sm text-primary-800/80 dark:text-primary-100/80">
                        Halaman ini hanya mencatat dana booking dari tenant ke rekening Pengelola. Dana belum otomatis diteruskan ke Pemilik Kos Asli.
                    </p>
                </div>
                <a href="{{ route('owner.payouts.index') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700">
                    Lanjut ke Transfer Pemilik Kos <i class="ri-arrow-right-line"></i>
                </a>
            </div>
            <ol class="mt-5 grid gap-3 sm:grid-cols-3">
                <li class="rounded-xl bg-white/80 p-4 dark:bg-slate-900/20">
                    <span class="text-xs font-bold text-primary-600 dark:text-primary-300">LANGKAH 1 · HALAMAN INI</span>
                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">Tenant bayar ke Pengelola</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Pembayaran booking tercatat sebagai dana yang diterima Pengelola.</p>
                </li>
                <li class="rounded-xl bg-white/80 p-4 dark:bg-slate-900/20">
                    <span class="text-xs font-bold text-primary-600 dark:text-primary-300">LANGKAH 2</span>
                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">Hitung dan buat payout</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Sistem menghitung komisi Pengelola dan dana bersih untuk Pemilik Kos.</p>
                </li>
                <li class="rounded-xl bg-white/80 p-4 dark:bg-slate-900/20">
                    <span class="text-xs font-bold text-primary-600 dark:text-primary-300">LANGKAH 3</span>
                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">Transfer dan unggah bukti</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Pengelola transfer manual, lalu Super Admin memverifikasi bukti transfer.</p>
                </li>
            </ol>
        </section>

        @if(! $bankAccount)
            <div class="rounded-2xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 p-5">
                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Rekening bank belum diatur</p>
                <p class="text-sm text-amber-700 dark:text-amber-300/90 mt-1">Simpan rekening Pengelola yang menerima pembayaran booking dari tenant.</p>
                <a href="{{ route('owner.bank-account.edit') }}" class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition">
                    <i class="ri-bank-line"></i> Atur Rekening
                </a>
            </div>
        @else
            <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Rekening Pengelola (penerima pembayaran tenant)</p>
                <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ $bankAccount->bank_name }} · {{ $bankAccount->maskedAccountNumber() }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atas nama: {{ $bankAccount->account_holder_name }}</p>
            </div>
        @endif

        @if($summary)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Dana Booking Masuk</p>
                    <p class="mt-1 text-lg font-bold text-slate-900 dark:text-slate-50">Rp {{ number_format((float) $summary['total_gross'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Siap Diproses Pengelola</p>
                    <p class="mt-1 text-lg font-bold text-amber-600">Rp {{ number_format((float) $summary['waiting'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $summary['count_waiting'] }} transaksi</p>
                </div>
                <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Sudah Diproses Pengelola</p>
                    <p class="mt-1 text-lg font-bold text-green-600">Rp {{ number_format((float) $summary['processed'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $summary['count_processed'] }} transaksi</p>
                </div>
                <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Dana Diterima Pengelola</p>
                    <p class="mt-1 text-lg font-bold text-primary-600">Rp {{ number_format((float) $summary['total_owner'], 0, ',', '.') }}</p>
                </div>
            </div>
        @endif

        <div class="table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Riwayat Distribusi</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Status di halaman ini terpisah dari transfer ke Pemilik Kos</span>
            </div>

            {{-- Mobile cards --}}
            <div class="space-y-3 p-4 md:hidden">
                @forelse($distributions as $d)
                    <x-mobile-record-card
                        :title="$d->booking?->booking_code ?? '-'"
                        :subtitle="($d->booking?->kos?->name ?? '-') . ' · ' . ($d->booking?->user?->name ?? '-')"
                        :meta="[
                            'Dana booking' => 'Rp ' . number_format((float) $d->gross_amount, 0, ',', '.'),
                            'Biaya' => 'Rp ' . number_format((float) $d->platform_fee, 0, ',', '.'),
                            'Dana pengelola' => 'Rp ' . number_format((float) $d->owner_amount, 0, ',', '.'),
                            'Tanggal' => $d->created_at?->format('d M Y') ?? '-',
                        ]">
                        <x-slot name="badge">
                            @php
                                $badge = match($d->status) {
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'ready' => 'bg-blue-100 text-blue-800',
                                    'processed' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badge }}">
                                {{ $d->status === 'pending' ? 'Menunggu Booking' : ($d->status === 'ready' ? 'Siap Diproses' : ($d->status === 'processed' ? 'Dana Diterima' : 'Gagal')) }}
                            </span>
                        </x-slot>
                        <x-slot name="actions">
                            @can('markProcessed', $d)
                                @if($d->status === 'ready')
                                    <form method="POST" action="{{ route('owner.distribution.mark-processed', $d) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex min-h-11 items-center gap-1.5 rounded-xl bg-green-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-green-700">
                                            <i class="ri-check-line"></i> Tandai Diproses
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </x-slot>
                    </x-mobile-record-card>
                @empty
                    <div class="card-surface">
                        <x-empty-state icon="ri-wallet-3-line" title="Belum ada distribusi"
                                       description="Distribusi pembayaran booking akan muncul di sini." />
                    </div>
                @endforelse
            </div>

            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-6 py-3">Booking</th>
                            <th class="px-6 py-3">Kos / Tenant</th>
                            <th class="px-6 py-3 text-right">Dana Booking</th>
                            <th class="px-6 py-3 text-right">Biaya</th>
                            <th class="px-6 py-3 text-right">Dana ke Pengelola</th>
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
                                        {{ $d->status === 'pending' ? 'Menunggu Konfirmasi Booking' : ($d->status === 'ready' ? 'Siap Diproses Pengelola' : ($d->status === 'processed' ? 'Dana Diterima Pengelola' : 'Gagal')) }}
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
                                                    <i class="ri-check-line"></i> Tandai Diproses Pengelola
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
