<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Booking" description="Kelola permintaan booking kamar dari calon penghuni." icon="ri-calendar-check-line" />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Filter --}}
        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode booking atau nama..." aria-label="Cari booking"
                           class="field-input pl-10 pr-3.5">
                </div>
                <select name="status" aria-label="Filter status booking" class="field-input sm:w-48">
                    <option value="">Semua Status</option>
                    @foreach(['pending','approved','rejected','cancelled','completed'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \StatusLabels::bookingLabel($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>
        </div>

        {{-- Mobile cards --}}
        <div class="space-y-3 md:hidden">
            @forelse($bookings as $b)
                <x-mobile-record-card
                    :title="$b->booking_code"
                    :subtitle="($b->user->name ?? '-') . ' · ' . ($b->kos->name ?? '-')"
                    :meta="[
                        'Kamar' => $b->kamar->room_number ?? '-',
                        'Periode' => ($b->start_date?->format('d M Y') ?? '-') . ' — ' . ($b->end_date?->format('d M Y') ?? '-'),
                        'Harga' => 'Rp ' . number_format($b->price, 0, ',', '.'),
                    ]">
                    <x-slot name="badge">
                        <x-status-badge :status="$b->status" context="booking" />
                    </x-slot>
                    <x-slot name="actions">
                        <x-button href="{{ route($prefix . '.booking.show', $b) }}" type="secondary" size="sm" class="min-h-11">
                            <i class="ri-eye-line"></i> Detail
                        </x-button>
                    </x-slot>
                </x-mobile-record-card>
            @empty
                <div class="card-surface">
                    <x-empty-state icon="ri-calendar-check-line" title="Belum ada booking"
                                   description="Booking dari calon penyewa akan muncul di sini." />
                </div>
            @endforelse
            @if($bookings->hasPages())
                <div>{{ $bookings->links() }}</div>
            @endif
        </div>

        {{-- Table --}}
        <div class="table-card hidden md:block">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th class="hidden md:table-cell">Penyewa</th>
                            <th>Kamar</th>
                            <th class="text-right">Harga</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $b)
                            <tr>
                                <td>
                                    <p class="font-mono font-semibold text-slate-900 dark:text-slate-50">{{ $b->booking_code }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $b->start_date?->format('d M Y') ?? $b->start_date }} s/d {{ $b->end_date?->format('d M Y') ?? $b->end_date }}</p>
                                </td>
                                <td class="hidden md:table-cell">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($b->user->name, 0, 1)) }}</span>
                                        <span class="text-slate-700 dark:text-slate-200 truncate max-w-[10rem]">{{ $b->user->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <p class="font-medium text-slate-900 dark:text-slate-50">{{ $b->kamar->room_number }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[10rem]">{{ $b->kos->name }}</p>
                                </td>
                                <td class="text-right font-medium text-slate-900 dark:text-slate-50 whitespace-nowrap">Rp {{ number_format($b->price, 0, ',', '.') }}</td>
                                <td class="text-center"><x-status-badge :status="$b->status" context="booking" /></td>
                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route("$prefix.booking.show", $b) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Detail" aria-label="Detail booking {{ $b->booking_code }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="ri-calendar-check-line" title="Belum ada booking"
                                                   description="Booking dari calon penyewa akan muncul di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-white/[0.06]">{{ $bookings->links() }}</div>
        </div>
    </div>
</x-app-layout>
