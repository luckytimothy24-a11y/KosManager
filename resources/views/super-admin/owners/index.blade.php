<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Owner Management" description="Kelola seluruh akun owner di platform." icon="ri-building-2-line">
            <x-button href="{{ route('super-admin.users.create') }}" type="primary">
                <i class="ri-add-line text-base"></i> Tambah Owner
            </x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau telepon..." aria-label="Cari owner"
                           class="field-input w-full pl-10">
                </div>
                <select name="status" aria-label="Filter status" class="field-input sm:w-44">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <select name="has_kos" aria-label="Filter kos" class="field-input sm:w-44">
                    <option value="">Semua</option>
                    <option value="1" {{ request('has_kos') === '1' ? 'selected' : '' }}>Memiliki Kos</option>
                </select>
                <button type="submit" class="btn-secondary px-4 py-2.5 whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>
        </div>

        <div class="table-card">
            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th scope="col">Owner</th>
                            <th scope="col" class="hidden md:table-cell">Telepon</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-center">Jumlah Kos</th>
                            <th scope="col" class="text-center">Jumlah Kamar</th>
                            <th scope="col" class="text-center">Jumlah Tenant</th>
                            <th scope="col" class="hidden md:table-cell">Daftar</th>
                            <th scope="col" class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($owners as $owner)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3 min-w-[10rem]">
                                        <a href="{{ route('super-admin.owners.show', $owner) }}"
                                           class="w-9 h-9 rounded-xl bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold text-xs flex items-center justify-center shrink-0 uppercase"
                                           title="Lihat {{ $owner->name }}" aria-label="Lihat {{ $owner->name }}">
                                            {{ mb_substr($owner->name, 0, 1) }}
                                        </a>
                                        <div class="min-w-0">
                                            <a href="{{ route('super-admin.owners.show', $owner) }}"
                                               class="block font-semibold text-slate-900 dark:text-slate-50 truncate hover:text-primary-600 dark:hover:text-primary-400 transition">
                                                {{ $owner->name }}
                                            </a>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $owner->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell text-slate-600 dark:text-slate-400">{{ $owner->phone ?? '-' }}</td>
                                <td class="text-center">
                                    <x-status-badge :status="$owner->is_active ? 'active' : 'inactive'" context="penghuni" :label="$owner->is_active ? 'Aktif' : 'Nonaktif'" />
                                </td>
                                <td class="text-center">{{ $owner->owned_kos_count ?? 0 }}</td>
                                <td class="text-center">{{ $owner->ownedKos->sum('kamar_count') ?? 0 }}</td>
                                <td class="text-center">{{ $owner->penghunis_count ?? 0 }}</td>
                                <td class="hidden md:table-cell text-xs text-slate-500 dark:text-slate-400">{{ $owner->created_at->translatedFormat('d M Y') }}</td>
                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('super-admin.owners.show', $owner) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Lihat" aria-label="Lihat {{ $owner->name }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if($owner->is_active)
                                            <form method="POST" action="{{ route('super-admin.owners.deactivate', $owner) }}" class="inline">
                                                @csrf @method('POST')
                                                <x-confirm-dialog
                                                    title="Nonaktifkan owner ini?"
                                                    description="{{ $owner->name }} akan kehilangan akses login."
                                                    confirmText="Ya, Nonaktifkan"
                                                    triggerClass="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition"
                                                >
                                                    <i class="ri-power-off-line"></i>
                                                    <x-slot name="actions">
                                                        <button type="submit" class="btn-secondary px-4 py-2 text-sm">Ya, Nonaktifkan</button>
                                                    </x-slot>
                                                </x-confirm-dialog>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('super-admin.owners.activate', $owner) }}" class="inline">
                                                @csrf @method('POST')
                                                <button type="submit"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition"
                                                        title="Aktifkan" aria-label="Aktifkan {{ $owner->name }}">
                                                    <i class="ri-power-on-line"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><x-empty-state icon="ri-building-2-line" title="Tidak ada owner ditemukan" description="Coba ubah kata kunci pencarian atau filter." /></td></tr>
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
