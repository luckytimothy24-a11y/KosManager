<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pembayaran Saya" description="Riwayat pembayaran dan status verifikasinya." />
    </x-slot>
    <x-alert />

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. pembayaran..." class="flex-1 rounded-lg border-gray-300 dark:border-slate-600 text-sm">
            <select name="status" class="rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                <option value="">Semua Status</option>
                @foreach(['pending','approved','rejected'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \PaymentLabels::verificationLabel($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 rounded-lg text-sm font-medium">Filter</button>
        </form>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-gray-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">No. Pembayaran</th>
                        <th class="px-4 py-3 text-right font-medium">Nominal</th>
                        <th class="px-4 py-3 text-left font-medium">Tanggal Bayar</th>
                        <th class="px-4 py-3 text-left font-medium">Metode</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($pembayarans as $p)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800">
                            <td class="px-4 py-3 font-mono text-xs">{{ $p->payment_number }}</td>
                            <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $p->payment_date->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">{{ \PaymentLabels::paymentMethod($p->payment_method) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \PaymentLabels::verificationBadge($p->verification_status) }}">{{ \PaymentLabels::verificationLabel($p->verification_status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('tenant.pembayaran.show', $p) }}" class="text-blue-600 hover:text-blue-800 text-xs">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-3"><x-empty-state icon="ri-bank-card-line" title="Belum ada pembayaran." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $pembayarans->links() }}</div>
    </div>
</x-app-layout>
