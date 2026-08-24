<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Check-In" description="Proses kedatangan penghuni dan riwayat check-in." />
    </x-slot>
    <x-alert />

    {{-- Booking Siap Check-In --}}
    @if($readyBookings->isNotEmpty())
        <div class="mb-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-500/10 flex items-center justify-center">
                    <i class="ri-login-box-line text-green-600"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Booking Siap Check-In</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Lengkapi data penghuni untuk mengaktifkan kontrak sewa.</p>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($readyBookings as $rb)
                    <div x-data="{ open: false }" class="border border-gray-200 dark:border-slate-700 rounded-lg overflow-hidden">
                        <button type="button" @click="open = !open"
                                class="w-full flex flex-wrap items-center justify-between gap-3 px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-slate-800/60 transition"
                                :aria-expanded="open.toString()" aria-controls="checkin-form-{{ $rb->id }}">
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm min-w-0">
                                <span class="font-mono text-xs text-slate-400 dark:text-slate-500">{{ $rb->booking_code }}</span>
                                <span class="font-medium text-slate-900 dark:text-white truncate">{{ $rb->user->name }}</span>
                                <span class="text-slate-500 dark:text-slate-400">{{ $rb->kos->name }} · Kamar {{ $rb->kamar->room_number }}</span>
                                <span class="text-slate-400 dark:text-slate-500 text-xs">{{ $rb->start_date->format('d M Y') }} – {{ $rb->end_date->format('d M Y') }}</span>
                            </div>
                            <span class="inline-flex items-center gap-1.5 shrink-0 bg-primary-500 hover:bg-primary-600 text-white text-xs font-semibold px-3.5 py-2 rounded-lg transition" tabindex="-1">
                                <i class="ri-user-add-line"></i> Proses Check-In
                            </span>
                        </button>

                        <div id="checkin-form-{{ $rb->id }}" x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
                            <form method="POST" action="{{ route("$prefix.checkin.process", $rb) }}" class="px-4 pb-4 pt-1 border-t border-gray-100 dark:border-slate-800 mt-1">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <label for="identity_number_{{ $rb->id }}" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">No. Identitas (KTP) <span class="text-red-500">*</span></label>
                                        <input type="text" id="identity_number_{{ $rb->id }}" name="identity_number" required maxlength="50" value="{{ old('identity_number') }}"
                                               class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm" placeholder="Contoh: 3374012345670001">
                                    </div>
                                    <div>
                                        <label for="phone_{{ $rb->id }}" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">No. HP <span class="text-red-500">*</span></label>
                                        <input type="tel" id="phone_{{ $rb->id }}" name="phone" required maxlength="20" value="{{ old('phone') }}"
                                               class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm" placeholder="08xxxxxxxxxx">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="address_{{ $rb->id }}" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Alamat Asli <span class="text-red-500">*</span></label>
                                        <textarea id="address_{{ $rb->id }}" name="address" required rows="2" maxlength="500"
                                                  class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm" placeholder="Alamat KTP / domisili penghuni">{{ old('address') }}</textarea>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="notes_{{ $rb->id }}" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Catatan</label>
                                        <input type="text" id="notes_{{ $rb->id }}" name="notes" maxlength="1000"
                                               class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm" placeholder="Opsional">
                                    </div>
                                </div>
                                @error('identity_number') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                                @error('phone') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                                @error('address') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                                <div class="flex items-center justify-end gap-3 mt-4">
                                    <a href="{{ route("$prefix.booking.show", $rb) }}" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 transition">Detail Booking</a>
                                    <button type="submit"
                                            onclick="this.disabled = true; this.form.submit()"
                                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-500 rounded-lg hover:bg-primary-600 active:bg-primary-700 transition disabled:opacity-60 disabled:cursor-not-allowed">
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
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-gray-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Penghuni</th>
                        <th class="px-4 py-3 text-left font-medium">Kamar</th>
                        <th class="px-4 py-3 text-left font-medium">Kos</th>
                        <th class="px-4 py-3 text-left font-medium">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium">Jam</th>
                        <th class="px-4 py-3 text-left font-medium">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($checkIns as $ci)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800">
                            <td class="px-4 py-3">{{ $ci->penghuni->user->name }}</td>
                            <td class="px-4 py-3">{{ $ci->kamar->room_number }}</td>
                            <td class="px-4 py-3">{{ $ci->kamar->kos->name }}</td>
                            <td class="px-4 py-3">{{ $ci->check_in_date }}</td>
                            <td class="px-4 py-3">{{ $ci->check_in_time }}</td>
                            <td class="px-4 py-3">{{ $ci->officer->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-3"><x-empty-state icon="ri-login-box-line" title="Belum ada check-in." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $checkIns->links() }}</div>
    </div>
</x-app-layout>
