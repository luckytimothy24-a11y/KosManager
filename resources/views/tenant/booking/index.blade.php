<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Booking Saya" description="Riwayat &amp; status booking kamar Anda.">
            <x-button href="{{ route('tenant.booking.create') }}" type="primary">
                <i class="ri-add-line text-base"></i> Booking Baru
            </x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">Kode</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden md:table-cell">Kamar</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden sm:table-cell">Periode</th>
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
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ \StatusLabels::rentalTypeLabel($b->rental_type) }}</p>
                                </td>
                                <td class="px-4 py-3.5 hidden md:table-cell">
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $b->kamar->room_number }} — {{ $b->kamar->room_name }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[12rem]">{{ $b->kos->name }}</p>
                                </td>
                                <td class="px-4 py-3.5 hidden sm:table-cell text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $b->start_date->format('d M Y') }} — {{ $b->end_date->format('d M Y') }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($b->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 text-center"><x-status-badge :status="$b->status" context="booking" /></td>
                                <td class="px-4 py-3.5">
                                    @if(in_array($b->status, ['pending', 'approved']))
                                        <div class="flex items-center justify-end">
                                            <x-confirm-dialog
                                                title="Batalkan booking ini?"
                                                description="Booking &ldquo;{{ $b->booking_code }}&rdquo; akan dibatalkan dan tidak dapat dikembalikan."
                                                confirmText="Ya, Batalkan"
                                                triggerClass="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                                aria-label="Batalkan booking {{ $b->booking_code }}"
                                            >
                                                <x-slot name="slot"><i class="ri-close-circle-line"></i></x-slot>
                                                <x-slot name="content">
                                                    <form method="POST" action="{{ route('tenant.booking.cancel', $b) }}" id="cancel-booking-{{ $b->id }}">
                                                        @csrf
                                                    </form>
                                                </x-slot>
                                                <x-slot name="actions">
                                                    <button type="submit" form="cancel-booking-{{ $b->id }}"
                                                            class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">
                                                        Ya, Batalkan
                                                    </button>
                                                </x-slot>
                                            </x-confirm-dialog>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-300 dark:text-slate-600">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="ri-calendar-check-line" title="Belum ada booking"
                                                   description="Ajukan booking untuk mulai menyewa kamar.">
                                        <x-button href="{{ route('tenant.booking.create') }}" type="primary"><i class="ri-add-line"></i> Buat Booking Sekarang</x-button>
                                    </x-empty-state>
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
