<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/25 shrink-0">
                <i class="ri-team-line text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-black text-slate-900 dark:text-white tracking-tight leading-none">Undangan Tinggal Bersama</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kamu diajak gabung — putuskan sekarang, langsung jadi anggota.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-lg mx-auto space-y-6">
        <x-alert />

        <div class="relative overflow-hidden rounded-[24px] border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
            <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-orange-500/10 to-amber-500/10 blur-2xl pointer-events-none"></div>
            <div class="p-6 sm:p-8 relative">
                <div class="flex items-start gap-4">
                    <div class="hidden sm:grid w-12 h-12 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20 shrink-0">
                        <i class="ri-home-heart-line text-xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-black tracking-widest uppercase text-orange-600 dark:text-orange-400">Ajakan dari {{ $booking->user->name }}</p>
                        <h2 class="mt-1 text-[17px] font-black leading-tight text-slate-900 dark:text-white">Gabung di {{ $booking->kos->name }}</h2>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-3 py-1 text-xs font-black"><i class="ri-door-open-line"></i> Kamar {{ $booking->kamar->room_number }}</span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 dark:bg-orange-500/10 border border-orange-100 dark:border-orange-500/20 text-orange-700 dark:text-orange-300 px-3 py-1 text-xs font-bold">{{ (int) ($booking->requested_occupants ?? 1) }} orang</span>
                            @if($booking->start_date && $booking->end_date)
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($booking->end_date)->translatedFormat('d M Y') }}</span>
                            @endif
                        </div>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">Jika kamu terima, kamu akan langsung menjadi anggota booking ini. Setelah itu, lengkapi identitasmu sendiri — tidak ikut pemilik lain.</p>
                    </div>
                </div>

                @if($occupant->status === 'invited')
                    @if($emailMismatch ?? false)
                        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 dark:bg-amber-500/10 dark:border-amber-500/20 p-4">
                            <div class="flex gap-3">
                                <span class="grid w-9 h-9 place-items-center rounded-xl bg-amber-500 text-white shrink-0"><i class="ri-error-warning-line"></i></span>
                                <div>
                                    <p class="text-sm font-black text-amber-800 dark:text-amber-300">Email tidak cocok</p>
                                    <p class="text-xs leading-relaxed text-amber-700 dark:text-amber-400 mt-1">Undangan dikirim ke <b>{{ $occupant->email }}</b>. Silakan login dengan akun yang emailnya sama untuk menerima undangan ini.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ a:false, d:false }">
                            <form method="POST" action="{{ route('tenant.invitations.accept', $token) }}" x-on:submit="if(a) $event.preventDefault(); a=true">
                                @csrf
                                <button :disabled="a" class="w-full inline-flex min-h-[48px] items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-500/25 hover:from-orange-600 hover:to-orange-700 active:scale-[0.98] transition disabled:opacity-60 disabled:cursor-not-allowed">
                                    <svg x-show="a" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <i x-show="!a" class="ri-check-line text-base"></i>
                                    <span x-text="a ? 'Memproses…' : 'Terima Ajakan'"></span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('tenant.invitations.decline', $token) }}" x-on:submit="if(d) $event.preventDefault(); d=true">
                                @csrf
                                <button :disabled="d" class="w-full inline-flex min-h-[48px] items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-5 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 active:scale-[0.98] transition disabled:opacity-60 disabled:cursor-not-allowed">
                                    <svg x-show="d" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <i x-show="!d" class="ri-close-line"></i>
                                    <span x-text="d ? 'Memproses…' : 'Tolak'"></span>
                                </button>
                            </form>
                        </div>
                        <p class="mt-3 text-center text-[11px] text-slate-400 dark:text-slate-500"><i class="ri-shield-check-line mr-1 text-emerald-500"></i> Aman — identitasmu tetap milikmu, tidak dibagikan.</p>
                    @endif
                @elseif($occupant->status === 'accepted')
                    <div class="mt-6 rounded-2xl border border-emerald-100 dark:border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/10 p-4 flex gap-3">
                        <span class="grid w-9 h-9 place-items-center rounded-xl bg-emerald-500 text-white shrink-0"><i class="ri-checkbox-circle-line"></i></span>
                        <div>
                            <p class="text-sm font-black text-emerald-800 dark:text-emerald-300">Sudah diterima ✓</p>
                            <p class="text-xs leading-relaxed text-emerald-700 dark:text-emerald-400 mt-1">Kamu sudah jadi anggota booking. Lengkapi identitas agar owner bisa konfirmasi check-in.</p>
                        </div>
                    </div>
                    <a href="{{ route('tenant.invitations.identity', $token) }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-500/20 hover:from-orange-600 hover:to-orange-700 transition"><i class="ri-quill-pen-line"></i> Lengkapi Identitas</a>
                @elseif($occupant->status === 'declined')
                    <div class="mt-6 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-4 flex gap-3">
                        <span class="grid w-9 h-9 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 shrink-0"><i class="ri-close-circle-line"></i></span>
                        <div>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Undangan ditolak</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kamu menolak ajakan ini. Jika berubah pikiran, minta pengundang kirim ulang.</p>
                        </div>
                    </div>
                @elseif($occupant->status === 'expired')
                    <div class="mt-6 rounded-2xl border border-red-100 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 p-4 flex gap-3">
                        <span class="grid w-9 h-9 place-items-center rounded-xl bg-red-500 text-white shrink-0"><i class="ri-time-line"></i></span>
                        <div>
                            <p class="text-sm font-black text-red-700 dark:text-red-300">Kedaluwarsa</p>
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">Undangan sudah lewat masa berlakunya.</p>
                        </div>
                    </div>
                @endif
            </div>
            <div class="px-6 sm:px-8 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('tenant.booking.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-orange-600 dark:hover:text-orange-400 transition"><i class="ri-arrow-left-line"></i> Ke Booking Saya</a>
                <span class="text-[11px] text-slate-400 dark:text-slate-500">Kode: <b class="font-mono text-slate-700 dark:text-slate-200">{{ $booking->booking_code }}</b></span>
            </div>
        </div>
    </div>
</x-app-layout>
