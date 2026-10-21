<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Check-In Identitas</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Booking yang menunggu verifikasi identitas.</p>
            </div>
            <a href="{{ route('tenant.booking.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-arrow-left-line"></i> Booking Saya
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            @if($bookings->isEmpty())
                <div class="py-16 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 grid place-items-center text-slate-400 dark:text-slate-500">
                        <i class="ri-shield-keyhole-line text-2xl"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada booking yang perlu check-in</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Booking dengan status disetujui akan muncul di sini untuk verifikasi identitas.</p>
                    <a href="{{ route('tenant.booking.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-primary-700 transition">Lihat Booking</a>
                </div>
            @else
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($bookings as $booking)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $booking->kos->name ?? '-' }} — Kamar {{ $booking->kamar->room_number ?? '-' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $booking->start_date?->format('d M Y') ?? '-' }} &middot; {{ $booking->end_date?->format('d M Y') ?? '-' }}</p>
                                @if($booking->identityConfirmed())
                                    <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 text-xs font-semibold px-2.5 py-1"><i class="ri-checkbox-circle-line"></i> Terkonfirmasi</span>
                                @elseif($booking->identity_data_submitted_at)
                                    <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 text-xs font-semibold px-2.5 py-1"><i class="ri-time-line"></i> Menunggu konfirmasi owner</span>
                                @else
                                    <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold px-2.5 py-1"><i class="ri-file-edit-line"></i> Perlu isi identitas</span>
                                @endif
                            </div>
                            <a href="{{ route('tenant.checkin.identity', $booking) }}" class="shrink-0 inline-flex items-center gap-2 rounded-xl {{ $booking->identityConfirmed() ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200' : 'bg-primary-600 text-white hover:bg-primary-700' }} px-5 py-2.5 text-sm font-bold transition">
                                {{ $booking->identityConfirmed() ? 'Lihat' : 'Lengkapi Identitas' }} <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
