<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Penghuni" description="Kelola data penghuni." />
    </x-slot>
    <x-alert />
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari penghuni..." class="flex-1 rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                <select name="status" class="rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-gray-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Nama</th>
                        <th class="px-4 py-3 text-left font-medium">Kamar</th>
                        <th class="px-4 py-3 text-left font-medium">Kos</th>
                        <th class="px-4 py-3 text-left font-medium">Telepon</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($penghunis as $p)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800">
                            <td class="px-4 py-3 font-medium">{{ $p->user->name }}</td>
                            <td class="px-4 py-3">{{ $p->kamar->room_number }}</td>
                            <td class="px-4 py-3">{{ $p->kos->name }}</td>
                            <td class="px-4 py-3">{{ $p->phone }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $p->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300' : 'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100' }}">{{ ucfirst($p->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right"><a href="{{ route("$prefix.penghuni.show", $p) }}" class="text-blue-600 hover:text-blue-800 text-xs">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-3"><x-empty-state icon="ri-user-star-line" title="Belum ada penghuni." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $penghunis->links() }}</div>
    </div>
</x-app-layout>
