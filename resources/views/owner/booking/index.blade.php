<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Booking" description="Kelola permintaan booking kamar dari calon penghuni." />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
            {{-- Filter bar --}}
            <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                    <div class="relative flex-1 min-w-0">
                        <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode booking atau nama..." aria-label="Cari booking"
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <select name="status" aria-label="Filter status booking"
                            class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Semua Status</option>
                        @foreach(['pending','approved','rejected','cancelled','completed'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \StatusLabels::bookingLabel($s) }}</option>
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
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">Kode</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden md:table-cell">Penyewa</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">Kamar</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Harga</th>
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($bookings as $b)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3.5">
                                    <p class="font-mono font-semibold text-slate-900 dark:text-white">{{ $b->booking_code }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $b->start_date?->format('d M Y') ?? $b->start_date }} s/d {{ $b->end_date?->format('d M Y') ?? $b->end_date }}</p>
                                </td>
                                <td class="px-4 py-3.5 hidden md:table-cell">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($b->user->name, 0, 1)) }}</span>
                                        <span class="text-slate-700 dark:text-slate-200 truncate max-w-[10rem]">{{ $b->user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $b->kamar->room_number }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[10rem]">{{ $b->kos->name }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-right font-medium text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($b->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 text-center"><x-status-badge :status="$b->status" context="booking" /></td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route("$prefix.booking.show", $b) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Detail" aria-label="Detail booking {{ $b->booking_code }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if($b->status === 'pending')
                                            <form method="POST" action="{{ route("$prefix.booking.approve", $b) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition"
                                                        title="Setujui" aria-label="Setujui booking {{ $b->booking_code }}"
                                                        x-data @click.prevent="$el.closest('form').requestSubmit()">
                                                    <i class="ri-check-line"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route("$prefix.booking.reject", $b) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                                        title="Tolak" aria-label="Tolak booking {{ $b->booking_code }}"
                                                        x-data @click.prevent="$el.closest('form').requestSubmit()">
                                                    <i class="ri-close-line"></i>
                                                </button>
                                            </form>
                                        @endif
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

            @if($bookings->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
