<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Manajemen Fasilitas" description="Master fasilitas umum Kos dan fasilitas Kamar." icon="ri-archive-drawer-line">
            <x-button href="{{ route('super-admin.fasilitas.create') }}" type="primary">
                <i class="ri-add-line text-base"></i> Tambah Fasilitas
            </x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="card-surface">
            <div class="px-4 sm:px-5 pt-4">
                <nav class="flex flex-wrap gap-1.5" aria-label="Tipe fasilitas">
                    <a href="{{ route('super-admin.fasilitas.index', ['type' => 'kamar']) }}"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ ($type ?? 'kamar') === 'kamar' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                        <i class="ri-door-open-line"></i> Fasilitas Kamar
                        <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full {{ ($type ?? 'kamar') === 'kamar' ? 'bg-primary-100 dark:bg-primary-500/20 text-primary-700 dark:text-primary-300' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-400' }}">{{ $totalKamar }}</span>
                    </a>
                    <a href="{{ route('super-admin.fasilitas.index', ['type' => 'kos']) }}"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ $type === 'kos' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                        <i class="ri-home-4-line"></i> Fasilitas Umum Kos
                        <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full {{ $type === 'kos' ? 'bg-primary-100 dark:bg-primary-500/20 text-primary-700 dark:text-primary-300' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-400' }}">{{ $totalKos }}</span>
                    </a>
                </nav>
            </div>

            <div class="p-4 sm:p-5">
                <div class="table-card">
                    <div class="overflow-x-auto scrollbar-thin">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th scope="col">Fasilitas</th>
                                    <th scope="col" class="text-center">Status</th>
                                    @if($type === 'kamar')
                                    <th scope="col" class="text-center">Dipakai di Kamar</th>
                                    @else
                                    <th scope="col" class="text-center">Dipakai di Kos</th>
                                    @endif
                                    <th scope="col" class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fasilitas as $f)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                @if($f->icon)
                                                    <span class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                                                        <i class="{{ str_starts_with($f->icon, 'ri-') ? $f->icon : 'ri-'.$f->icon.'-line' }} text-primary-600 dark:text-primary-400"></i>
                                                    </span>
                                                @else
                                                    <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-white/[0.06] flex items-center justify-center shrink-0">
                                                        <i class="ri-price-tag-line text-slate-400 dark:text-slate-500"></i>
                                                    </span>
                                                @endif
                                                <div>
                                                    <span class="font-semibold text-slate-900 dark:text-slate-50">{{ $f->name }}</span>
                                                    @if($f->description)
                                                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ $f->description }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <x-status-badge :status="$f->is_active ? 'active' : 'inactive'" context="kos" />
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300">
                                                <i class="{{ $type === 'kos' ? 'ri-home-4-line' : 'ri-door-open-line' }} text-[11px]"></i> {{ $type === 'kos' ? $f->kos_count : $f->kamar_count }} digunakan
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-end gap-1.5">
                                                <a href="{{ route('super-admin.fasilitas.edit', $f) }}"
                                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Edit fasilitas" aria-label="Edit {{ $f->name }}">
                                                    <i class="ri-edit-line"></i>
                                                </a>

                                                <form method="POST" action="{{ route('super-admin.fasilitas.destroy', $f) }}" class="inline">
                                                    @csrf @method('DELETE')
                                                    <x-confirm-dialog
                                                        title="Hapus fasilitas ini?"
                                                        description="Fasilitas &ldquo;{{ $f->name }}&rdquo; akan dilepas dari semua kos/kamar yang menggunakannya."
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
                                    <tr><td colspan="4"><x-empty-state icon="ri-archive-drawer-line" title="Belum ada fasilitas" description="Tambahkan fasilitas agar bisa dipilih saat mengelola kos atau kamar." /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($fasilitas->hasPages())
                        <div class="p-4 border-t border-slate-100 dark:border-white/[0.06]">
                            {{ $fasilitas->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
