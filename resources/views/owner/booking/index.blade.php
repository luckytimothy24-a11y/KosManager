<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Booking" description="Kelola data booking." />
    </x-slot>
    <x-alert />
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari booking..." class="flex-1 rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                <select name="status" class="rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                    <option value="">Semua Status</option>
                    @foreach(['pending','approved','rejected','cancelled','completed'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-gray-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Kode</th>
                        <th class="px-4 py-3 text-left font-medium">User</th>
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
                            <td class="px-4 py-3">{{ $b->user->name }}</td>
                            <td class="px-4 py-3">{{ $b->kamar->room_number }} - {{ $b->kos->name }}</td>
                            <td class="px-4 py-3 text-xs">{{ $b->start_date }} s/d {{ $b->end_date }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($b->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                @php $bc = ['pending'=>'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300','approved'=>'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300','rejected'=>'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300','cancelled'=>'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100','completed'=>'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300']; @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $bc[$b->status] }}">{{ ucfirst($b->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route("$prefix.booking.show", $b) }}" class="text-blue-600 hover:text-blue-800 text-xs">Detail</a>
                                    @if($b->status === 'pending')
                                        <form method="POST" action="{{ route("$prefix.booking.approve", $b) }}" class="inline">@csrf<button type="submit" class="text-green-600 hover:text-green-800 text-xs">Approve</button></form>
                                        <form method="POST" action="{{ route("$prefix.booking.reject", $b) }}" class="inline">@csrf<button type="submit" class="text-red-600 hover:text-red-800 text-xs">Reject</button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-3"><x-empty-state icon="ri-calendar-check-line" title="Belum ada booking." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $bookings->links() }}</div>
    </div>
</x-app-layout>
