<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $canManage = Auth::user()->hasRole('owner', 'super_admin'); @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Kamar" description="Kelola data kamar di seluruh properti Anda.">
            @if($canManage)
                <x-button href="{{ route('owner.kamar.create', array_filter(['kos_id' => request('kos_id')])) }}" type="primary">
                    <i class="ri-add-line text-base"></i> Tambah Kamar
                </x-button>
            @endif
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
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor atau nama kamar..." aria-label="Cari kamar"
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <select name="kos_id" aria-label="Filter kos"
                            class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Semua Kos</option>
                        @foreach($kosList as $k)
                            <option value="{{ $k->id }}" {{ request('kos_id') == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                        @endforeach
                    </select>
                    <select name="status" aria-label="Filter status kamar"
                            class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Semua Status</option>
                        @foreach(['available', 'booked', 'occupied', 'maintenance'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \StatusLabels::kamarLabel($s) }}</option>
                        @endforeach
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
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">Kamar</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden md:table-cell">Kos</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Harga/Bulan</th>
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($kamars as $kamar)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3 min-w-[10rem]">
                                        <span class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                                            <i class="ri-door-open-line text-base text-primary-500"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 dark:text-white">{{ $kamar->room_number }}</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[12rem]">{{ $kamar->room_name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300 hidden md:table-cell">
                                    {{ $kamar->kos->name }}
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $kamar->room_type }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <p class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($kamar->monthly_price, 0, ',', '.') }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">Rp {{ number_format($kamar->daily_price, 0, ',', '.') }}/hari</p>
                                </td>
                                <td class="px-4 py-3.5 text-center"><x-status-badge :status="$kamar->status" context="kamar" /></td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route("$prefix.kamar.show", $kamar) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Detail" aria-label="Detail kamar {{ $kamar->room_number }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if($canManage)
                                            <a href="{{ route("$prefix.kamar.edit", $kamar) }}"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Edit" aria-label="Edit kamar {{ $kamar->room_number }}">
                                                <i class="ri-edit-line"></i>
                                            </a>

                                            <x-confirm-dialog
                                                title="Hapus kamar ini?"
                                                description="Kamar &ldquo;{{ $kamar->room_number }}&rdquo; akan dihapus permanen. Tindakan ini tidak dapat dibatalkan."
                                                confirmText="Ya, Hapus"
                                                triggerClass="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                                aria-label="Hapus kamar {{ $kamar->room_number }}"
                                            >
                                                <x-slot name="slot"><i class="ri-delete-bin-line"></i></x-slot>
                                                <x-slot name="content">
                                                    <form method="POST" action="{{ route("$prefix.kamar.destroy", $kamar) }}" id="delete-kamar-{{ $kamar->id }}">
                                                        @csrf @method('DELETE')
                                                    </form>
                                                </x-slot>
                                                <x-slot name="actions">
                                                    <button type="submit" form="delete-kamar-{{ $kamar->id }}"
                                                            class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">
                                                        Ya, Hapus
                                                    </button>
                                                </x-slot>
                                            </x-confirm-dialog>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
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

            @if($kamars->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $kamars->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
