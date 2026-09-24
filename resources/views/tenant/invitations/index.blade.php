<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/25 shrink-0">
                <i class="ri-mail-send-line text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-black text-slate-900 dark:text-white tracking-tight leading-none">Undangan Kos</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ajakan tinggal bersama dari teman — terima atau tolak di sini.</p>
            </div>
            @php $pendingCount = $pending->count(); @endphp
            @if($pendingCount > 0)
                <span class="hidden sm:inline-flex ml-auto items-center gap-1.5 rounded-full bg-gradient-to-br from-orange-500 to-orange-600 text-white px-3 py-1.5 text-[11px] font-black shadow-lg shadow-orange-500/20"><i class="ri-notification-3-line"></i> {{ $pendingCount }} Menunggu</span>
            @endif
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6 pb-6">
        <x-alert />

        @if($pending->isNotEmpty())
            <section aria-label="Undangan menunggu" class="space-y-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-sm font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                        Undangan yang perlu ditanggapi
                    </h2>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Ketuk <b class="text-slate-700 dark:text-slate-200">Terima</b> untuk bergabung</span>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    @foreach($pending as $inv)
                        @php
                            $booking = $inv->booking;
                            $kos = $booking?->kos;
                            $kamar = $booking?->kamar;
                            $inviter = $booking?->user;
                        @endphp
                        <div class="group relative overflow-hidden rounded-[24px] border border-orange-100 dark:border-orange-500/20 bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all" x-data="{ submittingAccept:false, submittingDecline:false }">
                            <div class="absolute -top-16 -right-16 w-40 h-40 rounded-full bg-gradient-to-br from-orange-500/10 to-amber-500/10 blur-2xl pointer-events-none"></div>
                            <div class="relative flex items-start justify-between gap-4">
                                <div class="flex gap-3 min-w-0">
                                    <div class="hidden sm:grid w-11 h-11 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20 shrink-0">
                                        <i class="ri-home-heart-line text-lg"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[15px] font-black leading-tight text-slate-900 dark:text-white">{{ $kos->name ?? 'Kos' }}</p>
                                        <p class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                            @if($kamar) <span class="inline-flex items-center gap-1 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-2.5 py-1 text-[11px] font-bold"><i class="ri-door-open-line"></i> {{ $kamar->room_number }}</span> @endif
                                            <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-300 border border-orange-100 dark:border-orange-500/20 px-2.5 py-1 text-[11px] font-bold"><i class="ri-team-line"></i> {{ (int) ($booking->requested_occupants ?? 1) }} orang</span>
                                            @if($booking?->start_date && $booking?->end_date)
                                                <span class="text-slate-400">· {{ \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($booking->end_date)->translatedFormat('d M Y') }}</span>
                                            @endif
                                        </p>
                                        @if($inviter)
                                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5"><span class="grid w-6 h-6 place-items-center rounded-full bg-slate-100 dark:bg-slate-800 text-[10px] font-black">{{ mb_substr($inviter->name,0,1) }}</span> Diundang oleh <span class="font-bold text-slate-800 dark:text-slate-200">{{ $inviter->name }}</span></p>
                                        @endif
                                        @if($inv->invite_expires_at)
                                            <p class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 px-2.5 py-1 text-[11px] font-bold text-amber-700 dark:text-amber-300"><i class="ri-time-line"></i> Hingga {{ $inv->invite_expires_at->translatedFormat('d M Y H:i') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-gradient-to-br from-orange-500 to-orange-600 text-white px-3 py-1.5 text-[11px] font-black shadow">Menunggu</span>
                            </div>
                            <div class="mt-5 flex flex-col sm:flex-row gap-2.5">
                                <form method="POST" action="{{ route('tenant.invitations.acceptId', $inv) }}" class="flex-1" x-on:submit="if(submittingAccept) $event.preventDefault(); submittingAccept=true">
                                    @csrf
                                    <button type="submit" :disabled="submittingAccept" class="w-full inline-flex min-h-[46px] items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-500/25 transition hover:from-orange-600 hover:to-orange-700 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed" aria-label="Terima undangan {{ $kos->name ?? 'kos' }}">
                                        <i class="ri-check-line text-base" x-show="!submittingAccept"></i>
                                        <svg x-show="submittingAccept" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        <span x-text="submittingAccept ? 'Memproses…' : 'Terima & Gabung'"></span>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('tenant.invitations.declineId', $inv) }}" class="flex-1" x-on:submit="if(submittingDecline) $event.preventDefault(); submittingDecline=true">
                                    @csrf
                                    <button type="submit" :disabled="submittingDecline" class="w-full inline-flex min-h-[46px] items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-5 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 transition hover:bg-slate-50 dark:hover:bg-slate-700 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed" aria-label="Tolak undangan {{ $kos->name ?? 'kos' }}">
                                        <i class="ri-close-line" x-show="!submittingDecline"></i>
                                        <svg x-show="submittingDecline" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        <span x-text="submittingDecline ? 'Memproses…' : 'Tolak'"></span>
                                    </button>
                                </form>
                            </div>
                            <p class="mt-3 text-center text-[11px] text-slate-400 dark:text-slate-500"><i class="ri-shield-check-line mr-1 text-emerald-500"></i> Dengan menerima, kamu jadi anggota booking ini — identitas diisi sendiri.</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($pending->isEmpty() && $others->isEmpty())
            <section aria-label="Belum ada undangan" class="relative overflow-hidden rounded-[24px] border-2 border-dashed border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-8 sm:p-10 text-center">
                <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-orange-500/10 to-amber-500/10 blur-2xl pointer-events-none"></div>
                <div class="relative mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20">
                    <i class="ri-mail-open-line text-3xl"></i>
                </div>
                <h2 class="relative mt-5 text-lg font-black text-slate-900 dark:text-white">Belum Ada Undangan</h2>
                <p class="relative mx-auto mt-2 max-w-sm text-sm leading-relaxed text-slate-500 dark:text-slate-400">Ketika teman mengajak tinggal bersama, undangannya akan muncul di sini. Kamu bisa terima langsung tanpa ribet.</p>
                <div class="relative mt-7 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('tenant.kos.index') }}" class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 px-6 py-2.5 text-sm font-black text-white shadow-lg shadow-orange-500/20 hover:from-orange-600 hover:to-orange-700 transition"><i class="ri-search-eye-line"></i> Cari Kos</a>
                    <a href="{{ route('dashboard') }}" class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-6 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">Ke Dashboard</a>
                </div>
            </section>
        @endif

        @if($others->isNotEmpty())
            <section aria-label="Riwayat undangan" class="card-surface p-5 sm:p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 dark:bg-white flex items-center justify-center">
                        <i class="ri-history-line text-white dark:text-slate-900"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-900 dark:text-white">Riwayat Undangan</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Status terakhir undangan yang pernah kamu terima.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3">
                    @foreach($others as $inv)
                        @php
                            $booking = $inv->booking;
                            $kos = $booking?->kos;
                            $kamar = $booking?->kamar;
                            $inviter = $booking?->user;
                            $badge = match($inv->status){
                                'accepted' => ['label'=>'Diterima','class'=>'bg-emerald-500 text-white shadow'],
                                'declined' => ['label'=>'Ditolak','class'=>'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'],
                                'expired' => ['label'=>'Kedaluwarsa','class'=>'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-300 border border-red-100 dark:border-red-500/20'],
                                'cancelled' => ['label'=>'Dibatalkan','class'=>'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'],
                                default => ['label'=>ucfirst($inv->status),'class'=>'bg-slate-100 text-slate-600'],
                            };
                        @endphp
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 px-4 py-3.5 hover:bg-white dark:hover:bg-slate-800 transition">
                            <div class="min-w-0 flex gap-3">
                                <span class="hidden sm:grid w-9 h-9 place-items-center rounded-xl bg-white dark:bg-slate-700 border border-slate-100 dark:border-slate-600 text-slate-400 shrink-0"><i class="ri-mail-line"></i></span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold leading-tight text-slate-900 dark:text-white truncate">{{ $kos->name ?? 'Kos' }} @if($kamar) <span class="font-medium text-slate-500">· {{ $kamar->room_number }}</span> @endif</p>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400 truncate">
                                        @if($inviter) {{ $inviter->name }} · @endif
                                        {{ $inv->updated_at?->diffForHumans() ?? ucfirst($inv->status) }}
                                    </p>
                                </div>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full px-3 py-1 text-[11px] font-black {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
