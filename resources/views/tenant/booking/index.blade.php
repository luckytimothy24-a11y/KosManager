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

        @if($bookings->isEmpty())
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
                <x-empty-state icon="ri-calendar-check-line" title="Belum ada booking"
                               description="Buat booking baru untuk mulai menyewa kamar.">
                    <x-button href="{{ route('tenant.booking.create') }}" type="primary"><i class="ri-add-line"></i> Buat Booking Sekarang</x-button>
                </x-empty-state>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($bookings as $b)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden hover:shadow-lg transition-all duration-200 flex flex-col {{ in_array($b->status, ['cancelled', 'rejected']) ? 'opacity-90' : '' }}">
                        <a href="{{ route('tenant.booking.show', $b) }}" class="block p-5 flex-1 space-y-4" aria-label="Lihat detail booking {{ $b->booking_code }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono font-semibold text-slate-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">{{ $b->booking_code }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ \StatusLabels::rentalTypeLabel($b->rental_type) }}
                                        </span>
                                    </div>
                                    <h3 class="mt-2 text-sm font-bold text-slate-900 dark:text-white leading-snug">{{ $b->kos->name }}</h3>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Kamar {{ $b->kamar->room_number }} · {{ $b->kamar->room_name }}</p>
                                </div>
                                <x-status-badge :status="$b->status" context="booking" />
                            </div>

                            <div class="pt-3.5 border-t border-slate-100 dark:border-slate-800">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        <p class="flex items-center gap-1.5"><i class="ri-calendar-line text-primary-500"></i> {{ $b->start_date->format('d M Y') }} — {{ $b->end_date->format('d M Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Harga Sewa</p>
                                        <p class="text-base font-black text-slate-900 dark:text-white">Rp {{ number_format($b->price, 0, ',', '.') }} <span class="text-xs font-medium text-slate-400 dark:text-slate-500">{{ $b->priceUnitLabel() }}</span></p>
                                    </div>
                                </div>

                                @if($b->nextAction())
                                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-start gap-2 text-xs text-slate-500 dark:text-slate-400">
                                        <i class="ri-lightbulb-flash-line text-amber-500 mt-0.5 shrink-0"></i>
                                        <p>{{ $b->nextAction() }}</p>
                                    </div>
                                @endif
                            </div>
                        </a>

                        <div class="px-5 pb-5 -mt-1 flex flex-wrap items-center gap-2">
                            @if($b->isCancellable())
                                <x-confirm-dialog
                                    title="Batalkan booking ini?"
                                    description="Booking &ldquo;{{ $b->booking_code }}&rdquo; akan dibatalkan dan tidak dapat dikembalikan."
                                    confirmText="Ya, Batalkan"
                                    triggerClass="inline-flex items-center justify-center gap-1.5 flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500"
                                    aria-label="Batalkan booking {{ $b->booking_code }}">
                                    <x-slot name="slot"><i class="ri-close-circle-line"></i> Batalkan Booking</x-slot>
                                    <x-slot name="actions">
                                        <form method="POST" action="{{ route('tenant.booking.cancel', $b) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                            @csrf
                                            <button type="submit" :disabled="submitting"
                                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition shadow-sm shadow-red-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span x-show="!submitting">Ya, Batalkan</span>
                                                <span x-show="submitting" x-cloak>Membatalkan...</span>
                                            </button>
                                        </form>
                                    </x-slot>
                                </x-confirm-dialog>
                            @elseif(in_array($b->status, ['rejected', 'cancelled', 'expired']))
                                <a href="{{ route('tenant.kos.index') }}"
                                   class="inline-flex items-center justify-center gap-1.5 flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-primary-500 hover:bg-primary-600 transition shadow-sm shadow-primary-500/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                                    <i class="ri-search-line"></i> Cari Kos
                                </a>
                            @else
                                <span class="flex-1"></span>
                            @endif

                            <a href="{{ route('tenant.booking.show', $b) }}"
                               class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 hover:bg-slate-50 dark:hover:bg-slate-700 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
                               aria-label="Lihat detail booking {{ $b->booking_code }}">
                                <i class="ri-eye-line"></i> Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($bookings->hasPages())
                <div class="flex justify-center">{{ $bookings->links() }}</div>
            @endif
        @endif
    </div>
</x-app-layout>
