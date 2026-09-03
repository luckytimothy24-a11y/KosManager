<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Manajemen Fasilitas" description="Master fasilitas umum Kos dan fasilitas Kamar.">
            <x-button href="{{ route('super-admin.fasilitas.create') }}" type="primary">
                <i class="ri-add-line text-base"></i> Tambah Fasilitas
            </x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('super-admin.fasilitas.index', ['type' => 'kamar']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition {{ ($type ?? 'kamar') === 'kamar' ? 'bg-primary-500 text-white shadow-sm shadow-primary-500/30' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <i class="ri-door-open-line"></i> Fasilitas Kamar
                <span class="text-xs px-1.5 py-0.5 rounded-full {{ ($type ?? 'kamar') === 'kamar' ? 'bg-white/20' : 'bg-slate-100 dark:bg-slate-800' }}">{{ $totalKamar }}</span>
            </a>
            <a href="{{ route('super-admin.fasilitas.index', ['type' => 'kos']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition {{ $type === 'kos' ? 'bg-primary-500 text-white shadow-sm shadow-primary-500/30' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <i class="ri-home-4-line"></i> Fasilitas Umum Kos
                <span class="text-xs px-1.5 py-0.5 rounded-full {{ $type === 'kos' ? 'bg-white/20' : 'bg-slate-100 dark:bg-slate-800' }}">{{ $totalKos }}</span>
            </a>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">Fasilitas</th>
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold">Status</th>
                            @if($type === 'kamar')
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold">Dipakai di Kamar</th>
                            @else
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold">Dipakai di Kos</th>
                            @endif
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($fasilitas as $f)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        @if($f->icon)
                                            <span class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                                                <i class="{{ str_starts_with($f->icon, 'ri-') ? $f->icon : 'ri-'.$f->icon.'-line' }} text-primary-600 dark:text-primary-400"></i>
                                            </span>
                                        @else
                                            <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0">
                                                <i class="ri-price-tag-line text-slate-400 dark:text-slate-500"></i>
                                            </span>
                                        @endif
                                        <div>
                                            <span class="font-semibold text-slate-900 dark:text-white">{{ $f->name }}</span>
                                            @if($f->description)
                                                <p class="text-xs text-slate-400 dark:text-slate-500">{{ $f->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <x-status-badge :status="$f->is_active ? 'active' : 'inactive'" context="kos" />
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300">
                                        <i class="{{ $type === 'kos' ? 'ri-home-4-line' : 'ri-door-open-line' }} text-[11px]"></i> {{ $type === 'kos' ? $f->kos_count : $f->kamar_count }} digunakan
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('super-admin.fasilitas.edit', $f) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Edit fasilitas" aria-label="Edit {{ $f->name }}">
                                            <i class="ri-edit-line"></i>
                                        </a>

                                        <x-confirm-dialog
                                            title="Hapus fasilitas ini?"
                                            description="Fasilitas &ldquo;{{ $f->name }}&rdquo; akan dilepas dari semua kos/kamar yang menggunakannya."
                                            confirmText="Ya, Hapus"
                                            triggerClass="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                            aria-label="Hapus {{ $f->name }}"
                                        >
                                            <x-slot name="slot"><i class="ri-delete-bin-line"></i></x-slot>
                                            <x-slot name="actions">
                                                <form method="POST" action="{{ route('super-admin.fasilitas.destroy', $f) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
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
                            <tr><td colspan="4"><x-empty-state icon="ri-archive-drawer-line" title="Belum ada fasilitas" description="Tambahkan fasilitas agar bisa dipilih saat mengelola kos atau kamar." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($fasilitas->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $fasilitas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
