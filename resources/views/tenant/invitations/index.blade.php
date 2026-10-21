<x-app-layout>
    <x-slot name="header">
        @php $pendingCount = $pending->count(); @endphp
        <x-page-header title="Undangan Kos" description="Ajakan tinggal bersama dari teman — terima atau tolak di sini." icon="ri-mail-send-line">
            @if($pendingCount > 0)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-600 text-white px-3 py-1.5 text-[11px] font-semibold shadow"><i class="ri-notification-3-line"></i> {{ $pendingCount }} Menunggu</span>
            @endif
        </x-page-header>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6 pb-6">
        <x-alert />

        @if($pending->isNotEmpty())
            <section aria-label="Undangan menunggu" class="space-y-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-slate-50 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
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
                        <div class="group relative overflow-hidden card-surface border-primary-100 dark:border-primary-500/20 p-5 sm:p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all" x-data="{ submittingAccept:false, submittingDecline:false }">
                            <div class="absolute -top-16 -right-16 w-40 h-40 rounded-full bg-gradient-to-br from-primary-500/10 to-primary-500/10 blur-2xl pointer-events-none"></div>
                            <div class="relative flex items-start justify-between gap-4">
                                <div class="flex gap-3 min-w-0">
                                    <div class="hidden sm:grid w-11 h-11 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20 shrink-0">
                                        <i class="ri-home-heart-line text-lg"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[15px] font-bold leading-tight text-slate-900 dark:text-slate-50">{{ $kos->name ?? 'Kos' }}</p>
                                        <p class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                            @if($kamar) <span class="inline-flex items-center gap-1 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-2.5 py-1 text-[11px] font-bold"><i class="ri-door-open-line"></i> {{ $kamar->room_number }}</span> @endif
                                            <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-300 border border-primary-100 dark:border-primary-500/20 px-2.5 py-1 text-[11px] font-bold"><i class="ri-team-line"></i> {{ (int) ($booking->requested_occupants ?? 1) }} orang</span>
                                            @if($booking?->start_date && $booking?->end_date)
                                                <span class="text-slate-400">· {{ \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($booking->end_date)->translatedFormat('d M Y') }}</span>
                                            @endif
                                        </p>
                                        @if($inviter)
                                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5"><span class="grid w-6 h-6 place-items-center rounded-full bg-slate-100 dark:bg-surface-elevated text-[10px] font-bold">{{ mb_substr($inviter->name,0,1) }}</span> Diundang oleh <span class="font-bold text-slate-800 dark:text-slate-200">{{ $inviter->name }}</span></p>
                                        @endif
                                        @if($inv->invite_expires_at)
                                            <p class="mt-2 inline-flex items-center gap-1 rounded-full bg-primary-50 dark:bg-primary-500/10 border border-primary-100 dark:border-primary-500/20 px-2.5 py-1 text-[11px] font-bold text-primary-700 dark:text-primary-300"><i class="ri-time-line"></i> Hingga {{ $inv->invite_expires_at->translatedFormat('d M Y H:i') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-primary-600 text-white px-3 py-1.5 text-[11px] font-semibold shadow">Menunggu</span>
                            </div>
                            <div class="mt-5 flex flex-col sm:flex-row gap-2.5">
                                <form method="POST" action="{{ route('tenant.invitations.acceptId', $inv) }}" class="flex-1" x-on:submit="if(submittingAccept) $event.preventDefault(); submittingAccept=true">
                                    @csrf
                                    <button type="submit" :disabled="submittingAccept" class="btn-primary w-full min-h-[46px] py-3" aria-label="Terima undangan {{ $kos->name ?? 'kos' }}">
                                        <i class="ri-check-line text-base" x-show="!submittingAccept"></i>
                                        <svg x-show="submittingAccept" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        <span x-text="submittingAccept ? 'Memproses…' : 'Terima & Gabung'"></span>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('tenant.invitations.declineId', $inv) }}" class="flex-1" x-on:submit="if(submittingDecline) $event.preventDefault(); submittingDecline=true">
                                    @csrf
                                    <button type="submit" :disabled="submittingDecline" class="btn-secondary w-full min-h-[46px] py-3" aria-label="Tolak undangan {{ $kos->name ?? 'kos' }}">
                                        <i class="ri-close-line" x-show="!submittingDecline"></i>
                                        <svg x-show="submittingDecline" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        <span x-text="submittingDecline ? 'Memproses…' : 'Tolak'"></span>
                                    </button>
                                </form>
                            </div>
                            <p class="mt-3 text-center text-meta"><i class="ri-shield-check-line mr-1 text-emerald-500"></i> Dengan menerima, kamu jadi anggota booking ini — identitas diisi sendiri.</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($pending->isEmpty() && $others->isEmpty())
            <section aria-label="Belum ada undangan" class="card-surface relative overflow-hidden p-8 sm:p-10 text-center">
                <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-primary-500/10 to-primary-500/10 blur-2xl pointer-events-none"></div>
                <div class="relative mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20">
                    <i class="ri-mail-open-line text-3xl"></i>
                </div>
                <h2 class="relative mt-5 text-lg font-bold text-slate-900 dark:text-slate-50">Belum Ada Undangan</h2>
                <p class="relative mx-auto mt-2 max-w-sm text-sm leading-relaxed text-slate-500 dark:text-slate-400">Ketika teman mengajak tinggal bersama, undangannya akan muncul di sini. Kamu bisa terima langsung tanpa ribet.</p>
                <div class="relative mt-7 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('tenant.kos.index') }}" class="btn-primary"><i class="ri-search-eye-line"></i> Cari Kos</a>
                    <a href="{{ route('dashboard') }}" class="btn-secondary">Ke Dashboard</a>
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
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-50">Riwayat Undangan</h2>
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
                                'declined' => ['label'=>'Ditolak','class'=>'bg-slate-100 dark:bg-surface-elevated text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08]'],
                                'expired' => ['label'=>'Kedaluwarsa','class'=>'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-300 border border-red-100 dark:border-red-500/20'],
                                'cancelled' => ['label'=>'Dibatalkan','class'=>'bg-slate-100 dark:bg-surface-elevated text-slate-500 dark:text-slate-400'],
                                default => ['label'=>ucfirst($inv->status),'class'=>'bg-slate-100 text-slate-600'],
                            };
                        @endphp
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 dark:border-white/[0.08] bg-slate-50/50 dark:bg-surface-elevated/50 px-4 py-3.5 hover:bg-white dark:hover:bg-slate-800 transition">
                            <div class="min-w-0 flex gap-3">
                                <span class="hidden sm:grid w-9 h-9 place-items-center rounded-xl bg-white dark:bg-surface-elevated border border-slate-100 dark:border-white/[0.08] text-slate-400 shrink-0"><i class="ri-mail-line"></i></span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold leading-tight text-slate-900 dark:text-slate-50 truncate">{{ $kos->name ?? 'Kos' }} @if($kamar) <span class="font-medium text-slate-500">· {{ $kamar->room_number }}</span> @endif</p>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400 truncate">
                                        @if($inviter) {{ $inviter->name }} · @endif
                                        {{ $inv->updated_at?->diffForHumans() ?? ucfirst($inv->status) }}
                                    </p>
                                </div>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full px-3 py-1 text-[11px] font-semibold {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
