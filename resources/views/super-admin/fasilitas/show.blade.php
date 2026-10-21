<x-app-layout>
    <x-slot name="header">
        <x-page-header title="{{ $fasilitas->name }}" description="{{ ucfirst($fasilitas->type) }} · {{ $fasilitas->is_active ? 'Aktif' : 'Nonaktif' }}">
            <x-button href="{{ route('super-admin.fasilitas.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nama</dt>
                    <dd class="mt-1 font-medium text-slate-900 dark:text-slate-50">{{ $fasilitas->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tipe</dt>
                    <dd class="mt-1 font-medium text-slate-900 dark:text-slate-50">{{ ucfirst($fasilitas->type) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</dt>
                    <dd class="mt-1"><x-status-badge :status="$fasilitas->is_active ? 'active' : 'inactive'" context="kos" /></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Digunakan</dt>
                    <dd class="mt-1 font-medium text-slate-900 dark:text-slate-50">{{ $fasilitas->kamar_count }} kamar, {{ $fasilitas->kos_count }} kos</dd>
                </div>
                @if($fasilitas->description)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Deskripsi</dt>
                        <dd class="mt-1 text-slate-700 dark:text-slate-300">{{ $fasilitas->description }}</dd>
                    </div>
                @endif
                @if($fasilitas->icon)
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Icon</dt>
                        <dd class="mt-1 font-mono text-sm text-slate-700 dark:text-slate-300">{{ $fasilitas->icon }}</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-6 pt-6 border-t border-slate-100 dark:border-white/10 flex items-center gap-3">
                <a href="{{ route('super-admin.fasilitas.edit', $fasilitas) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 transition">
                    <i class="ri-edit-line"></i> Edit
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
