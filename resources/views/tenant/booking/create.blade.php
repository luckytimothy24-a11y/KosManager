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
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
                @php $preselectedKamarId = old('kamar_id', request('kamar_id')); @endphp

                {{-- Pilih kos --}}
                <div>
                    <label for="kos-select" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Pilih Kos <span class="text-red-500">*</span></label>
                    <form method="GET" action="{{ route('tenant.booking.create') }}" id="kos-form" class="flex flex-col sm:flex-row gap-3">
                        <select id="kos-select" name="kos_id" required aria-label="Pilih kos"
                                class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500"
                                onchange="this.form.submit()">
                            <option value="">Pilih Kos</option>
                            @foreach($kos as $k)
                                <option value="{{ $k->id }}" {{ request('kos_id') == $k->id ? 'selected' : '' }}>{{ $k->name }} — {{ $k->address }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
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
                                                class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                            <option value="">Pilih Kamar</option>
                                            @foreach($kamar as $k)
                                                <option value="{{ $k->id }}"
                                                        data-room="{{ $k->room_number }} — {{ $k->room_name }}"
                                                        data-monthly="{{ $k->monthly_price }}"
                                                        data-daily="{{ $k->daily_price }}"
                                                        data-photo="{{ $k->photo ?? '' }}"
                                                        {{ (string) $preselectedKamarId === (string) $k->id ? 'selected' : '' }}>
                                                    {{ $k->room_number }} — {{ $k->room_name }} ({{ $k->room_type }}) · Rp {{ number_format($k->monthly_price, 0, ',', '.') }}/bulan
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kamar_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                                        {{-- Foto kamar (dynamic) --}}
                                        <div id="room-photo-wrap" class="mt-3 relative rounded-xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 aspect-[4/3] hidden">
                                            <img id="room-photo-img" src="" alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'">
                                            <span id="room-photo-letter" class="absolute inset-0 flex items-center justify-center text-[4rem] leading-none font-black uppercase text-primary-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true"></span>
                                        </div>
                                    </div>

                                    <fieldset>
                                        <legend class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-2">Tipe Sewa <span class="text-red-500">*</span></legend>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition bg-white dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50/60 has-[:checked]:dark:bg-primary-500/10">
                                                <input type="radio" name="rental_type" value="monthly" {{ old('rental_type', 'monthly') === 'monthly' ? 'checked' : '' }} required
                                                       class="text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                                                <span>
                                                    <span class="block text-sm font-semibold text-slate-900 dark:text-white">Bulanan</span>
                                                    <span class="block text-xs text-slate-400 dark:text-slate-500">Sewa per bulan</span>
                                                </span>
                                            </label>
                                            <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition bg-white dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50/60 has-[:checked]:dark:bg-primary-500/10">
                                                <input type="radio" name="rental_type" value="daily" {{ old('rental_type') === 'daily' ? 'checked' : '' }}
                                                       class="text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                                                <span>
                                                    <span class="block text-sm font-semibold text-slate-900 dark:text-white">Harian</span>
                                                    <span class="block text-xs text-slate-400 dark:text-slate-500">Sewa per hari</span>
                                                </span>
                                            </label>
                                        </div>
                                    </fieldset>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        <div>
                                            <label for="start-date" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                                            <input type="date" id="start-date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" min="{{ now()->toDateString() }}" required
                                                   class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                            @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label for="end-date" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Tanggal Selesai <span class="text-red-500">*</span></label>
                                            <input type="date" id="end-date" name="end_date" value="{{ old('end_date', now()->addMonth()->toDateString()) }}" required
                                                   class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                            @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label for="notes-input" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Catatan</label>
                                        <textarea id="notes-input" name="notes" rows="3" placeholder="Opsional — pesan untuk pemilik kos"
                                                  class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('notes') }}</textarea>
                                    </div>

                                    <div class="flex items-center justify-end gap-3 pt-2">
                                        <a href="{{ route('tenant.kos.show', ['kos' => request('kos_id')]) }}"
                                           class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">Batal</a>
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
                                        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400"><i class="ri-file-list-line"></i> Review Booking</h3>
                                        <dl class="space-y-3 text-sm">
                                            @if($selectedKos)
                                                {{-- Foto kos --}}
                                                <div class="relative rounded-xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 aspect-[16/9]">
                                                    @if($selectedKos->photo)
                                                        <img src="{{ asset('storage/' . $selectedKos->photo) }}" alt="Foto {{ $selectedKos->name }}"
                                                             class="absolute inset-0 w-full h-full object-cover"
                                                             loading="lazy" onerror="this.style.display='none'">
                                                    @endif
                                                    <span class="absolute inset-0 flex items-center justify-center text-[5rem] leading-none font-black uppercase text-primary-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($selectedKos->name, 0, 1) }}</span>
                                                </div>

                                                <div class="rounded-xl bg-white/70 dark:bg-slate-900/40 border border-primary-100 dark:border-primary-500/20 p-3 space-y-2">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <dt class="text-slate-500 dark:text-slate-400 shrink-0">Kos</dt>
                                                        <dd class="font-bold text-slate-900 dark:text-white text-right">{{ $selectedKos->name }}</dd>
                                                    </div>
                                                    <div class="flex items-start justify-between gap-3">
                                                        <dt class="text-slate-500 dark:text-slate-400 shrink-0">Alamat</dt>
                                                        <dd class="font-semibold text-slate-700 dark:text-slate-200 text-right leading-snug">{{ $selectedKos->address }}</dd>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400 shrink-0">Kamar</dt>
                                                <dd id="sum-kamar" class="font-semibold text-slate-900 dark:text-white text-right">—</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400 shrink-0">Tipe sewa</dt>
                                                <dd id="sum-type" class="font-semibold text-slate-900 dark:text-white text-right">—</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400 shrink-0">Periode</dt>
                                                <dd id="sum-periode" class="font-semibold text-slate-900 dark:text-white text-right">—</dd>
                                            </div>
                                            <div class="pt-3 border-t border-primary-100 dark:border-primary-500/20 flex items-center justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400">Biaya sewa</dt>
                                                <dd id="sum-total" class="text-lg font-black text-primary-600 dark:text-primary-300">—</dd>
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
                        <div class="py-12 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto">
                                <i class="ri-home-smile-line text-2xl text-slate-300 dark:text-slate-600"></i>
                            </div>
                            <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">Tidak ada kamar tersedia untuk kos ini.</p>
                            <a href="{{ route('tenant.kos.index') }}" class="mt-2 inline-block text-sm font-semibold text-primary-500 hover:text-primary-600 transition">Cari kos lain</a>
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto">
                                <i class="ri-arrow-up-line text-2xl text-slate-300 dark:text-slate-600"></i>
                            </div>
                            <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">Pilih kos terlebih dahulu untuk melihat kamar tersedia.</p>
                        </div>
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

                var elKamar = document.getElementById('sum-kamar');
                var elType = document.getElementById('sum-type');
                var elPeriode = document.getElementById('sum-periode');
                var elTotal = document.getElementById('sum-total');
                var roomPhotoWrap = document.getElementById('room-photo-wrap');
                var roomPhotoImg = document.getElementById('room-photo-img');
                var roomPhotoLetter = document.getElementById('room-photo-letter');

                function daysBetween(a, b) {
                    var d1 = new Date(a + 'T00:00:00');
                    var d2 = new Date(b + 'T00:00:00');
                    return Math.round((d2 - d1) / 86400000);
                }

                function update() {
                    var opt = roomSel.options[roomSel.selectedIndex];
                    if (!opt || !opt.value) {
                        elKamar.textContent = '—';
                        elType.textContent = '—';
                        elPeriode.textContent = '—';
                        elTotal.textContent = '—';
                        roomPhotoWrap.classList.add('hidden');
                        return;
                    }
                    var isDaily = document.querySelector('input[name="rental_type"]:checked').value === 'daily';
                    var price = Number(opt.dataset[isDaily ? 'daily' : 'monthly'] || 0);
                    var unit = isDaily ? '/hari' : '/bulan';

                    elKamar.textContent = opt.dataset.room || ('Kamar #' + opt.value);
                    elType.textContent = (isDaily ? 'Harian' : 'Bulanan') + ' · ' + fmt(price) + unit;

                    var s = startIn.value, e = endIn.value;
                    var total = 0;
                    if (s && e && daysBetween(s, e) > 0) {
                        var n = daysBetween(s, e);
                        elPeriode.textContent = s + ' s/d ' + e + ' (' + n + ' hari)';
                        if (isDaily) {
                            total = price * n;
                        } else {
                            total = price * Math.ceil(n / 30);
                        }
                    } else {
                        elPeriode.textContent = 'Tanggal tidak valid';
                    }

                    elTotal.textContent = total > 0 ? fmt(total) : (price > 0 ? fmt(price) : 'Hubungi pemilik');

                    var photo = opt.dataset.photo;
                    var roomLabel = (opt.dataset.room || '').split(' — ')[0] || '';
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
                }

                [roomSel, startIn, endIn].forEach(function (el) {
                    el.addEventListener('change', update);
                    el.addEventListener('input', update);
                });
                Array.prototype.forEach.call(document.querySelectorAll('input[name="rental_type"]'), function (el) {
                    el.addEventListener('change', update);
                });
                update();
            })();
        </script>
    @endif
</x-app-layout>
