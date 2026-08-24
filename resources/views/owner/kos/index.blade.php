<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Kelola Kos" description="Daftar seluruh properti kos Anda.">
            <x-button href="{{ route('owner.kos.create') }}" type="primary">
                <i class="ri-add-line text-base"></i> Tambah Kos
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
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kos atau alamat..." aria-label="Cari kos"
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <select name="status" aria-label="Filter status kos"
                            class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
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
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">Kos</th>
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold">Kamar</th>
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold">Penghuni</th>
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold hidden sm:table-cell">Status</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($kos as $item)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3 min-w-[12rem] max-w-xs">
                                        @if($item->photo && @file_exists(public_path('storage/'.$item->photo)))
                                            <img src="{{ asset('storage/'.$item->photo) }}" alt="Foto {{ $item->name }}"
                                                 class="w-11 h-11 rounded-xl object-cover shrink-0 bg-slate-100 dark:bg-slate-800">
                                        @else
                                            <span class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                                                <i class="ri-building-2-line text-lg text-primary-500"></i>
                                            </span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 dark:text-white truncate">{{ $item->name }}</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate flex items-center gap-1">
                                                <i class="ri-map-pin-line text-[10px] shrink-0"></i> {{ $item->address }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300">
                                        {{ $item->kamar_count }} kamar
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300">
                                        {{ $item->penghunis_count }} orang
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center hidden sm:table-cell">
                                    <x-status-badge :status="$item->status" context="kos" />
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route("owner.kos.show", $item) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Detail" aria-label="Detail {{ $item->name }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        <a href="{{ route("owner.kos.edit", $item) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Edit" aria-label="Edit {{ $item->name }}">
                                            <i class="ri-edit-line"></i>
                                        </a>

                                        <x-confirm-dialog
                                            title="Hapus kos ini?"
                                            description="Kos &ldquo;{{ $item->name }}&rdquo; beserta data terkait akan dihapus permanen. Tindakan ini tidak dapat dibatalkan."
                                            confirmText="Ya, Hapus"
                                            triggerClass="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                            aria-label="Hapus {{ $item->name }}"
                                        >
                                            <x-slot name="slot"><i class="ri-delete-bin-line"></i></x-slot>
                                            <x-slot name="content">
                                                <form method="POST" action="{{ route('owner.kos.destroy', $item) }}" id="delete-kos-{{ $item->id }}">
                                                    @csrf @method('DELETE')
                                                </form>
                                            </x-slot>
                                            <x-slot name="actions">
                                                <button type="submit" form="delete-kos-{{ $item->id }}"
                                                        class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">
                                                    Ya, Hapus
                                                </button>
                                            </x-slot>
                                        </x-confirm-dialog>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state icon="ri-building-2-line" title="Belum ada data kos"
                                                   description="Tambahkan kos pertama Anda untuk mulai mengelola kamar dan penghuni.">
                                        <x-button href="{{ route('owner.kos.create') }}" type="primary"><i class="ri-add-line"></i> Tambah Kos</x-button>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($kos->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $kos->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
