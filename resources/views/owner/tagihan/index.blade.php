<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $createUrl = route("$prefix.tagihan.create"); @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Tagihan" description="Kelola tagihan sewa &amp; status pembayarannya.">
            <x-button href="{{ $createUrl }}" type="primary">
                <i class="ri-add-line text-base"></i> Buat Tagihan
            </x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
            {{-- Filter bar --}}
            <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                    <div class="relative flex-1 min-w-0">
                        <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor tagihan..." aria-label="Cari tagihan"
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <select name="status" aria-label="Filter status tagihan"
                            class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Semua Status</option>
                        @foreach(['unpaid','pending_verification','paid','overdue','cancelled'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \PaymentLabels::tagihanLabel($s) }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition whitespace-nowrap">
                        <i class="ri-filter-3-line"></i> Terapkan
                    </button>
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">No. Tagihan</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden md:table-cell">Penghuni</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">Kamar</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden sm:table-cell">Jatuh Tempo</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Total</th>
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($tagihans as $t)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3.5">
                                    <p class="font-mono font-semibold text-slate-900 dark:text-white">{{ $t->bill_number }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ \PaymentLabels::billType($t->bill_type) }}</p>
                                </td>
                                <td class="px-4 py-3.5 hidden md:table-cell">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($t->penghuni->user->name, 0, 1)) }}</span>
                                        <span class="text-slate-700 dark:text-slate-200 truncate max-w-[10rem]">{{ $t->penghuni->user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-medium text-slate-900 dark:text-white">{{ $t->kamar->room_number }}</td>
                                <td class="px-4 py-3.5 hidden sm:table-cell">
                                    <span class="text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $t->due_date->translatedFormat('d M Y') }}</span>
                                    @if(in_array($t->status, ['unpaid', 'overdue']) && $t->due_date->isPast())
                                        <p class="text-xs text-red-500">{{ abs($t->due_date->diffInDays(now())) }} hari lewat</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 text-center"><x-status-badge :status="$t->status" context="tagihan" /></td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route(Auth::user()->isTenant() ? 'tenant.tagihan.show' : "$prefix.tagihan.show", $t) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition"
                                           title="Detail" aria-label="Detail tagihan {{ $t->bill_number }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state icon="ri-file-list-3-line" title="Belum ada tagihan"
                                                   description="Buat tagihan sewa untuk penghuni dengan kontrak aktif.">
                                        <x-button href="{{ $createUrl }}" type="primary"><i class="ri-add-line"></i> Buat Tagihan</x-button>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tagihans->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $tagihans->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
