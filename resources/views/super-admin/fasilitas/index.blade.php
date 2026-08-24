<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Manajemen Fasilitas" description="Kelola data fasilitas.">
            <x-button href="{{ route('super-admin.fasilitas.create') }}" type="primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah
            </x-button>
        </x-page-header>
    </x-slot>
    <x-alert />
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-gray-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Nama</th>
                        <th class="px-4 py-3 text-center font-medium">Jumlah Kamar</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($fasilitas as $f)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800">
                            <td class="px-4 py-3 font-medium">{{ $f->name }}</td>
                            <td class="px-4 py-3 text-center"><span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300">{{ $f->kamar_count }}</span></td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('super-admin.fasilitas.edit', $f) }}" class="text-yellow-600 hover:text-yellow-800 text-xs">Edit</a>
                                    <form method="POST" action="{{ route('super-admin.fasilitas.destroy', $f) }}" onsubmit="return confirm('Yakin?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-3"><x-empty-state icon="ri-archive-drawer-line" title="Belum ada data." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $fasilitas->links() }}</div>
    </div>
</x-app-layout>
