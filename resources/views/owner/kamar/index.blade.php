<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $canManage = Auth::user()->hasRole('owner', 'super_admin'); @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Kamar" description="Kelola data kamar di seluruh properti Anda." icon="ri-door-open-line">
            @if($canManage)
                <x-button href="{{ route('owner.kamar.create', array_filter(['kos_id' => request('kos_id')])) }}" type="primary">
                    <i class="ri-add-line text-base"></i> Tambah Kamar
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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor atau nama kamar..." aria-label="Cari kamar"
                           class="field-input pl-10 pr-3.5">
                </div>
                <select name="kos_id" aria-label="Filter kos" class="field-input sm:w-48">
                    <option value="">Semua Kos</option>
                    @foreach($kosList as $k)
                        <option value="{{ $k->id }}" {{ request('kos_id') == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                    @endforeach
                </select>
                <select name="status" aria-label="Filter status kamar" class="field-input sm:w-48">
                    <option value="">Semua Status</option>
                    @foreach(['available', 'booked', 'occupied', 'maintenance'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \StatusLabels::kamarLabel($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-card">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th>Kamar</th>
                            <th class="hidden md:table-cell">Kos</th>
                            <th class="text-center hidden sm:table-cell">Mode</th>
                            <th class="text-right">Harga/Bulan</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kamars as $kamar)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3 min-w-[10rem]">
                                        <span class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                                            <i class="ri-door-open-line text-base text-primary-500"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 dark:text-slate-50">{{ $kamar->room_number }}</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[12rem]">{{ $kamar->room_name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell">
                                    {{ $kamar->kos->name }}
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $kamar->room_type }}</p>
                                </td>
                                <td class="text-center hidden sm:table-cell">
                                    <x-status-badge :status="$kamar->billing_mode ?? 'personal'" context="billing_mode" />
                                </td>
                                <td class="text-right">
                                    <p class="font-semibold text-slate-900 dark:text-slate-50">Rp {{ number_format($kamar->monthly_price, 0, ',', '.') }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">Rp {{ number_format($kamar->daily_price, 0, ',', '.') }}/hari · Rp {{ number_format($kamar->weekly_price, 0, ',', '.') }}/mgg</p>
                                </td>
                                <td class="text-center"><x-status-badge :status="$kamar->status" context="kamar" /></td>
                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route("$prefix.kamar.show", $kamar) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Detail" aria-label="Detail kamar {{ $kamar->room_number }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if($canManage)
                                            <a href="{{ route("$prefix.kamar.edit", $kamar) }}"
                                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Edit" aria-label="Edit kamar {{ $kamar->room_number }}">
                                                <i class="ri-edit-line"></i>
                                            </a>

                                            <x-confirm-dialog
                                                title="Hapus kamar ini?"
                                                description="Kamar &ldquo;{{ $kamar->room_number }}&rdquo; akan dihapus permanen. Tindakan ini tidak dapat dibatalkan."
                                                confirmText="Ya, Hapus"
                                                triggerClass="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                                aria-label="Hapus kamar {{ $kamar->room_number }}"
                                            >
                                                <x-slot name="slot"><i class="ri-delete-bin-line"></i></x-slot>
                                                <x-slot name="actions">
                                                    <form method="POST" action="{{ route("$prefix.kamar.destroy", $kamar) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
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
                                <td colspan="6">
                                    <x-empty-state icon="ri-door-open-line" title="Belum ada data kamar"
                                                   description="Tambahkan kamar untuk mulai menerima booking penghuni.">
                                        @if($canManage)
                                            <x-button href="{{ route('owner.kamar.create') }}" type="primary"><i class="ri-add-line"></i> Tambah Kamar</x-button>
                                        @endif
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-white/[0.06]">{{ $kamars->links() }}</div>
        </div>
    </div>
</x-app-layout>
