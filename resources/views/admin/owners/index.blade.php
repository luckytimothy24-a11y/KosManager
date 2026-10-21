<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pengelola Owner" description="Daftar owner yang mengelola kos dalam penugasan Anda." icon="ri-team-line" />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="grid grid-cols-2 gap-3">
            <div class="card-surface p-4">
                <p class="text-[11px] font-bold tracking-widest uppercase text-slate-400 dark:text-slate-500">Total Owner</p>
                <p class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-50">{{ $totalOwners }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">dalam cakupan penugasan</p>
            </div>
            <div class="card-surface p-4">
                <p class="text-[11px] font-bold tracking-widest uppercase text-slate-400 dark:text-slate-500">Total Kos Dikelola</p>
                <p class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-50">{{ $totalKos }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">kos unik pada penugasan Anda</p>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." aria-label="Cari owner" class="field-input w-full pl-10">
                </div>
                <select name="status" aria-label="Filter status" class="field-input sm:w-44">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <button type="submit" class="btn-secondary px-4 py-2.5 whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.owners.index') }}" class="btn-secondary px-4 py-2.5 whitespace-nowrap text-center">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-card">
            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th scope="col">Owner</th>
                            <th scope="col" class="hidden md:table-cell">Email</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-center">Jumlah Kos</th>
                            <th scope="col" class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($owners as $owner)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3 min-w-[10rem]">
                                        <a href="{{ route('admin.owners.show', $owner) }}" class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 font-bold text-xs flex items-center justify-center shrink-0 uppercase" title="Lihat {{ $owner->name }}" aria-label="Lihat {{ $owner->name }}">
                                            {{ mb_substr($owner->name, 0, 1) }}
                                        </a>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.owners.show', $owner) }}" class="block font-semibold text-slate-900 dark:text-slate-50 truncate hover:text-primary-600 dark:hover:text-primary-400 transition">{{ $owner->name }}</a>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate md:hidden">{{ $owner->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell text-slate-600 dark:text-slate-400 truncate max-w-[16rem]">{{ $owner->email }}</td>
                                <td class="text-center">
                                    <x-status-badge :status="$owner->is_active ? 'active' : 'inactive'" context="penghuni" :label="$owner->is_active ? 'Aktif' : 'Nonaktif'" />
                                </td>
                                <td class="text-center font-semibold text-slate-900 dark:text-slate-100">{{ $owner->owned_kos_count ?? $owner->ownedKos_count ?? 0 }}</td>
                                <td>
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route('admin.owners.show', $owner) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 text-white dark:bg-white dark:text-slate-900 hover:opacity-90 transition" aria-label="Lihat detail {{ $owner->name }}">
                                            <i class="ri-eye-line"></i> Lihat Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-empty-state icon="ri-team-line" title="Tidak ada pengelola ditemukan" description="Tidak ada owner dalam cakupan penugasan Anda atau coba ubah kata kunci pencarian." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($owners->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-white/[0.06]">
                    {{ $owners->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
