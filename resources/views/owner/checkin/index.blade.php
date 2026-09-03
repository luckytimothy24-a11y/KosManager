<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Check-In" description="Proses kedatangan penghuni &amp; riwayat check-in." />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        @if($errors->any())
            <div class="rounded-2xl border border-red-200 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] p-4" role="alert">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-red-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-error-warning-line text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-red-800 dark:text-red-200">Data check-in belum lengkap</p>
                        <p class="mt-1 text-xs text-red-700/80 dark:text-red-300/80">Periksa kembali isian pada form booking yang ingin diproses, lalu kirim ulang.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Booking Siap Check-In --}}
        @if($readyBookings->isNotEmpty())
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-5 sm:p-6">
                <div class="flex items-center gap-3 pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-9 h-9 rounded-xl bg-green-50 dark:bg-green-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-login-box-line text-lg text-green-600 dark:text-green-400"></i>
                    </span>
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-white text-sm">Booking Siap Check-In</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500">Lengkapi data penghuni untuk mengaktifkan kontrak sewa.</p>
                    </div>
                    <span class="ml-auto px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300">{{ $readyBookings->count() }} menunggu</span>
                </div>

                <div class="space-y-3">
                    @foreach($readyBookings as $rb)
                        <div x-data="{ open: false }" class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden transition-colors" :class="open && 'border-primary-200 dark:border-primary-500/30'">
                            <button type="button" @click="open = !open"
                                    class="group w-full flex flex-wrap items-center justify-between gap-3 px-4 py-3.5 text-left hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition"
                                    :aria-expanded="open.toString()" aria-controls="checkin-form-{{ $rb->id }}">
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm min-w-0">
                                    <span class="font-mono text-xs font-semibold text-slate-400 dark:text-slate-500">{{ $rb->booking_code }}</span>
                                    <span class="font-semibold text-slate-900 dark:text-white truncate">{{ $rb->user->name }}</span>
                                    <span class="text-slate-500 dark:text-slate-400 truncate">{{ $rb->kos->name }} · Kamar {{ $rb->kamar->room_number }}</span>
                                    <span class="text-slate-400 dark:text-slate-500 text-xs">{{ $rb->start_date->format('d M Y') }} – {{ $rb->end_date->format('d M Y') }}</span>
                                </div>
                                <span class="inline-flex items-center gap-1.5 shrink-0 bg-primary-500 group-hover:bg-primary-600 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition" tabindex="-1">
                                    <i class="ri-user-add-line"></i> Proses Check-In
                                    <i class="ri-arrow-down-s-line transition-transform duration-200 ml-0.5" :class="open && 'rotate-180'"></i>
                                </span>
                            </button>

                            <div id="checkin-form-{{ $rb->id }}" x-show="open"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-cloak>
                                <form method="POST" action="{{ route("$prefix.checkin.process", $rb) }}"
                                      x-data="{ submitting: false }" x-on:submit="submitting = true"
                                      class="px-4 pb-4 pt-4 border-t border-slate-100 dark:border-slate-800 mt-1">
                                    @csrf
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-1">
                                        <div>
                                            <label for="identity_number_{{ $rb->id }}" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">No. Identitas (KTP) <span class="text-red-500">*</span></label>
                                            <input type="text" id="identity_number_{{ $rb->id }}" name="identity_number" required maxlength="50" value="{{ old('identity_number') }}"
                                                   class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                                                   placeholder="Contoh: 3374012345670001">
                                        </div>
                                        <div>
                                            <label for="phone_{{ $rb->id }}" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">No. HP <span class="text-red-500">*</span></label>
                                            <input type="tel" id="phone_{{ $rb->id }}" name="phone" required maxlength="20" value="{{ old('phone') }}"
                                                   class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                                                   placeholder="08xxxxxxxxxx">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label for="address_{{ $rb->id }}" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Alamat Asli <span class="text-red-500">*</span></label>
                                            <textarea id="address_{{ $rb->id }}" name="address" required rows="2" maxlength="500"
                                                      class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                                                      placeholder="Alamat KTP / domisili penghuni">{{ old('address') }}</textarea>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label for="notes_{{ $rb->id }}" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Catatan</label>
                                            <input type="text" id="notes_{{ $rb->id }}" name="notes" maxlength="1000"
                                                   class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                                                   placeholder="Opsional">
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-end gap-3 mt-5">
                                        <a href="{{ route("$prefix.booking.show", $rb) }}"
                                           class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">Detail Booking</a>
                                        <button type="submit" :disabled="submitting"
                                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition shadow-sm shadow-primary-500/30 disabled:opacity-60 disabled:cursor-not-allowed">
                                            <i class="ri-check-line"></i> Konfirmasi Check-In
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Riwayat Check-In --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <i class="ri-history-line text-primary-500"></i> Riwayat Check-In
            </h2>

            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-semibold">Penghuni</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">Kamar</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden md:table-cell">Kos</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden sm:table-cell">Tanggal &amp; Jam</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden lg:table-cell">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($checkIns as $ci)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($ci->penghuni->user->name, 0, 1)) }}</span>
                                        <span class="font-medium text-slate-900 dark:text-white truncate max-w-[10rem]">{{ $ci->penghuni->user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-medium text-slate-700 dark:text-slate-200">{{ $ci->kamar->room_number }}</td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300 hidden md:table-cell">{{ $ci->kamar->kos->name }}</td>
                                <td class="px-4 py-3.5 hidden sm:table-cell">
                                    <p class="text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ \Carbon\Carbon::parse($ci->check_in_date)->format('d M Y') }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $ci->check_in_time }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300 hidden lg:table-cell">{{ $ci->officer->name }}</td>
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

            @if($checkIns->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $checkIns->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
