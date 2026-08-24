<x-app-layout>
    @php $prefix = 'owner'; @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Kos" description="Kelola data kos Anda.">
            <x-button href="{{ route('owner.kos.create') }}" type="primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Kos
            </x-button>
        </x-page-header>
    </x-slot>

    <x-alert />

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kos..."
                       class="flex-1 rounded-lg border-gray-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 text-sm">
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
                        <th class="px-4 py-3 text-left font-medium">Nama Kos</th>
                        <th class="px-4 py-3 text-left font-medium">Alamat</th>
                        <th class="px-4 py-3 text-left font-medium">Telepon</th>
                        <th class="px-4 py-3 text-center font-medium">Kamar</th>
                        <th class="px-4 py-3 text-center font-medium">Penghuni</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($kos as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $item->name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300 max-w-xs truncate">{{ $item->address }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300">{{ $item->phone }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300">
                                    {{ $item->kamar_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300">
                                    {{ $item->penghunis_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300' : 'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route("$prefix.kos.show", $item) }}" class="text-blue-600 hover:text-blue-800 text-xs">Detail</a>
                                    <a href="{{ route("$prefix.kos.edit", $item) }}" class="text-yellow-600 hover:text-yellow-800 text-xs">Edit</a>
                                    <form method="POST" action="{{ route("$prefix.kos.destroy", $item) }}" onsubmit="return confirm('Yakin ingin menghapus kos ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-3"><x-empty-state icon="ri-building-2-line" title="Belum ada data kos." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 dark:border-slate-700">
            {{ $kos->links() }}
        </div>
    </div>
</x-app-layout>
