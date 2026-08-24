<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Check-Out" description="Persetujuan pengajuan check-out penghuni." />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <i class="ri-logout-box-line text-primary-500"></i> Daftar Pengajuan Check-Out
            </h2>

            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-semibold">Penghuni</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">Kamar</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden sm:table-cell">Tgl Pengajuan</th>
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($checkOuts as $co)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($co->penghuni->user->name, 0, 1)) }}</span>
                                        <span class="font-medium text-slate-900 dark:text-white truncate max-w-[10rem]">{{ $co->penghuni->user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $co->kamar->room_number }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[10rem]">{{ $co->kamar->kos->name }}</p>
                                </td>
                                <td class="px-4 py-3.5 hidden sm:table-cell">
                                    <p class="text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $co->request_date->format('d M Y') }}</p>
                                    @if($co->check_out_date)
                                        <p class="text-xs text-slate-400 dark:text-slate-500 whitespace-nowrap">rencana: {{ $co->check_out_date->format('d M Y') }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center"><x-status-badge :status="$co->status" context="checkout" /></td>
                                <td class="px-4 py-3.5">
                                    @if($co->status === 'pending' && !Auth::user()->isTenant())
                                        <div class="flex items-center justify-end gap-1.5">
                                            <form method="POST" action="{{ route("$prefix.checkout.approve", $co) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition"
                                                        title="Setujui check-out" aria-label="Setujui check-out {{ $co->penghuni->user->name }}"
                                                        x-data @click.prevent="$el.closest('form').requestSubmit()">
                                                    <i class="ri-check-line"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route("$prefix.checkout.reject", $co) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                                        title="Tolak check-out" aria-label="Tolak check-out {{ $co->penghuni->user->name }}"
                                                        x-data @click.prevent="$el.closest('form').requestSubmit()">
                                                    <i class="ri-close-line"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($co->status !== 'pending')
                                        <span class="text-xs text-slate-400 dark:text-slate-500">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state icon="ri-logout-box-line" title="Belum ada pengajuan check-out"
                                                   description="Pengajuan check-out dari penghuni akan tampil di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($checkOuts->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $checkOuts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
