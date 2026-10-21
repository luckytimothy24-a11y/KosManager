<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Check-Out" description="Persetujuan pengajuan check-out penghuni." icon="ri-logout-box-line" />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        @php $pendingCheckoutIds = $checkOuts->getCollection()->where('status', 'pending')->pluck('id')->values()->all(); @endphp
        <div class="table-card" x-data="{ selected: [], allPending: {{ json_encode($pendingCheckoutIds) }} }">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-100 dark:border-white/[0.06]">
                <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50">
                    <i class="ri-logout-box-line text-primary-500"></i> Daftar Pengajuan Check-Out
                </h2>
            </div>

            <div x-show="selected.length > 0" x-cloak class="flex flex-wrap items-center gap-3 border-b border-green-100 bg-green-50/60 px-4 sm:px-6 py-3 dark:border-green-500/20 dark:bg-green-500/[0.06]">
                <span class="text-xs font-bold text-green-700 dark:text-green-300"><span x-text="selected.length"></span> dipilih</span>
                <x-confirm-dialog title="Setujui Massal?" description="Setujui semua pengajuan terpilih? Item bertagihan menunggak dilewati otomatis."
                                   confirmText="Ya, Setujui" confirmClass="bg-green-600 hover:bg-green-700 text-white"
                                   triggerClass="inline-flex items-center gap-1.5 rounded-xl bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-xs font-semibold transition"
                                   aria-label="Setujui massal check-out terpilih">
                    <x-slot name="slot"><i class="ri-check-double-line"></i> Setujui Terpilih</x-slot>
                    <x-slot name="actions">
                        <form method="POST" action="{{ route("$prefix.checkout.bulk-approve") }}" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                            @csrf
                            <template x-for="id in selected" :key="id">
                                <input type="hidden" name="ids[]" :value="id">
                            </template>
                            <button type="submit" :disabled="submitting"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">Ya, Setujui</button>
                        </form>
                    </x-slot>
                </x-confirm-dialog>
                <button type="button" @click="selected = []" class="text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400">Batalkan pilihan</button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <input type="checkbox" aria-label="Pilih semua pending"
                                       :checked="allPending.length > 0 && selected.length === allPending.length"
                                       @change="selected = $event.target.checked ? [...allPending] : []"
                                       class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500/30">
                            </th>
                            <th class="px-4 sm:px-6">Penghuni</th>
                            <th>Kamar</th>
                            <th class="hidden sm:table-cell">Tgl Pengajuan</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checkOuts as $co)
                            <tr>
                                <td>
                                    @if($co->status === 'pending')
                                        <input type="checkbox" :value="{{ $co->id }}" :checked="selected.includes({{ $co->id }})"
                                               @change="selected = $event.target.checked ? [...selected, {{ $co->id }}] : selected.filter(id => id !== {{ $co->id }})"
                                               aria-label="Pilih check-out {{ $co->penghuni->user->name }}"
                                               class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500/30">
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($co->penghuni->user->name, 0, 1)) }}</span>
                                        <span class="font-medium text-slate-900 dark:text-slate-50 truncate max-w-[10rem]">{{ $co->penghuni->user->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <p class="font-medium text-slate-900 dark:text-slate-50">{{ $co->kamar->room_number }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[10rem]">{{ $co->kamar->kos->name }}</p>
                                </td>
                                <td class="hidden sm:table-cell">
                                    <p class="text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $co->request_date->format('d M Y') }}</p>
                                    @if($co->check_out_date)
                                        <p class="text-xs text-slate-400 dark:text-slate-500 whitespace-nowrap">rencana: {{ $co->check_out_date->format('d M Y') }}</p>
                                    @endif
                                </td>
                                <td class="text-center"><x-status-badge :status="$co->status" context="checkout" /></td>
                                <td>
                                    @if($co->status === 'pending' && !Auth::user()->isTenant())
                                        <div class="flex items-center justify-end gap-1.5">
                                            <x-confirm-dialog title="Setujui Check-Out?" description="Setujui check-out untuk {{ $co->penghuni->user->name }}? Penghuni akan dikeluarkan dari kamar."
                                                               confirmText="Setujui" confirmClass="bg-green-600 hover:bg-green-700 text-white"
                                                               triggerClass="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition"
                                                               aria-label="Setujui check-out {{ $co->penghuni->user->name }}">
                                                <x-slot name="slot"><i class="ri-check-line"></i></x-slot>
                                                <x-slot name="actions">
                                                    <form method="POST" action="{{ route("$prefix.checkout.approve", $co) }}" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                                        @csrf
                                                        <button type="submit" :disabled="submitting"
                                                                 class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                            <span x-show="!submitting">Setujui</span>
                                                            <span x-show="submitting" x-cloak>Memproses...</span>
                                                        </button>
                                                    </form>
                                                </x-slot>
                                            </x-confirm-dialog>
                                            <x-confirm-dialog title="Tolak Check-Out?" description="Tolak check-out untuk {{ $co->penghuni->user->name }}?"
                                                               confirmText="Tolak"
                                                               triggerClass="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                                               aria-label="Tolak check-out {{ $co->penghuni->user->name }}">
                                                <x-slot name="slot"><i class="ri-close-line"></i></x-slot>
                                                <x-slot name="actions">
                                                    <form method="POST" action="{{ route("$prefix.checkout.reject", $co) }}" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                                        @csrf
                                                        <button type="submit" :disabled="submitting"
                                                                 class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                            <span x-show="!submitting">Tolak</span>
                                                            <span x-show="submitting" x-cloak>Memproses...</span>
                                                        </button>
                                                    </form>
                                                </x-slot>
                                            </x-confirm-dialog>
                                            <x-confirm-dialog title="Batalkan Check-Out?" description="Batalkan pengajuan check-out untuk {{ $co->penghuni->user->name }}? Data pengajuan tetap tersimpan dengan status Dibatalkan."
                                                               confirmText="Ya, Batalkan" confirmClass="bg-slate-600 hover:bg-slate-700 text-white"
                                                               triggerClass="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                                               aria-label="Batalkan check-out {{ $co->penghuni->user->name }}">
                                                <x-slot name="slot"><i class="ri-close-circle-line"></i></x-slot>
                                                <x-slot name="actions">
                                                    <form method="POST" action="{{ route("$prefix.checkout.cancel", $co) }}" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                                        @csrf
                                                        <button type="submit" :disabled="submitting"
                                                                 class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-slate-600 hover:bg-slate-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                            <span x-show="!submitting">Ya, Batalkan</span>
                                                            <span x-show="submitting" x-cloak>Memproses...</span>
                                                        </button>
                                                    </form>
                                                </x-slot>
                                            </x-confirm-dialog>
                                        </div>
                                    @elseif($co->status !== 'pending')
                                        <span class="text-xs text-slate-400 dark:text-slate-500">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="ri-logout-box-line" title="Belum ada pengajuan check-out"
                                                   description="Pengajuan check-out dari penghuni akan tampil di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-white/[0.06]">{{ $checkOuts->links() }}</div>
        </div>
    </div>
</x-app-layout>
