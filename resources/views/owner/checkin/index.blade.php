<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Check-In" description="Review pengajuan check-in tenant — konfirmasi atau tolak. Lalu proses check-in final." icon="ri-login-box-line" />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        @if($errors->any())
            <div class="rounded-2xl border border-red-200/80 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] p-4" role="alert">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-red-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-error-warning-line text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-red-800 dark:text-red-200">Gagal memproses</p>
                        <ul class="mt-1 text-xs text-red-700/80 dark:text-red-300/80 list-disc pl-4 space-y-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Booking Siap Check-In — now 4 states: pending review / confirmed / rejected / not_submitted --}}
        @if($readyBookings->isNotEmpty())
            <div class="card-surface p-5 sm:p-6">
                <div class="flex items-center gap-3 pb-4 mb-4 border-b border-slate-100 dark:border-white/[0.06]">
                    <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-shield-check-line text-lg text-amber-600 dark:text-amber-400"></i>
                    </span>
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-slate-50 text-sm">Pengajuan Check-In</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500">Tenant mengisi WhatsApp & alamat — Anda cukup verifikasi kontak dan pembayaran DP.</p>
                    </div>
                    <span class="ml-auto px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">{{ $readyBookings->count() }} booking</span>
                </div>

                <div class="space-y-3">
                    @foreach($readyBookings as $rb)
                        @php $identityStatus = $rb->identityStatus(); @endphp
                        <div x-data="{ open: false, showReject: false }" class="border rounded-2xl overflow-hidden transition-colors
                            @if($identityStatus === 'pending') border-amber-200 dark:border-amber-500/30
                            @elseif($identityStatus === 'confirmed') border-green-200 dark:border-green-500/20
                            @elseif($identityStatus === 'rejected') border-red-200 dark:border-red-500/20
                            @else border-slate-200 dark:border-white/[0.08] @endif"
                             :class="open && 'border-primary-200 dark:border-primary-500/30'">
                            <button type="button" @click="open = !open"
                                    class="group w-full flex flex-wrap items-center justify-between gap-3 px-4 py-3.5 text-left hover:bg-slate-50/60 dark:hover:bg-white/[0.03] transition"
                                    :aria-expanded="open.toString()" aria-controls="checkin-form-{{ $rb->id }}">
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm min-w-0">
                                    <span class="inline-flex items-center gap-2 min-w-0">
                                        <span class="font-mono text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $rb->booking_code }}</span>
                                        <span class="text-slate-300 dark:text-slate-600" aria-hidden="true">•</span>
                                        <span class="min-w-0 truncate font-semibold text-slate-900 dark:text-slate-50">{{ $rb->user->name }}</span>
                                    </span>
                                    <span class="text-slate-500 dark:text-slate-400 truncate hidden sm:inline">{{ $rb->kos->name }} · Kamar {{ $rb->kamar->room_number }}</span>
                                    <span class="text-slate-400 dark:text-slate-500 text-xs">{{ $rb->start_date->format('d M Y') }} – {{ $rb->end_date->format('d M Y') }}</span>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" tabindex="-1">
                                    @if($identityStatus === 'confirmed')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 dark:bg-green-500/10 px-2.5 py-1">
                                            <i class="ri-check-line text-green-600 dark:text-green-400"></i>
                                            <span class="text-green-700 dark:text-green-300">Dikonfirmasi</span>
                                        </span>
                                    @elseif($identityStatus === 'pending')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1">
                                            <i class="ri-time-line text-amber-600 dark:text-amber-400"></i>
                                            <span class="text-amber-700 dark:text-amber-300">Menunggu Konfirmasi</span>
                                        </span>
                                    @elseif($identityStatus === 'rejected')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 dark:bg-red-500/10 px-2.5 py-1">
                                            <i class="ri-close-circle-line text-red-600 dark:text-red-400"></i>
                                            <span class="text-red-700 dark:text-red-300">Ditolak</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 dark:bg-white/[0.06] px-2.5 py-1">
                                            <i class="ri-information-line text-slate-500"></i>
                                            <span class="text-slate-600 dark:text-slate-300">Belum Mengisi</span>
                                        </span>
                                    @endif
                                    <i class="ri-arrow-down-s-line ml-0.5 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"></i>
                                </span>
                            </button>

                            <div id="checkin-form-{{ $rb->id }}" x-show="open"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-cloak>

                                {{-- PENDING: show contact data + DP + Confirm / Reject --}}
                                @if($identityStatus === 'pending')
                                    <div class="px-4 pb-4 pt-4 border-t border-slate-100 dark:border-white/[0.06] mt-1">
                                        <div class="flex items-start gap-3 rounded-xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] px-4 py-4 mb-4">
                                            <span class="w-9 h-9 rounded-full bg-amber-100 dark:bg-amber-500/15 flex items-center justify-center shrink-0">
                                                <i class="ri-user-follow-line text-amber-600 dark:text-amber-400"></i>
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Check-In Menunggu Konfirmasi</p>
                                                <p class="mt-1 text-sm text-amber-700/90 dark:text-amber-300/90">Periksa kontak tenant dan pembayaran DP di bawah ini. Anda tidak perlu mengetik ulang — cukup konfirmasi atau tolak.</p>
                                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600/80 dark:text-amber-400/80">Nama Tenant</span>
                                                        <p class="mt-0.5 font-semibold text-slate-900 dark:text-slate-50">{{ $rb->user->name }}</p>
                                                        <p class="text-xs text-slate-500">{{ $rb->user->email }}</p>
                                                    </div>
                                                    <div>
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600/80 dark:text-amber-400/80">Kos / Kamar</span>
                                                        <p class="mt-0.5 font-medium text-slate-900 dark:text-slate-50">{{ $rb->kos->name }} · Kamar {{ $rb->kamar->room_number }}</p>
                                                    </div>
                                                    <div>
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600/80 dark:text-amber-400/80">WhatsApp</span>
                                                        <p class="mt-0.5 font-mono font-medium text-slate-900 dark:text-slate-50">{{ $rb->phone ?: '-' }}</p>
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600/80 dark:text-amber-400/80">Alamat</span>
                                                        <p class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $rb->address ?: '-' }}</p>
                                                    </div>
                                                </div>
                                                @include('owner.checkin._dp-status', ['booking' => $rb, 'prefix' => $prefix])
                                                @if($rb->identity_data_submitted_at)
                                                    <p class="mt-3 text-xs text-amber-600/80 dark:text-amber-400/80 flex items-center gap-1.5">
                                                        <i class="ri-time-line"></i> Diajukan: {{ $rb->identity_data_submitted_at->translatedFormat('d F Y, H:i') }}
                                                    </p>
                                                @endif
                                                <span class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-amber-100 dark:bg-amber-500/15 px-2.5 py-1 text-[11px] font-semibold text-amber-700 dark:text-amber-300">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menunggu Konfirmasi
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
                                            <button type="button" @click="showReject = !showReject"
                                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 bg-white dark:bg-surface-elevated border border-red-200 dark:border-red-500/30 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                                <i class="ri-close-line"></i> Tolak
                                            </button>
                                            <form method="POST" action="{{ route("$prefix.checkin.confirm", $rb) }}" class="flex-1 sm:flex-none">
                                                @csrf
                                                <button type="submit"
                                                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-xl transition disabled:opacity-50">
                                                    <i class="ri-check-line"></i> Konfirmasi Check-In
                                                </button>
                                            </form>
                                            <a href="{{ route("$prefix.booking.show", $rb) }}"
                                               class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-surface-elevated border border-slate-200/60 dark:border-white/[0.08] rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.06] transition text-center">Detail</a>
                                        </div>

                                        {{-- Reject with reason --}}
                                        <div x-show="showReject" x-cloak x-transition class="mt-4 rounded-xl border border-red-200 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] p-4">
                                            <form method="POST" action="{{ route("$prefix.checkin.reject", $rb) }}" class="space-y-3">
                                                @csrf
                                                <label for="reason_{{ $rb->id }}" class="block text-xs font-semibold text-red-800 dark:text-red-300">Alasan Penolakan <span class="text-red-500">*</span></label>
                                                <textarea id="reason_{{ $rb->id }}" name="reason" required minlength="5" maxlength="500" rows="2"
                                                          class="w-full rounded-xl border-red-200 dark:border-red-500/20 bg-white dark:bg-surface-elevated dark:text-slate-200 text-sm placeholder-slate-400 focus:border-red-400 focus:ring-red-400/20 outline-none transition"
                                                          placeholder="Contoh: Nomor WhatsApp tidak dapat dihubungi. Mohon perbaiki."></textarea>
                                                <div class="flex items-center justify-end gap-2.5">
                                                    <button type="button" @click="showReject = false"
                                                            class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200/60 dark:border-white/[0.08] bg-white dark:bg-surface-elevated rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.06] transition">Batal</button>
                                                    <button type="submit"
                                                            class="px-5 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition disabled:opacity-50">Tolak Check-In</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                {{-- CONFIRMED: readonly green + Process check-in --}}
                                @elseif($identityStatus === 'confirmed')
                                    <div class="px-4 pb-4 pt-4 border-t border-slate-100 dark:border-white/[0.06] mt-1">
                                        <div class="flex items-start gap-3 rounded-xl border border-green-100 dark:border-green-500/20 bg-green-50/70 dark:bg-green-500/[0.06] px-4 py-4 mb-4">
                                            <span class="w-9 h-9 rounded-full bg-green-100 dark:bg-green-500/15 flex items-center justify-center shrink-0">
                                                <i class="ri-shield-check-line text-green-600 dark:text-green-400"></i>
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-green-800 dark:text-green-300">✓ Check-In Dikonfirmasi</p>
                                                <p class="mt-1 text-sm text-green-700/90 dark:text-green-300/90">Kontak tenant telah Anda konfirmasi. Lanjutkan ke proses check-in final.</p>
                                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                                    <div>
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-green-600/80 dark:text-green-400/80">No. WhatsApp</span>
                                                        <p class="mt-0.5 font-mono font-medium text-green-800 dark:text-green-200">{{ $rb->phone ?: '-' }}</p>
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-green-600/80 dark:text-green-400/80">Alamat</span>
                                                        <p class="mt-0.5 font-medium text-green-800 dark:text-green-200">{{ $rb->address ?: '-' }}</p>
                                                    </div>
                                                </div>
                                                @include('owner.checkin._dp-status', ['booking' => $rb, 'prefix' => $prefix])
                                                @if($rb->identity_confirmed_at)
                                                    <p class="mt-2 text-xs text-green-600/80 dark:text-green-400/80 flex items-center gap-1.5">
                                                        <i class="ri-time-line"></i> Dikonfirmasi: {{ $rb->identity_confirmed_at->translatedFormat('d F Y, H:i') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route("$prefix.checkin.process", $rb) }}"
                                              x-data="{ submitting: false }" x-on:submit="submitting = true"
                                              class="mt-1">
                                            @csrf
                                            <div>
                                                <label for="notes_{{ $rb->id }}" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Catatan (opsional)</label>
                                                <input type="text" id="notes_{{ $rb->id }}" name="notes" maxlength="1000"
                                                       class="field-input"
                                                       placeholder="Opsional">
                                            </div>
                                            <div class="mt-4 flex flex-col items-stretch justify-end gap-3 sm:flex-row sm:items-center">
                                                <a href="{{ route("$prefix.booking.show", $rb) }}"
                                                   class="inline-flex w-full items-center justify-center px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-surface-elevated border border-slate-200/60 dark:border-white/[0.08] rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.06] transition sm:w-auto">Detail Booking</a>
                                                <button type="submit" :disabled="submitting"
                                                        class="btn-primary w-full sm:w-auto disabled:opacity-60">
                                                    <i class="ri-login-box-line"></i> Proses Check-In
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                {{-- REJECTED --}}
                                @elseif($identityStatus === 'rejected')
                                    <div class="px-4 pb-4 pt-4 border-t border-slate-100 dark:border-white/[0.06] mt-1">
                                        <div class="flex items-start gap-3 rounded-xl border border-red-100 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] px-4 py-4">
                                            <span class="w-9 h-9 rounded-full bg-red-100 dark:bg-red-500/15 flex items-center justify-center shrink-0">
                                                <i class="ri-close-circle-line text-red-600 dark:text-red-400"></i>
                                            </span>
                                            <div class="flex-1">
                                                <p class="text-sm font-bold text-red-800 dark:text-red-300">Check-In Ditolak</p>
                                                <p class="mt-1 text-sm text-red-700/90 dark:text-red-300/90">Menunggu tenant memperbaiki data.</p>
                                                @if($rb->identity_rejection_reason)
                                                    <div class="mt-3 rounded-xl bg-white dark:bg-surface-elevated border border-red-200 dark:border-red-500/20 px-4 py-3">
                                                        <p class="text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-400">Alasan</p>
                                                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $rb->identity_rejection_reason }}</p>
                                                    </div>
                                                @endif
                                                @if($rb->identity_rejected_at)
                                                    <p class="mt-2 text-xs text-red-600/80 dark:text-red-400/80 flex items-center gap-1.5">
                                                        <i class="ri-time-line"></i> Ditolak: {{ $rb->identity_rejected_at->translatedFormat('d F Y, H:i') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route("$prefix.booking.show", $rb) }}"
                                           class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-surface-elevated border border-slate-200/60 dark:border-white/[0.08] rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.06] transition">
                                            <i class="ri-eye-line"></i> Detail Booking
                                        </a>
                                    </div>

                                {{-- NOT SUBMITTED --}}
                                @else
                                    <div class="px-4 pb-4 pt-4 border-t border-slate-100 dark:border-white/[0.06] mt-1">
                                        <div class="flex items-start gap-3 rounded-xl border border-slate-100 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.04] px-4 py-4">
                                            <span class="w-9 h-9 rounded-full bg-slate-100 dark:bg-white/[0.06] flex items-center justify-center shrink-0">
                                                <i class="ri-information-line text-slate-500"></i>
                                            </span>
                                            <div>
                                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Menunggu Data Kontak Tenant</p>
                                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tenant belum mengajukan check-in. Minta tenant membuka menu <span class="font-semibold">Booking Saya → Pengajuan Check-In</span> pada akunnya.</p>
                                                @include('owner.checkin._dp-status', ['booking' => $rb, 'prefix' => $prefix])
                                                <a href="{{ route("$prefix.booking.show", $rb) }}"
                                                   class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-surface-elevated border border-slate-200/60 dark:border-white/[0.08] rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.06] transition">
                                                    <i class="ri-eye-line"></i> Detail Booking
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Riwayat Check-In --}}
        <div class="table-card">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-100 dark:border-white/[0.06]">
                <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50">
                    <i class="ri-history-line text-primary-500"></i> Riwayat Check-In
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-4 sm:px-6">Penghuni</th>
                            <th>Kamar</th>
                            <th class="hidden md:table-cell">Kos</th>
                            <th class="hidden sm:table-cell">Tanggal &amp; Jam</th>
                            <th class="hidden lg:table-cell">Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checkIns as $ci)
                            <tr>
                                <td class="px-4 sm:px-6">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($ci->penghuni->user->name, 0, 1)) }}</span>
                                        <span class="font-medium text-slate-900 dark:text-slate-50 truncate max-w-[10rem]">{{ $ci->penghuni->user->name }}</span>
                                    </div>
                                </td>
                                <td class="font-medium text-slate-700 dark:text-slate-200">{{ $ci->kamar->room_number }}</td>
                                <td class="text-slate-600 dark:text-slate-300 hidden md:table-cell">{{ $ci->kamar->kos->name }}</td>
                                <td class="hidden sm:table-cell">
                                    <p class="text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ \Carbon\Carbon::parse($ci->check_in_date)->format('d M Y') }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $ci->check_in_time }}</p>
                                </td>
                                <td class="text-slate-600 dark:text-slate-300 hidden lg:table-cell">{{ $ci->officer->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state icon="ri-login-box-line" title="Belum ada check-in"
                                                   description="Riwayat proses check-in penghuni akan tampil di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-white/[0.06]">{{ $checkIns->links() }}</div>
        </div>
    </div>
</x-app-layout>
