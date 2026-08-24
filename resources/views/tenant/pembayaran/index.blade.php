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

        {{-- Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">No. Pembayaran</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Nominal</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden sm:table-cell">Tanggal Bayar</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden md:table-cell">Metode</th>
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($pembayarans as $p)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3.5 font-mono font-semibold text-slate-900 dark:text-white">{{ $p->payment_number }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 hidden sm:table-cell text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $p->payment_date->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3.5 hidden md:table-cell">
                                    <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                        <i class="{{ ['transfer_bank' => 'ri-bank-line', 'cash' => 'ri-cash-line', 'e_wallet' => 'ri-smartphone-line'][$p->payment_method] ?? 'ri-wallet-3-line' }} text-slate-400"></i>
                                        {{ \PaymentLabels::paymentMethod($p->payment_method) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center"><x-status-badge :status="$p->verification_status" context="verification" /></td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route('tenant.pembayaran.show', $p) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition"
                                           title="Detail" aria-label="Detail pembayaran {{ $p->payment_number }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="ri-bank-card-line" title="Belum ada pembayaran"
                                                   description="Riwayat pembayaran tagihan Anda akan tampil di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pembayarans->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $pembayarans->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
