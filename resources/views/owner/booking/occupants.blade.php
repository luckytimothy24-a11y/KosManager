<x-app-layout>
    @php $prefix = Auth::user()->isAdmin() ? 'admin' : 'owner'; @endphp
    <x-slot name="header">
        <x-page-header title="Occupants Booking #{{ $booking->id }}" description="{{ $booking->kos?->name }} · Kamar {{ $booking->kamar?->room_number }}">
            <x-button href="{{ route($prefix . '.booking.show', $booking) }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Daftar Occupant</h3>
                <span class="text-xs text-slate-400 dark:text-slate-500">{{ $booking->occupants->count() }} orang</span>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($booking->occupants as $occupant)
                    <div class="px-6 py-5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-surface-elevated flex items-center justify-center shrink-0">
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ strtoupper(substr($occupant->name ?? $occupant->user?->name ?? '?', 0, 1)) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $occupant->name ?? $occupant->user?->name ?? '-' }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $occupant->email ?? $occupant->user?->email ?? '-' }}</p>
                                    @if($occupant->phone)
                                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ $occupant->phone }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                                        'accepted' => 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300',
                                        'rejected' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                                    ];
                                    $identityColors = [
                                        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                                        'confirmed' => 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300',
                                        'rejected' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium {{ $statusColors[$occupant->status] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($occupant->status) }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium {{ $identityColors[$occupant->identity_status] ?? 'bg-slate-100 text-slate-600' }}">
                                    ID: {{ ucfirst($occupant->identity_status) }}
                                </span>
                            </div>
                        </div>

                        @if($occupant->identityPending() && $occupant->hasIdentityData())
                            <div class="mt-4 flex items-center gap-2">
                                <form method="POST" action="{{ route("$prefix.booking-occupants.confirm", $occupant) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                                        <i class="ri-check-line"></i> Konfirmasi
                                    </button>
                                </form>
                                <button x-data="{ open: false }" @click="open = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-500/10 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition">
                                    <i class="ri-close-line"></i> Tolak
                                </button>
                                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                    <div @click.away="open = false" class="bg-white dark:bg-surface-elevated rounded-2xl shadow-xl p-6 max-w-md w-full mx-4">
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50">Tolak Pengajuan?</h4>
                                        <form method="POST" action="{{ route("$prefix.booking-occupants.reject", $occupant) }}" class="mt-4 space-y-3">
                                            @csrf
                                            <textarea name="reason" rows="3" required minlength="5" maxlength="500" placeholder="Alasan penolakan..."
                                                      class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated text-sm focus:border-red-500 focus:ring-red-500"></textarea>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" @click="open = false" class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">Batal</button>
                                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition">Tolak</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($occupant->penghuni_id)
                            <p class="mt-2 text-xs text-green-600 dark:text-green-400 font-medium"><i class="ri-check-double-line"></i> Sudah check-in</p>
                        @endif
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <span class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-surface-elevated flex items-center justify-center">
                            <i class="ri-group-line text-xl text-slate-300 dark:text-slate-600"></i>
                        </span>
                        <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada occupant</p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Occupant akan muncul setelah booking dikonfirmasi.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
