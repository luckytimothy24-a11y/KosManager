<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Manajemen Users" description="Kelola seluruh pengguna.">
            <x-button href="{{ route('super-admin.users.create') }}" type="primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah User
            </x-button>
        </x-page-header>
    </x-slot>
    <x-alert />
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari user..." class="flex-1 rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                <select name="role" class="rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                    <option value="">Semua Role</option>
                    @foreach(['super_admin','admin','owner','tenant'] as $r)
                        <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-gray-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Nama</th>
                        <th class="px-4 py-3 text-left font-medium">Email</th>
                        <th class="px-4 py-3 text-center font-medium">Role</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($users as $u)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800">
                            <td class="px-4 py-3 font-medium">{{ $u->name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300">{{ $u->email }}</td>
                            <td class="px-4 py-3 text-center">
                                @php $rc = ['super_admin'=>'bg-purple-100 text-purple-800 dark:bg-purple-500/10 dark:text-purple-300','admin'=>'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300','owner'=>'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300','tenant'=>'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100']; @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $rc[$u->role] }}">{{ ucfirst(str_replace('_',' ',$u->role)) }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $u->is_active ? 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('super-admin.users.edit', $u) }}" class="text-yellow-600 hover:text-yellow-800 text-xs">Edit</a>
                                    <form method="POST" action="{{ route('super-admin.users.destroy', $u) }}" onsubmit="return confirm('Yakin?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-3"><x-empty-state icon="ri-team-line" title="Tidak ada user." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $users->links() }}</div>
    </div>
</x-app-layout>
