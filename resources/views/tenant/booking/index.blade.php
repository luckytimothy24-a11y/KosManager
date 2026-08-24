<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Booking Saya" description="Riwayat booking Anda.">
            <x-button href="{{ route('tenant.booking.create') }}" type="primary">Booking Baru</x-button>
        </x-page-header>
    </x-slot>
    <x-alert />
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-gray-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Kode</th>
                        <th class="px-4 py-3 text-left font-medium">Kamar</th>
                        <th class="px-4 py-3 text-left font-medium">Periode</th>
                        <th class="px-4 py-3 text-right font-medium">Harga</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($bookings as $b)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800">
                            <td class="px-4 py-3 font-mono text-xs">{{ $b->booking_code }}</td>
                            <td class="px-4 py-3">{{ $b->kamar->room_number }} - {{ $b->kos->name }}</td>
                            <td class="px-4 py-3 text-xs">{{ $b->start_date }} s/d {{ $b->end_date }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($b->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                @php $bc = ['pending'=>'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300','approved'=>'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300','rejected'=>'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300','cancelled'=>'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100','completed'=>'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300']; @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $bc[$b->status] }}">{{ ucfirst($b->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if(in_array($b->status, ['pending', 'approved']))
                                    <form method="POST" action="{{ route('tenant.booking.cancel', $b) }}" onsubmit="return confirm('Yakin membatalkan booking?')">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Batalkan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-3">
                            <x-empty-state icon="ri-calendar-check-line" title="Belum ada booking" description="Ajukan booking untuk mulai menyewa kamar.">
                                <x-button href="{{ route('tenant.booking.create') }}" type="primary">Buat booking sekarang</x-button>
                            </x-empty-state>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $bookings->links() }}</div>
    </div>
</x-app-layout>
