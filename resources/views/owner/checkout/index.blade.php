<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Check-Out" description="Daftar pengajuan check-out." />
    </x-slot>
    <x-alert />
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-gray-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Penghuni</th>
                        <th class="px-4 py-3 text-left font-medium">Kamar</th>
                        <th class="px-4 py-3 text-left font-medium">Tanggal Pengajuan</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($checkOuts as $co)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800">
                            <td class="px-4 py-3">{{ $co->penghuni->user->name }}</td>
                            <td class="px-4 py-3">{{ $co->kamar->room_number }}</td>
                            <td class="px-4 py-3">{{ $co->request_date }}</td>
                            <td class="px-4 py-3 text-center">
                                @php $cs = ['pending'=>'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300','approved'=>'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300','rejected'=>'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300','completed'=>'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300']; @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cs[$co->status] }}">{{ ucfirst($co->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($co->status === 'pending' && !Auth::user()->isTenant())
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route("$prefix.checkout.approve", $co) }}" class="inline">@csrf<button type="submit" class="text-green-600 hover:text-green-800 text-xs">Setuju</button></form>
                                        <form method="POST" action="{{ route("$prefix.checkout.reject", $co) }}" class="inline">@csrf<button type="submit" class="text-red-600 hover:text-red-800 text-xs">Tolak</button></form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-3"><x-empty-state icon="ri-logout-box-line" title="Belum ada pengajuan check-out." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $checkOuts->links() }}</div>
    </div>
</x-app-layout>
