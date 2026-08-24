<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Tagihan" description="Kelola data tagihan.">
            @if(!Auth::user()->isTenant())
                <x-button href="{{ route('owner.tagihan.create') }}" type="primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Tagihan
                </x-button>
            @endif
        </x-page-header>
    </x-slot>
    <x-alert />
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tagihan..." class="flex-1 rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                <select name="status" class="rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                    <option value="">Semua Status</option>
                    @foreach(['unpaid','pending_verification','paid','overdue','cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \PaymentLabels::tagihanLabel($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-gray-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">No. Tagihan</th>
                        <th class="px-4 py-3 text-left font-medium">Penghuni</th>
                        <th class="px-4 py-3 text-left font-medium">Kamar</th>
                        <th class="px-4 py-3 text-left font-medium">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-right font-medium">Total</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($tagihans as $t)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800">
                            <td class="px-4 py-3 font-mono text-xs">{{ $t->bill_number }}</td>
                            <td class="px-4 py-3">{{ $t->penghuni->user->name }}</td>
                            <td class="px-4 py-3">{{ $t->kamar->room_number }}</td>
                            <td class="px-4 py-3">{{ $t->due_date->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \PaymentLabels::tagihanBadge($t->status) }}">{{ \PaymentLabels::tagihanLabel($t->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right"><a href="{{ route(Auth::user()->isTenant() ? 'tenant.tagihan.show' : "$prefix.tagihan.show", $t) }}" class="text-blue-600 hover:text-blue-800 text-xs">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-3"><x-empty-state icon="ri-file-list-3-line" title="Belum ada tagihan." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $tagihans->links() }}</div>
    </div>
</x-app-layout>
