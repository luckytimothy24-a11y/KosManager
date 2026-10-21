<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Penghuni" description="Daftar seluruh penghuni di properti Anda." icon="ri-user-star-line" />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Filter --}}
        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, telepon, atau identitas..." aria-label="Cari penghuni"
                           class="field-input pl-10 pr-3.5">
                </div>
                <select name="status" aria-label="Filter status penghuni" class="field-input sm:w-48">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <button type="submit" class="btn-secondary whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-card">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th>Penghuni</th>
                            <th class="hidden md:table-cell">Kamar</th>
                            <th class="hidden lg:table-cell">Telepon</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penghunis as $p)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3 min-w-[10rem]">
                                        <span class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-sm font-bold shrink-0">{{ strtoupper(substr($p->user->name, 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 dark:text-slate-50 truncate max-w-[12rem]">{{ $p->user->name }}</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[12rem]">{{ $p->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell">
                                    <p class="font-medium text-slate-900 dark:text-slate-50">{{ $p->kamar->room_number }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[10rem]">{{ $p->kos->name }}</p>
                                </td>
                                <td class="hidden lg:table-cell text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $p->phone ?: '-' }}</td>
                                <td class="text-center"><x-status-badge :status="$p->status" context="penghuni" /></td>
                                <td>
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route("$prefix.penghuni.show", $p) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition"
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
            <div class="px-4 py-3 border-t border-slate-100 dark:border-white/[0.06]">{{ $penghunis->links() }}</div>
        </div>
    </div>
</x-app-layout>
