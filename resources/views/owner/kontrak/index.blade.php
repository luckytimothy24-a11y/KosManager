<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Kontrak" description="Kelola kontrak sewa aktif dan riwayatnya." icon="ri-file-text-line" />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Filter --}}
        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor kontrak atau nama penghuni..." aria-label="Cari kontrak"
                           class="field-input pl-10 pr-3.5">
                </div>
                <select name="status" aria-label="Filter status kontrak" class="field-input sm:w-48">
                    <option value="">Semua Status</option>
                    @foreach(['active','expired','terminated'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \StatusLabels::kontrakLabel($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>
        </div>

        {{-- Mobile cards --}}
        <div class="space-y-3 md:hidden">
            @forelse($kontraks as $k)
                <x-mobile-record-card
                    :title="$k->contract_number"
                    :subtitle="($k->penghuni->user->name ?? '-') . ' · ' . ($k->kos->name ?? '-')"
                    :meta="[
                        'Kamar' => $k->kamar->room_number ?? '-',
                        'Periode' => \Illuminate\Support\Carbon::parse($k->start_date)->format('d M Y') . ' — ' . \Illuminate\Support\Carbon::parse($k->end_date)->format('d M Y'),
                        'Harga' => 'Rp ' . number_format($k->rental_price, 0, ',', '.'),
                    ]">
                    <x-slot name="badge">
                        <x-status-badge :status="$k->status" context="kontrak" />
                    </x-slot>
                    <x-slot name="actions">
                        <x-button href="{{ route($prefix . '.kontrak.show', $k) }}" type="secondary" size="sm" class="min-h-11">
                            <i class="ri-eye-line"></i> Detail
                        </x-button>
                    </x-slot>
                </x-mobile-record-card>
            @empty
                <div class="card-surface">
                    <x-empty-state icon="ri-file-text-line" title="Belum ada kontrak"
                                   description="Kontrak sewa akan dibuat otomatis setelah check-in disetujui." />
                </div>
            @endforelse
            @if($kontraks->hasPages())
                <div>{{ $kontraks->links() }}</div>
            @endif
        </div>

        {{-- Table --}}
        <div class="table-card hidden md:block">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th>No. Kontrak</th>
                            <th class="hidden md:table-cell">Penghuni</th>
                            <th>Kamar</th>
                            <th class="hidden sm:table-cell">Periode</th>
                            <th class="text-right">Harga</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kontraks as $k)
                            <tr>
                                <td>
                                    <p class="font-mono font-semibold text-slate-900 dark:text-slate-50">{{ $k->contract_number }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ \StatusLabels::rentalTypeLabel($k->rental_type) }}</p>
                                </td>
                                <td class="hidden md:table-cell">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($k->penghuni->user->name, 0, 1)) }}</span>
                                        <span class="text-slate-700 dark:text-slate-200 truncate max-w-[10rem]">{{ $k->penghuni->user->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <p class="font-medium text-slate-900 dark:text-slate-50">{{ $k->kamar->room_number }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[10rem]">{{ $k->kos->name }}</p>
                                </td>
                                <td class="hidden sm:table-cell">
                                    <p class="text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($k->start_date)->format('d M Y') }} — {{ \Illuminate\Support\Carbon::parse($k->end_date)->format('d M Y') }}</p>
                                </td>
                                <td class="text-right font-medium text-slate-900 dark:text-slate-50 whitespace-nowrap">Rp {{ number_format($k->rental_price, 0, ',', '.') }}</td>
                                <td class="text-center"><x-status-badge :status="$k->status" context="kontrak" /></td>
                                <td>
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route("$prefix.kontrak.show", $k) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition"
                                           title="Detail" aria-label="Detail kontrak {{ $k->contract_number }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state icon="ri-file-text-line" title="Belum ada kontrak"
                                                   description="Kontrak sewa akan dibuat otomatis setelah check-in disetujui." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-white/[0.06]">{{ $kontraks->links() }}</div>
        </div>
    </div>
</x-app-layout>
