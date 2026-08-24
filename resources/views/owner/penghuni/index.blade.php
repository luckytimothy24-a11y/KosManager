<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Penghuni" description="Daftar seluruh penghuni di properti Anda." />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            {{-- Filter bar --}}
            <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                    <div class="relative flex-1 min-w-0">
                        <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, telepon, atau identitas..." aria-label="Cari penghuni"
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <select name="status" aria-label="Filter status penghuni"
                            class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
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
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">Penghuni</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden md:table-cell">Kamar</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden lg:table-cell">Telepon</th>
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($penghunis as $p)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3 min-w-[10rem]">
                                        <span class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-sm font-bold shrink-0">{{ strtoupper(substr($p->user->name, 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 dark:text-white truncate max-w-[12rem]">{{ $p->user->name }}</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[12rem]">{{ $p->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 hidden md:table-cell">
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $p->kamar->room_number }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[10rem]">{{ $p->kos->name }}</p>
                                </td>
                                <td class="px-4 py-3.5 hidden lg:table-cell text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $p->phone ?: '-' }}</td>
                                <td class="px-4 py-3.5 text-center"><x-status-badge :status="$p->status" context="penghuni" /></td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route("$prefix.penghuni.show", $p) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition"
                                           title="Detail" aria-label="Detail penghuni {{ $p->user->name }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state icon="ri-user-star-line" title="Belum ada penghuni"
                                                   description="Penghuni akan muncul otomatis setelah check-in disetujui." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($penghunis->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $penghunis->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
