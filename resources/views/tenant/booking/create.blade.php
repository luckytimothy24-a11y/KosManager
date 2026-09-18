<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Buat Booking" description="Pilih kamar, tentukan periode, lalu konfirmasi.">
            <x-button href="{{ route('tenant.booking.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Booking Saya', 'url' => route('tenant.booking.index')],
            ['label' => 'Booking Baru'],
        ]" />
        <x-alert />

        <div class="max-w-4xl">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8">
                @php $preselectedKamarId = old('kamar_id', request('kamar_id')); @endphp

                {{-- Pilih kos --}}
                <div>
                    <label for="kos-select" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Pilih Kos <span class="text-red-500">*</span></label>
                    <form method="GET" action="{{ route('tenant.booking.create') }}" id="kos-form" class="flex flex-col sm:flex-row gap-3">
                        <select id="kos-select" name="kos_id" required aria-label="Pilih kos"
                                class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500"
                                onchange="this.form.submit()">
                            <option value="">Pilih Kos</option>
                            @foreach($kos as $k)
                                <option value="{{ $k->id }}" {{ request('kos_id') == $k->id ? 'selected' : '' }}>{{ $k->name }} â€” {{ $k->address }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-zinc-700">
                    @if($kamar->count())
                        <form method="POST" action="{{ route('tenant.booking.store') }}" id="booking-form"
                              x-data="{ submitting: false }" x-on:submit="submitting = true">
                            @csrf
                            <input type="hidden" name="kos_id" value="{{ request('kos_id') }}">
                            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                                <div class="lg:col-span-3 space-y-5">
                                    <div>
                                        <label for="kamar-select" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Kamar <span class="text-red-500">*</span></label>
                                        <select id="kamar-select" name="kamar_id" required
                                                class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                            <option value="">Pilih Kamar</option>
                                            @foreach($kamar as $k)
                                                <option value="{{ $k->id }}"
                                                        data-room="{{ $k->room_number }} â€” {{ $k->room_name }}"
                                                        data-monthly="{{ $k->monthly_price }}"
                                                        data-weekly="{{ $k->weekly_price }}"
                                                        data-daily="{{ $k->daily_price }}"
                                                        data-billing-mode="{{ $k->billing_mode ?? 'personal' }}"
                                                        data-capacity="{{ $k->capacity }}"
                                                        data-photo="{{ $k->photo ?? '' }}"
                                                        {{ (string) $preselectedKamarId === (string) $k->id ? 'selected' : '' }}>
                                                    {{ $k->room_number }} â€” {{ $k->room_name }} ({{ $k->room_type }}) Â· Rp {{ number_format($k->monthly_price, 0, ',', '.') }}/bulan
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kamar_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                                        {{-- Foto kamar (dynamic) --}}
                                        <div id="room-photo-wrap" class="mt-3 relative rounded-xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-primary-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 aspect-[4/3] hidden">
                                            <img id="room-photo-img" src="" alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'">
                                            <span id="room-photo-letter" class="absolute inset-0 flex items-center justify-center text-[4rem] leading-none font-black uppercase text-primary-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true"></span>
                                        </div>
                                    </div>

                                    <fieldset>
                                        <legend class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-2">Tipe Sewa <span class="text-red-500">*</span></legend>
                                        <div class="grid grid-cols-3 gap-3">
                                            <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition bg-white dark:bg-zinc-800/60 border-slate-200 dark:border-zinc-700 hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50/60 has-[:checked]:dark:bg-primary-500/10">
                                                <input type="radio" name="rental_type" value="monthly" {{ old('rental_type', 'monthly') === 'monthly' ? 'checked' : '' }} required
                                                       class="text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                                                <span>
                                                    <span class="block text-sm font-semibold text-slate-900 dark:text-white">Bulanan</span>
                                                    <span class="block text-xs text-slate-400 dark:text-slate-500">Sewa per bulan</span>
                                                </span>
                                            </label>
                                            <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition bg-white dark:bg-zinc-800/60 border-slate-200 dark:border-zinc-700 hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50/60 has-[:checked]:dark:bg-primary-500/10">
                                                <input type="radio" name="rental_type" value="weekly" {{ old('rental_type') === 'weekly' ? 'checked' : '' }}
                                                       class="text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                                                <span>
                                                    <span class="block text-sm font-semibold text-slate-900 dark:text-white">Mingguan</span>
                                                    <span class="block text-xs text-slate-400 dark:text-slate-500">Sewa per minggu</span>
                                                </span>
                                            </label>
                                            <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition bg-white dark:bg-zinc-800/60 border-slate-200 dark:border-zinc-700 hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50/60 has-[:checked]:dark:bg-primary-500/10">
                                                <input type="radio" name="rental_type" value="daily" {{ old('rental_type') === 'daily' ? 'checked' : '' }}
                                                       class="text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                                                <span>
                                                    <span class="block text-sm font-semibold text-slate-900 dark:text-white">Harian</span>
                                                    <span class="block text-xs text-slate-400 dark:text-slate-500">Sewa per hari</span>
                                                </span>
                                            </label>
                                        </div>
                                    </fieldset>

                                    {{-- F-04: Jumlah penghuni (human label, not requested_occupants) --}}
                                    <div id="occupant-field">
                                        <label for="occupant-count" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Berapa orang yang akan tinggal? <span class="text-red-500">*</span></label>
                                        <p id="occupant-help" class="text-xs text-slate-500 dark:text-slate-400 mb-2 leading-relaxed">Masukkan jumlah orang yang akan menempati kamar ini.</p>
                                        <div class="flex items-center gap-3">
                                            <button type="button" id="occupant-minus" aria-label="Kurangi jumlah penghuni" class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 min-h-[44px] min-w-[44px]"><i class="ri-subtract-line"></i></button>
                                            <input type="number" id="occupant-count" name="requested_occupants" value="{{ old('requested_occupants', 1) }}" min="1" max="10" required inputmode="numeric"
                                                   aria-describedby="occupant-help occupant-capacity occupant-invite-help"
                                                   class="w-20 text-center rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm font-semibold py-2.5 focus:border-primary-500 focus:ring-primary-500">
                                            <button type="button" id="occupant-plus" aria-label="Tambah jumlah penghuni" class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 min-h-[44px] min-w-[44px]"><i class="ri-add-line"></i></button>
                                            <span id="occupant-suffix" class="text-sm text-slate-500 dark:text-slate-400">orang</span>
                                        </div>
                                        <p id="occupant-capacity" class="mt-2 text-xs text-slate-500 dark:text-slate-400"></p>
                                        <p id="occupant-invite-help" class="mt-1.5 hidden rounded-xl border border-primary-100 dark:border-primary-500/20 bg-primary-50/60 dark:bg-primary-500/[0.06] px-3 py-2.5 text-xs leading-relaxed text-primary-700 dark:text-primary-300"><i class="ri-mail-line mr-1"></i> Setelah booking dibuat, kamu dapat mengundang penghuni lain untuk bergabung.</p>
                                        @error('requested_occupants') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- F-04: Pembayaran kamar (human label, not billing_mode) --}}
                                    <fieldset id="billing-mode-fieldset" class="hidden">
                                        <legend class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-2">Pembayaran kamar <span class="text-red-500">*</span></legend>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <label class="flex items-start gap-3 min-h-[44px] px-4 py-3 rounded-xl border cursor-pointer transition bg-white dark:bg-zinc-800/60 border-slate-200 dark:border-zinc-700 hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50/60 has-[:checked]:dark:bg-primary-500/10">
                                                <input type="radio" name="billing_mode" value="personal" {{ old('billing_mode', 'personal') === 'personal' ? 'checked' : '' }}
                                                       class="mt-1 text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                                                <span class="min-w-0">
                                                    <span class="block text-sm font-semibold text-slate-900 dark:text-white">Saya tanggung sendiri</span>
                                                    <span class="block text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Saya membayar seluruh biaya kamar.</span>
                                                </span>
                                            </label>
                                            <label class="flex items-start gap-3 min-h-[44px] px-4 py-3 rounded-xl border cursor-pointer transition bg-white dark:bg-zinc-800/60 border-slate-200 dark:border-zinc-700 hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50/60 has-[:checked]:dark:bg-primary-500/10">
                                                <input type="radio" name="billing_mode" value="shared" {{ old('billing_mode') === 'shared' ? 'checked' : '' }}
                                                       class="mt-1 text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                                                <span class="min-w-0">
                                                    <span class="block text-sm font-semibold text-slate-900 dark:text-white">Dibagi dengan penghuni lain</span>
                                                    <span class="block text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Biaya kamar akan dibagi sesuai jumlah penghuni.</span>
                                                </span>
                                            </label>
                                        </div>
                                        <p id="billing-mode-help" class="mt-2 text-xs text-slate-500 dark:text-slate-400 leading-relaxed hidden"></p>
                                        @error('billing_mode') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                                    </fieldset>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        <div>
                                            <label for="start-date" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                                            <input type="date" id="start-date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" min="{{ now()->toDateString() }}" required
                                                   class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                            @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label for="end-date" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Tanggal Selesai <span class="text-red-500">*</span></label>
                                            <input type="date" id="end-date" name="end_date" value="{{ old('end_date', now()->addMonth()->toDateString()) }}" required
                                                   class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                            @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label for="notes-input" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Catatan</label>
                                        <textarea id="notes-input" name="notes" rows="3" placeholder="Opsional â€” pesan untuk pemilik kos"
                                                  class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('notes') }}</textarea>
                                    </div>

                                    <div class="flex items-center justify-end gap-3 pt-2">
                                        <a href="{{ route('tenant.kos.show', ['kos' => request('kos_id')]) }}"
                                           class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">Batal</a>
                                        <button type="submit" :disabled="submitting"
                                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition shadow-sm shadow-primary-500/30 disabled:opacity-60 disabled:cursor-not-allowed">
                                            <template x-if="!submitting">
                                                <span class="flex items-center gap-2"><i class="ri-calendar-check-line"></i> Konfirmasi Booking</span>
                                            </template>
                                            <template x-if="submitting" x-cloak>
                                                <span class="flex items-center gap-2"><i class="ri-loader-4-line animate-spin"></i> Memproses...</span>
                                            </template>
                                        </button>
                                    </div>
                                </div>

                                {{-- Review / Ringkasan + Trust --}}
                                <aside class="lg:col-span-2">
                                    <div class="rounded-2xl border border-primary-100 dark:border-primary-500/20 bg-primary-50/50 dark:bg-primary-500/[0.06] p-5 lg:sticky lg:top-20 space-y-5">
                                        <h2 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400"><i class="ri-file-list-line"></i> Review Booking</h2>
                                        <dl class="space-y-3 text-sm">
                                            @if($selectedKos)
                                                {{-- Foto kos --}}
                                                <div class="relative rounded-xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-primary-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 aspect-[16/9]">
                                                    @if($selectedKos->photo)
                                                        <img src="{{ asset('storage/' . $selectedKos->photo) }}" alt="Foto {{ $selectedKos->name }}"
                                                             class="absolute inset-0 w-full h-full object-cover"
                                                             loading="lazy" onerror="this.style.display='none'">
                                                    @endif
                                                    <span class="absolute inset-0 flex items-center justify-center text-[5rem] leading-none font-black uppercase text-primary-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($selectedKos->name, 0, 1) }}</span>
                                                </div>

                                                <div class="rounded-xl bg-white/70 dark:bg-zinc-800/40 border border-primary-100 dark:border-primary-500/20 p-3 space-y-2">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <dt class="text-slate-500 dark:text-slate-400 shrink-0">Kos</dt>
                                                        <dd class="font-bold text-slate-900 dark:text-white text-right">{{ $selectedKos->name }}</dd>
                                                    </div>
                                                    <div class="flex items-start justify-between gap-3">
                                                        <dt class="text-slate-500 dark:text-slate-400 shrink-0">Alamat</dt>
                                                        <dd class="font-semibold text-slate-700 dark:text-slate-200 text-right leading-snug">
                                                            {{ $selectedKos->address }}
                                                            @if($selectedKos->googleMapsDirectionsUrl())
                                                                <x-kos-directions-button :kos="$selectedKos" variant="secondary" size="sm" label="Arah ke Kos" />
                                                            @endif
                                                        </dd>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400 shrink-0">Kamar</dt>
                                                <dd id="sum-kamar" class="font-semibold text-slate-900 dark:text-white text-right">â€”</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400 shrink-0">Jumlah penghuni</dt>
                                                <dd id="sum-occupants" class="font-semibold text-slate-900 dark:text-white text-right">—</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400 shrink-0">Pembayaran</dt>
                                                <dd id="sum-billing" class="font-semibold text-slate-900 dark:text-white text-right">—</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400 shrink-0">Tipe sewa</dt>
                                                <dd id="sum-type" class="font-semibold text-slate-900 dark:text-white text-right">â€”</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400 shrink-0">Periode</dt>
                                                <dd id="sum-periode" class="font-semibold text-slate-900 dark:text-white text-right">â€”</dd>
                                            </div>
                                            <p id="sum-occupant-help" class="hidden text-[11px] text-primary-600 dark:text-primary-300 leading-relaxed rounded-xl border border-primary-100 dark:border-primary-500/20 bg-white/60 dark:bg-slate-700/40 px-3 py-2"></p>
                                            <div class="pt-3 border-t border-primary-100 dark:border-primary-500/20">
                                                <div class="flex items-center justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400">Total harga kamar</dt>
                                                <dd id="sum-total" class="text-lg font-black text-primary-600 dark:text-primary-300">â€”</dd>
                                            </div>
                                        </dl>

                                        {{-- Trust Information --}}
                                        <div class="pt-4 border-t border-primary-100 dark:border-primary-500/20">
                                            <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2.5">Yang Kamu Dapatkan</h4>
                                            <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                                                <li class="flex items-start gap-2">
                                                    <i class="ri-check-double-line text-green-500 mt-0.5 shrink-0"></i>
                                                    <span>Booking langsung terkonfirmasi</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <i class="ri-check-double-line text-green-500 mt-0.5 shrink-0"></i>
                                                    <span>Kamar langsung diamankan</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <i class="ri-check-double-line text-green-500 mt-0.5 shrink-0"></i>
                                                    <span>Pembayaran dilakukan sesuai tagihan</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <i class="ri-check-double-line text-green-500 mt-0.5 shrink-0"></i>
                                                    <span>Check-in diproses pengelola saat kedatangan</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </aside>
                            </div>
                        </form>
                    @elseif(request('kos_id'))
                        <x-empty-state
                            icon="ri-home-smile-line"
                            title="Tidak ada kamar tersedia"
                            description="Kos ini belum memiliki kamar yang tersedia saat ini.">
                            <a href="{{ route('tenant.kos.index') }}" class="btn-secondary">
                                <i class="ri-search-eye-line"></i> Cari kos lain
                            </a>
                        </x-empty-state>
                    @else
                        <x-empty-state
                            icon="ri-arrow-up-line"
                            title="Pilih kos terlebih dahulu"
                            description="Pilih kos di atas untuk melihat kamar yang tersedia.">
                        </x-empty-state>
                    @endif
                </div>
            </div>
        </div>
    </div>

     @if($kamar->count())
        <script>
            (function () {
                var fmt = function (n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); };
                var roomSel = document.getElementById('kamar-select');
                var startIn = document.getElementById('start-date');
                var endIn = document.getElementById('end-date');
                if (!roomSel) return;

                var occupantInput = document.getElementById('occupant-count');
                var occupantMinus = document.getElementById('occupant-minus');
                var occupantPlus = document.getElementById('occupant-plus');
                var occupantCapacity = document.getElementById('occupant-capacity');
                var occupantInviteHelp = document.getElementById('occupant-invite-help');
                var elKamar = document.getElementById('sum-kamar');
                var elOccupants = document.getElementById('sum-occupants');
                var elBilling = document.getElementById('sum-billing');
                var elType = document.getElementById('sum-type');
                var elPeriode = document.getElementById('sum-periode');
                var elTotal = document.getElementById('sum-total');
                var elOccupantHelp = document.getElementById('sum-occupant-help');
                var elTotalNote = document.getElementById('sum-total-note');
                var roomPhotoWrap = document.getElementById('room-photo-wrap');
                var roomPhotoImg = document.getElementById('room-photo-img');
                var roomPhotoLetter = document.getElementById('room-photo-letter');
                var billingFieldset = document.getElementById('billing-mode-fieldset');
                var billingHelp = document.getElementById('billing-mode-help');

                function daysBetween(a, b) {
                    var d1 = new Date(a + 'T00:00:00');
                    var d2 = new Date(b + 'T00:00:00');
                    return Math.round((d2 - d1) / 86400000);
                }
                function clampOccupant() {
                    var v = parseInt(occupantInput.value, 10) || 1;
                    var cap = parseInt((roomSel.options[roomSel.selectedIndex] || {}).dataset.capacity || '10', 10) || 10;
                    if (v < 1) v = 1; if (v > cap) v = cap; if (v > 10) v = 10;
                    occupantInput.value = v; return v;
                }
                function billingLabel(val) { return val === 'shared' ? 'Dibagi dengan penghuni lain' : 'Saya tanggung sendiri'; }
                function updateOccupantUI() {
                    var opt = roomSel.options[roomSel.selectedIndex];
                    var cap = opt && opt.value ? parseInt(opt.dataset.capacity || '1', 10) || 1 : 1;
                    var n = clampOccupant();
                    if (!opt || !opt.value) { occupantCapacity.textContent = ''; occupantInviteHelp.classList.add('hidden'); occupantInput.max = 10; return; }
                    occupantInput.max = Math.min(cap, 10);
                    occupantCapacity.textContent = cap > 1 ? 'Kamar ini dapat dihuni hingga ' + cap + ' orang.' : 'Kamar ini untuk 1 orang.';
                    if (n > 1 && cap > 1) occupantInviteHelp.classList.remove('hidden'); else occupantInviteHelp.classList.add('hidden');
                }

                function updateBillingMode() {
                    if (!billingFieldset) return;
                    var opt = roomSel.options[roomSel.selectedIndex];
                    if (!opt || !opt.value) {
                        billingFieldset.classList.add('hidden');
                        return;
                    }
                    var bm = (opt.dataset.billingMode || 'personal').toLowerCase();
                    if (bm === 'shared') {
                        billingFieldset.classList.remove('hidden');
                        if (billingHelp) {
                            billingHelp.textContent = 'Kamar ini mendukung patungan. Pilih Dibagi dengan penghuni lain jika ingin total biaya dibagi penghuni.';
                            billingHelp.classList.remove('hidden');
                        }
                    } else {
                        billingFieldset.classList.add('hidden');
                        var personal = billingFieldset.querySelector('input[value="personal"]');
                        if (personal) personal.checked = true;
                        if (billingHelp) billingHelp.classList.add('hidden');
                    }
                }

                function update() {
                    var opt = roomSel.options[roomSel.selectedIndex];
                    if (!opt || !opt.value) {
                        elKamar.textContent = 'â€”';
                        elType.textContent = 'â€”';
                        elPeriode.textContent = 'â€”';
                        elTotal.textContent = 'â€”';
                        roomPhotoWrap.classList.add('hidden');
                        updateBillingMode();
                        return;
                    }
                    var rentalType = document.querySelector('input[name="rental_type"]:checked').value;
                    var price = Number(opt.dataset[rentalType] || 0);
                    var unitLabels = { daily: '/hari', weekly: '/mgg', monthly: '/bulan' };
                    var typeLabels = { daily: 'Harian', weekly: 'Mingguan', monthly: 'Bulanan' };
                    var unit = unitLabels[rentalType] || '/bulan';

                    elKamar.textContent = opt.dataset.room || ('Kamar #' + opt.value);
                    var n = clampOccupant();
                    var billingVal = (document.querySelector('input[name="billing_mode"]:checked') || {}).value || 'personal';
                    var cap = parseInt(opt.dataset.capacity || '1', 10) || 1;
                    if (elOccupants) elOccupants.textContent = n + ' orang' + (n === 1 ? '' : ' · ' + cap + ' maks');
                    if (elBilling) elBilling.textContent = billingLabel(billingVal);
                    elType.textContent = (typeLabels[rentalType] || 'Bulanan') + ' Â· ' + fmt(price) + unit;

                    var s = startIn.value, e = endIn.value;
                    var total = 0;
                    if (s && e && s <= e) {
                        var inclusiveDays = daysBetween(s, e) + 1;
                        elPeriode.textContent = s + ' s/d ' + e + ' (' + inclusiveDays + ' hari)';
                        if (rentalType === 'daily') {
                            total = price * inclusiveDays;
                        } else if (rentalType === 'weekly') {
                            total = price * Math.ceil(inclusiveDays / 7);
                        } else {
                            var sd = new Date(s + 'T00:00:00'), ed = new Date(e + 'T00:00:00');
                            total = price * ( (ed.getFullYear() - sd.getFullYear()) * 12 + (ed.getMonth() - sd.getMonth()) + 1 );
                        }
                    } else {
                        elPeriode.textContent = 'Tanggal tidak valid';
                    }

                    elTotal.textContent = total > 0 ? fmt(total) : (price > 0 ? fmt(price) : 'Hubungi pemilik');
                    if (elOccupantHelp) {
                        if (n > 1) { elOccupantHelp.textContent = 'Kamu bisa mengundang penghuni lain setelah booking dibuat.'; elOccupantHelp.classList.remove('hidden'); }
                        else { elOccupantHelp.textContent = 'Jumlah penghuni 1 orang.'; elOccupantHelp.classList.remove('hidden'); }
                    }
                    if (elTotalNote) {
                        if (billingVal === 'shared' && n > 1) { elTotalNote.textContent = 'Total harga kamar akan dibagi sesuai jumlah penghuni.'; elTotalNote.classList.remove('hidden'); }
                        else if (billingVal === 'shared') { elTotalNote.textContent = 'Total harga kamar — siap dibagi jika mengundang penghuni lain.'; elTotalNote.classList.remove('hidden'); }
                        else { elTotalNote.textContent = 'Total yang kamu bayar untuk periode ini.'; elTotalNote.classList.remove('hidden'); }
                    }

                    var photo = opt.dataset.photo;
                    var roomLabel = (opt.dataset.room || '').split(' â€” ')[0] || '';
                    if (photo) {
                        roomPhotoImg.src = '{{ asset("storage/") }}/' + photo;
                        roomPhotoImg.alt = 'Foto Kamar ' + roomLabel;
                        roomPhotoImg.style.display = '';
                        roomPhotoLetter.textContent = '';
                        roomPhotoWrap.classList.remove('hidden');
                    } else {
                        roomPhotoImg.src = '';
                        roomPhotoImg.style.display = 'none';
                        roomPhotoLetter.textContent = roomLabel.substring(0, 2);
                        roomPhotoWrap.classList.remove('hidden');
                    }
                    updateBillingMode(); updateOccupantUI();
                }

                if (occupantMinus) occupantMinus.addEventListener('click', function(){ var v=Math.max(1,(parseInt(occupantInput.value,10)||1)-1); occupantInput.value=v; updateOccupantUI(); update(); });
                if (occupantPlus) occupantPlus.addEventListener('click', function(){ var cap=parseInt((roomSel.options[roomSel.selectedIndex]||{}).dataset.capacity||'10',10)||10; var v=Math.min(cap,(parseInt(occupantInput.value,10)||1)+1); occupantInput.value=v; updateOccupantUI(); update(); });
                if (occupantInput) { occupantInput.addEventListener('change', function(){ updateOccupantUI(); update(); }); occupantInput.addEventListener('input', function(){ updateOccupantUI(); update(); }); }
                [roomSel, startIn, endIn].forEach(function (el) { el.addEventListener('change', update); el.addEventListener('input', update); });
                Array.prototype.forEach.call(document.querySelectorAll('input[name="rental_type"]'), function (el) { el.addEventListener('change', update); });
                Array.prototype.forEach.call(document.querySelectorAll('input[name="billing_mode"]'), function (el) { el.addEventListener('change', update); });
                roomSel.addEventListener('change', function(){ updateOccupantUI(); updateBillingMode(); update(); });
                update(); updateBillingMode(); updateOccupantUI();
            })();
        </script>
    @endif
</x-app-layout>
