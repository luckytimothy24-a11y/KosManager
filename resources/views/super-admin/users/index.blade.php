<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Manajemen User" description="Kelola seluruh pengguna platform KosManager." icon="ri-team-line">
            <x-button href="{{ route('super-admin.users.export', request()->only('search', 'role')) }}" type="secondary">
                <i class="ri-download-line text-base"></i> Export CSV
            </x-button>
            <x-button href="{{ route('super-admin.users.create') }}" type="primary">
                <i class="ri-add-line text-base"></i> Tambah User
            </x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email user..." aria-label="Cari user"
                           class="field-input w-full pl-10" >
                </div>
                <select name="role" aria-label="Filter role" class="field-input sm:w-52">
                    <option value="">Semua Role</option>
                    @foreach(['super_admin' => 'Super Admin', 'admin' => 'Admin', 'owner' => 'Owner', 'tenant' => 'Tenant'] as $r => $rLabel)
                        <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ $rLabel }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary px-4 py-2.5 whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($users as $u)
                <x-mobile-record-card
                    :title="$u->name"
                    :subtitle="$u->email"
                    :meta="[
                        'Dibuat' => $u->created_at->translatedFormat('d M Y'),
                    ]">
                    <x-slot name="badge">
                        <div class="flex flex-col items-end gap-1">
                            <x-status-badge :status="$u->role" context="role" />
                            <x-status-badge :status="$u->is_active ? 'active' : 'inactive'" context="penghuni" :label="$u->is_active ? 'Aktif' : 'Nonaktif'" />
                        </div>
                    </x-slot>
                    <x-slot name="actions">
                        <x-button href="{{ route('super-admin.users.show', $u) }}" type="secondary" size="sm" class="min-h-11">
                            <i class="ri-eye-line"></i> Detail
                        </x-button>
                        <x-button href="{{ route('super-admin.users.edit', $u) }}" type="secondary" size="sm" class="min-h-11">
                            <i class="ri-edit-line"></i> Edit
                        </x-button>
                    </x-slot>
                </x-mobile-record-card>
            @empty
                <div class="card-surface">
                    <x-empty-state icon="ri-team-line" title="Tidak ada user ditemukan"
                                   description="Coba ubah kata kunci pencarian atau filter role." />
                </div>
            @endforelse
        </div>

        <div class="table-card">
            <div class="hidden overflow-x-auto scrollbar-thin md:block">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Role</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="hidden md:table-cell">Dibuat</th>
                            <th scope="col" class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3 min-w-[10rem]">
                                        <span class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 font-bold text-xs flex items-center justify-center shrink-0 uppercase">
                                            {{ mb_substr($u->name, 0, 1) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 dark:text-slate-50 truncate">{{ $u->name }}</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $u->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <x-status-badge :status="$u->role" context="role" />
                                </td>
                                <td class="text-center">
                                    <x-status-badge :status="$u->is_active ? 'active' : 'inactive'" context="penghuni" :label="$u->is_active ? 'Aktif' : 'Nonaktif'" />
                                </td>
                                <td class="hidden md:table-cell text-slate-500 dark:text-slate-400">{{ $u->created_at->translatedFormat('d M Y') }}</td>
                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('super-admin.users.edit', $u) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Edit user" aria-label="Edit {{ $u->name }}">
                                            <i class="ri-edit-line"></i>
                                        </a>

                                        <a href="{{ route('super-admin.users.show', $u) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Lihat user" aria-label="Lihat {{ $u->name }}">
                                            <i class="ri-eye-line"></i>
                                        </a>

                                        <form method="POST" action="{{ route('super-admin.users.destroy', $u) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <x-confirm-dialog
                                                title="Hapus user ini?"
                                                description="{{ $u->name }} akan dihapus permanen beserta data terkait. Tindakan ini tidak dapat dibatalkan."
                                                confirmText="Ya, Hapus"
                                                triggerClass="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                            >
                                                <i class="ri-delete-bin-line"></i>
                                                <x-slot name="actions">
                                                    <button type="submit" class="btn-danger px-4 py-2 text-sm">Ya, Hapus</button>
                                                </x-slot>
                                            </x-confirm-dialog>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-empty-state icon="ri-team-line" title="Tidak ada user ditemukan" description="Coba ubah kata kunci pencarian atau filter role." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-white/[0.06]">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
