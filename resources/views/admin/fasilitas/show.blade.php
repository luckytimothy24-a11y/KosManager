<x-app-layout>
    <x-slot name="header">
        <x-page-header title="{{ $fasilitas->name }}" description="{{ ucfirst($fasilitas->type) }} · {{ $fasilitas->is_active ? 'Aktif' : 'Nonaktif' }}" icon="ri-archive-drawer-line">
            <x-button href="{{ route('admin.fasilitas.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="form-section">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                <div>
                    <dt class="text-label">Nama</dt>
                    <dd class="mt-1 text-value">{{ $fasilitas->name }}</dd>
                </div>
                <div>
                    <dt class="text-label">Tipe</dt>
                    <dd class="mt-1 text-value">{{ ucfirst($fasilitas->type) }}</dd>
                </div>
                <div>
                    <dt class="text-label">Status</dt>
                    <dd class="mt-1"><x-status-badge :status="$fasilitas->is_active ? 'active' : 'inactive'" context="kos" /></dd>
                </div>
                <div>
                    <dt class="text-label">Digunakan</dt>
                    <dd class="mt-1 text-value">{{ $fasilitas->kamar_count }} kamar, {{ $fasilitas->kos_count }} kos</dd>
                </div>
                @if($fasilitas->description)
                    <div class="sm:col-span-2">
                        <dt class="text-label">Deskripsi</dt>
                        <dd class="mt-1 text-slate-700 dark:text-slate-300">{{ $fasilitas->description }}</dd>
                    </div>
                @endif
                @if($fasilitas->icon)
                    <div>
                        <dt class="text-label">Icon</dt>
                        <dd class="mt-1 font-mono text-sm text-slate-700 dark:text-slate-300">{{ $fasilitas->icon }}</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-6 pt-6 border-t border-slate-100 dark:border-white/[0.06] flex items-center gap-3">
                <a href="{{ route('admin.fasilitas.edit', $fasilitas) }}" class="btn-primary">
                    <i class="ri-edit-line"></i> Edit
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
