<x-app-layout>
    @php $isAdminView = auth()->check() && auth()->user()->isAdmin(); $kosPrefix = $isAdminView ? 'admin' : 'owner'; @endphp
    <x-slot name="header">
        <x-page-header title="Kelola Kos" description="{{ $isAdminView ? 'Daftar kos yang ditugaskan kepada Anda.' : 'Daftar seluruh properti kos Anda.' }}" icon="ri-building-2-line">
            @if(!$isAdminView)
            <x-button href="{{ route('owner.kos.create') }}" type="primary">
                <i class="ri-add-line text-base"></i> Tambah Kos
            </x-button>
            @endif
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Filter --}}
        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kos atau alamat..." aria-label="Cari kos"
                           class="field-input pl-10 pr-3.5">
                </div>
                <select name="status" aria-label="Filter status kos" class="field-input sm:w-48">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <button type="submit" class="btn-secondary whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>
        </div>

        {{-- Mobile cards --}}
        <div class="space-y-3 md:hidden">
            @forelse($kos as $item)
                <x-mobile-record-card
                    :title="$item->name"
                    :subtitle="$item->address"
                    :meta="[
                        'Kamar' => $item->kamar_count . ' kamar',
                        'Penghuni' => $item->penghunis_count . ' orang',
                    ]">
                    <x-slot name="badge">
                        <x-status-badge :status="$item->status" context="kos" />
                    </x-slot>
                    <x-slot name="actions">
                        <x-button href="{{ route($kosPrefix . '.kos.show', $item) }}" type="secondary" size="sm" class="min-h-11">
                            <i class="ri-eye-line"></i> Detail
                        </x-button>
                        @if(!$isAdminView)
                            <x-button href="{{ route('owner.kos.edit', $item) }}" type="secondary" size="sm" class="min-h-11">
                                <i class="ri-edit-line"></i> Edit
                            </x-button>
                        @endif
                    </x-slot>
                </x-mobile-record-card>
            @empty
                <div class="card-surface">
                    <x-empty-state icon="ri-building-2-line" title="Belum ada data kos"
                                   description="{{ $isAdminView ? 'Belum ada Kos yang ditugaskan kepada Anda.' : 'Belum ada properti kos yang Anda kelola.' }}" />
                </div>
            @endforelse
            @if($kos->hasPages())
                <div>{{ $kos->links() }}</div>
            @endif
        </div>

        {{-- Table --}}
        <div class="table-card hidden md:block">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th>Kos</th>
                            <th class="text-center">Kamar</th>
                            <th class="text-center">Penghuni</th>
                            <th class="text-center hidden sm:table-cell">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kos as $item)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3 min-w-[12rem] max-w-xs">
                                        @if($item->photo && @file_exists(public_path('storage/'.$item->photo)))
                                            <img src="{{ asset('storage/'.$item->photo) }}" alt="Foto {{ $item->name }}"
                                                 class="w-11 h-11 rounded-xl object-cover shrink-0 bg-slate-100 dark:bg-white/[0.06]">
                                        @else
                                            <span class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                                                <i class="ri-building-2-line text-lg text-primary-500"></i>
                                            </span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 dark:text-slate-50 truncate">{{ $item->name }}</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate flex items-center gap-1">
                                                <i class="ri-map-pin-line text-[10px] shrink-0"></i> {{ $item->address }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300">
                                        {{ $item->kamar_count }} kamar
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300">
                                        {{ $item->penghunis_count }} orang
                                    </span>
                                </td>
                                <td class="text-center hidden sm:table-cell">
                                    <x-status-badge :status="$item->status" context="kos" />
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route("$kosPrefix.kos.show", $item) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Detail" aria-label="Detail {{ $item->name }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if(!$isAdminView)
                                        <a href="{{ route("owner.kos.edit", $item) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Edit" aria-label="Edit {{ $item->name }}">
                                            <i class="ri-edit-line"></i>
                                        </a>

                                        <x-confirm-dialog
                                            title="Hapus kos ini?"
                                            description="Kos &ldquo;{{ $item->name }}&rdquo; beserta data terkait akan dihapus permanen. Tindakan ini tidak dapat dibatalkan."
                                            confirmText="Ya, Hapus"
                                            triggerClass="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                            aria-label="Hapus {{ $item->name }}"
                                        >
                                            <x-slot name="slot"><i class="ri-delete-bin-line"></i></x-slot>
                                            <x-slot name="actions">
                                                <form method="POST" action="{{ route('owner.kos.destroy', $item) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" :disabled="submitting"
                                                            class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                        <span x-show="!submitting">Ya, Hapus</span>
                                                        <span x-show="submitting" x-cloak>Menghapus...</span>
                                                    </button>
                                                </form>
                                            </x-slot>
                                        </x-confirm-dialog>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state icon="ri-building-2-line" title="Belum ada data kos"
                                                   description="{{ $isAdminView ? 'Belum ada Kos yang ditugaskan kepada Anda.' : 'Belum ada properti kos yang Anda kelola.' }}">
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-white/[0.06]">{{ $kos->links() }}</div>
        </div>
    </div>
</x-app-layout>
