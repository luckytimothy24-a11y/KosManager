<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Kontrak Saya" description="Ringkasan kontrak sewa kamar Anda." />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        @if($kontraks->isEmpty())
            <div class="card-surface">
                <x-empty-state icon="ri-file-text-line" title="Belum ada kontrak"
                               description="Kontrak sewa akan tampil di sini setelah Anda melakukan check-in di kamar." />
            </div>
        @else
            <div class="grid grid-cols-1 gap-4">
                @foreach($kontraks as $k)
                    <div class="card-surface overflow-hidden hover:shadow-lg transition-all duration-200">
                        <a href="{{ route('tenant.kontrak.show', $k) }}" class="block p-5 space-y-4" aria-label="Lihat detail kontrak {{ $k->contract_number }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono font-semibold text-slate-900 dark:text-slate-50 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">{{ $k->contract_number }}</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300">
                                            {{ \StatusLabels::rentalTypeLabel($k->rental_type) }}
                                        </span>
                                    </div>
                                    <h3 class="mt-2 text-sm font-bold text-slate-900 dark:text-slate-50 leading-snug">{{ $k->kos->name }}</h3>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Kamar {{ $k->kamar->room_number }} · {{ $k->kamar->room_name }}</p>
                                </div>
                                <x-status-badge :status="$k->status" context="kontrak" />
                            </div>

                            <div class="pt-3.5 border-t border-slate-100 dark:border-white/[0.08]">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        <p class="flex items-center gap-1.5"><i class="ri-calendar-line text-primary-500"></i> {{ $k->start_date->format('d M Y') }} — {{ $k->end_date->format('d M Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-label">Harga Sewa</p>
                                        <p class="text-base font-semibold text-slate-900 dark:text-slate-50">Rp {{ number_format($k->rental_price, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <div class="px-5 pb-5 -mt-1 flex flex-wrap items-center gap-2">
                            <a href="{{ route('tenant.kontrak.show', $k) }}"
                               class="btn-secondary flex-1"
                               aria-label="Lihat detail kontrak {{ $k->contract_number }}">
                                <i class="ri-eye-line"></i> Detail Kontrak
                            </a>
                            @php $pendingCo = $k->penghuni?->checkOuts->first(); @endphp
                            @if($pendingCo)
                                <x-confirm-dialog title="Batalkan Pengajuan Check-Out?" description="Pengajuan check-out Anda akan dibatalkan. Data pengajuan tetap tersimpan."
                                                   confirmText="Ya, Batalkan" confirmClass="bg-slate-600 hover:bg-slate-700 text-white"
                                                   triggerClass="inline-flex items-center justify-center gap-1.5 flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 transition"
                                                   aria-label="Batalkan pengajuan check-out">
                                    <x-slot name="slot"><i class="ri-close-circle-line"></i> Batalkan Pengajuan</x-slot>
                                    <x-slot name="actions">
                                        <form method="POST" action="{{ route('tenant.checkout.cancel', $pendingCo) }}" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                            @csrf
                                            <button type="submit" :disabled="submitting"
                                                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-slate-600 hover:bg-slate-700 transition disabled:opacity-50 disabled:cursor-not-allowed">Ya, Batalkan</button>
                                        </form>
                                    </x-slot>
                                </x-confirm-dialog>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($kontraks->hasPages())
                <div class="flex justify-center">{{ $kontraks->links() }}</div>
            @endif
        @endif
    </div>
</x-app-layout>