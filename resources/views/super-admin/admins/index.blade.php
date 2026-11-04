<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Admin Management" description="Kelola akun admin dan penugasan kos." icon="ri-user-settings-line">
            <x-button href="{{ route('super-admin.admins.create') }}" type="primary">
                <i class="ri-add-line text-base"></i> Tambah Admin
            </x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau telepon..." aria-label="Cari admin"
                           class="field-input w-full pl-10">
                </div>
                <select name="status" aria-label="Filter status" class="field-input sm:w-44">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <select name="has_assignment" aria-label="Filter assignment" class="field-input sm:w-44">
                    <option value="">Semua</option>
                    <option value="1" {{ request('has_assignment') === '1' ? 'selected' : '' }}>Memiliki Kos</option>
                </select>
                <button type="submit" class="btn-secondary px-4 py-2.5 whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($admins as $admin)
                <x-mobile-record-card
                    :title="$admin->name"
                    :subtitle="$admin->email"
                    :meta="[
                        'Telepon' => $admin->phone ?? '-',
                        'Kos assigned' => $admin->assigned_kos_count ?? 0,
                        'Daftar' => $admin->created_at->translatedFormat('d M Y'),
                    ]">
                    <x-slot name="badge">
                        <x-status-badge :status="$admin->is_active ? 'active' : 'inactive'" context="penghuni" :label="$admin->is_active ? 'Aktif' : 'Nonaktif'" />
                    </x-slot>
                    <x-slot name="actions">
                        <x-button href="{{ route('super-admin.admins.show', $admin) }}" type="secondary" size="sm" class="min-h-11">
                            <i class="ri-eye-line"></i> Detail
                        </x-button>
                    </x-slot>
                </x-mobile-record-card>
            @empty
                <div class="card-surface">
                    <x-empty-state icon="ri-user-settings-line" title="Tidak ada admin ditemukan"
                                   description="Coba ubah kata kunci pencarian atau filter." />
                </div>
            @endforelse
        </div>

        <div class="table-card">
            <div class="hidden overflow-x-auto scrollbar-thin md:block">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th scope="col">Admin</th>
                            <th scope="col" class="hidden md:table-cell">Telepon</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-center">Kos Assigned</th>
                            <th scope="col" class="hidden md:table-cell">Daftar</th>
                            <th scope="col" class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3 min-w-[10rem]">
                                        <a href="{{ route('super-admin.admins.show', $admin) }}"
                                           class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold text-xs flex items-center justify-center shrink-0 uppercase"
                                           title="Lihat {{ $admin->name }}" aria-label="Lihat {{ $admin->name }}">
                                            {{ mb_substr($admin->name, 0, 1) }}
                                        </a>
                                        <div class="min-w-0">
                                            <a href="{{ route('super-admin.admins.show', $admin) }}"
                                               class="block font-semibold text-slate-900 dark:text-slate-50 truncate hover:text-primary-600 dark:hover:text-primary-400 transition">
                                                {{ $admin->name }}
                                            </a>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $admin->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell text-slate-600 dark:text-slate-400">{{ $admin->phone ?? '-' }}</td>
                                <td class="text-center">
                                    <x-status-badge :status="$admin->is_active ? 'active' : 'inactive'" context="penghuni" :label="$admin->is_active ? 'Aktif' : 'Nonaktif'" />
                                </td>
                                <td class="text-center">{{ $admin->assigned_kos_count ?? 0 }}</td>
                                <td class="hidden md:table-cell text-xs text-slate-500 dark:text-slate-400">{{ $admin->created_at->translatedFormat('d M Y') }}</td>
                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('super-admin.admins.show', $admin) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Lihat" aria-label="Lihat {{ $admin->name }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if($admin->is_active)
                                            <form method="POST" action="{{ route('super-admin.admins.deactivate', $admin) }}" class="inline">
                                                @csrf @method('POST')
                                                <x-confirm-dialog
                                                    title="Nonaktifkan admin ini?"
                                                    description="{{ $admin->name }} akan kehilangan akses login."
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
                                            <form method="POST" action="{{ route('super-admin.admins.activate', $admin) }}">
                                                @csrf @method('POST')
                                                <button type="submit"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition"
                                                        title="Aktifkan" aria-label="Aktifkan {{ $admin->name }}">
                                                    <i class="ri-power-on-line"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state icon="ri-user-settings-line" title="Tidak ada admin ditemukan" description="Coba ubah kata kunci pencarian atau filter." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($admins->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-white/[0.06]">
                    {{ $admins->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
