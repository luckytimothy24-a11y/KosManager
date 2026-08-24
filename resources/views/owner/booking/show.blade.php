<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route($prefix.'.booking.index'); @endphp
    <x-slot name="header">
        <x-page-header title="Detail Booking {{ $booking->booking_code }}">
            <x-button href="{{ $backUrl }}" type="secondary">Kembali</x-button>
        </x-page-header>
    </x-slot>
    <div class="max-w-2xl">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500 dark:text-slate-400">Kode Booking:</span><p class="font-mono font-medium">{{ $booking->booking_code }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Status:</span>
                    @php $bc = ['pending'=>'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300','approved'=>'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300','rejected'=>'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300','cancelled'=>'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100','completed'=>'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300']; @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $bc[$booking->status] }}">{{ ucfirst($booking->status) }}</span>
                </div>
                <div><span class="text-gray-500 dark:text-slate-400">User:</span><p class="font-medium">{{ $booking->user->name }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Email:</span><p class="font-medium">{{ $booking->user->email }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Kos:</span><p class="font-medium">{{ $booking->kos->name }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Kamar:</span><p class="font-medium">{{ $booking->kamar->room_number }} ({{ $booking->kamar->room_type }})</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Tanggal Booking:</span><p class="font-medium">{{ $booking->booking_date }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Tipe Sewa:</span><p class="font-medium">{{ ucfirst($booking->rental_type) }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Mulai:</span><p class="font-medium">{{ $booking->start_date }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Selesai:</span><p class="font-medium">{{ $booking->end_date }}</p></div>
                <div class="col-span-2"><span class="text-gray-500 dark:text-slate-400">Harga:</span><p class="font-medium text-lg">Rp {{ number_format($booking->price, 0, ',', '.') }}</p></div>
                @if($booking->notes)
                    <div class="col-span-2"><span class="text-gray-500 dark:text-slate-400">Catatan:</span><p class="font-medium">{{ $booking->notes }}</p></div>
                @endif
            </div>

            @can('update', $booking)
                <div class="mt-6 pt-5 border-t border-gray-100 dark:border-slate-800 flex flex-wrap items-center justify-end gap-3">
                    @if($booking->status === 'pending')
                        <form method="POST" action="{{ route("$prefix.booking.reject", $booking) }}"
                              onsubmit="return confirm('Tolak booking ini?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-sm font-semibold text-red-600 hover:text-red-700 bg-red-50 dark:bg-red-500/10 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition">Tolak</button>
                        </form>
                        <form method="POST" action="{{ route("$prefix.booking.approve", $booking) }}"
                              onsubmit="return confirm('Setujui booking ini? Kamar akan direservasi.')">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 active:bg-green-800 rounded-lg transition">
                                <i class="ri-check-line"></i> Setujui Booking
                            </button>
                        </form>
                    @elseif($booking->status === 'approved')
                        <a href="{{ route("$prefix.checkin.index") }}"
                           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 active:bg-primary-700 rounded-lg transition">
                            <i class="ri-login-box-line"></i> Proses Check-In
                        </a>
                    @endif
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
