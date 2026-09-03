<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Manajemen User" description="Kelola seluruh pengguna platform KosManager.">
            <x-button href="{{ route('super-admin.users.create') }}" type="primary">
                <i class="ri-add-line text-base"></i> Tambah User
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
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email user..." aria-label="Cari user"
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <select name="role" aria-label="Filter role"
                            class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Semua Role</option>
                        @foreach(['super_admin' => 'Super Admin', 'admin' => 'Admin', 'owner' => 'Owner', 'tenant' => 'Tenant'] as $r => $rLabel)
                            <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ $rLabel }}</option>
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
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">User</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">Role</th>
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden md:table-cell">Dibuat</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($users as $u)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3 min-w-[10rem]">
                                        <span class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 font-bold text-xs flex items-center justify-center shrink-0 uppercase">
                                            {{ mb_substr($u->name, 0, 1) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 dark:text-white truncate">{{ $u->name }}</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $u->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    @php
                                        $roleBadge = [
                                            'super_admin' => ['bg-purple-100 text-purple-800 dark:bg-purple-500/10 dark:text-purple-300', 'ri-vip-crown-line'],
                                            'admin' => ['bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300', 'ri-user-settings-line'],
                                            'owner' => ['bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300', 'ri-building-2-line'],
                                            'tenant' => ['bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100', 'ri-user-line'],
                                        ][$u->role] ?? ['bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100', 'ri-user-line'];
                                        $roleLabel = ['super_admin' => 'Super Admin', 'admin' => 'Admin', 'owner' => 'Owner', 'tenant' => 'Tenant'][$u->role] ?? ucfirst($u->role);
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $roleBadge[0] }}">
                                        <i class="{{ $roleBadge[1] }} text-[11px]"></i> {{ $roleLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $u->is_active ? 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $u->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                        {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 hidden md:table-cell text-slate-500 dark:text-slate-400">{{ $u->created_at->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('super-admin.users.edit', $u) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Edit user" aria-label="Edit {{ $u->name }}">
                                            <i class="ri-edit-line"></i>
                                        </a>

                                        <x-confirm-dialog
                                            title="Hapus user ini?"
                                            description="{{ $u->name }} akan dihapus permanen beserta data terkait. Tindakan ini tidak dapat dibatalkan."
                                            confirmText="Ya, Hapus"
                                            triggerClass="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                            aria-label="Hapus {{ $u->name }}"
                                        >
                                            <x-slot name="slot"><i class="ri-delete-bin-line"></i></x-slot>
                                            <x-slot name="actions">
                                                <form method="POST" action="{{ route('super-admin.users.destroy', $u) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" :disabled="submitting"
                                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition shadow-sm shadow-red-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
                                                        <span x-show="!submitting">Ya, Hapus</span>
                                                        <span x-show="submitting" x-cloak>Menghapus...</span>
                                                    </button>
                                                </form>
                                            </x-slot>
                                        </x-confirm-dialog>
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
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
