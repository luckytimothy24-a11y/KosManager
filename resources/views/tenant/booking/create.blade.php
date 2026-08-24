<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Buat Booking" description="Pilih kamar, tentukan periode, lalu konfirmasi.">
            <x-button href="{{ route('tenant.booking.index') }}" type="secondary">Kembali</x-button>
        </x-page-header>
    </x-slot>
    <x-alert />
    <div class="max-w-4xl">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            @php $preselectedKamarId = old('kamar_id', request('kamar_id')); @endphp

            <form method="GET" action="{{ route('tenant.booking.create') }}" class="mb-6">
                <label for="kos-select" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Kos <span class="text-red-500">*</span></label>
                <select id="kos-select" name="kos_id" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm" onchange="this.form.submit()">
                    <option value="">Pilih Kos</option>
                    @foreach($kos as $k)
                        <option value="{{ $k->id }}" {{ request('kos_id') == $k->id ? 'selected' : '' }}>{{ $k->name }} - {{ $k->address }}</option>
                    @endforeach
                </select>
            </form>

            @if($kamar->count())
                <form method="POST" action="{{ route('tenant.booking.store') }}" id="booking-form">
                    @csrf
                    <input type="hidden" name="kos_id" value="{{ request('kos_id') }}">
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                        <div class="lg:col-span-3 space-y-4">
                            <div>
                                <label for="kamar-select" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Kamar <span class="text-red-500">*</span></label>
                                <select id="kamar-select" name="kamar_id" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                                    <option value="">Pilih Kamar</option>
                                    @foreach($kamar as $k)
                                        <option value="{{ $k->id }}"
                                                data-room="{{ $k->room_number }} — {{ $k->room_name }}"
                                                data-monthly="{{ $k->monthly_price }}"
                                                data-daily="{{ $k->daily_price }}"
                                                {{ (string) $preselectedKamarId === (string) $k->id ? 'selected' : '' }}>
                                            {{ $k->room_number }} - {{ $k->room_name }} ({{ ucfirst($k->room_type) }}) - Rp {{ number_format($k->monthly_price, 0, ',', '.') }}/bulan
                                        </option>
                                    @endforeach
                                </select>
                                @error('kamar_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="rental-type" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Tipe Sewa <span class="text-red-500">*</span></label>
                                <select id="rental-type" name="rental_type" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                                    <option value="monthly" {{ old('rental_type', 'monthly') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="daily" {{ old('rental_type') === 'daily' ? 'selected' : '' }}>Harian</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="start-date" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                                    <input type="date" id="start-date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" min="{{ now()->toDateString() }}" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                                    @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="end-date" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                                    <input type="date" id="end-date" name="end_date" value="{{ old('end_date', now()->addMonth()->toDateString()) }}" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                                    @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="notes-input" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Catatan</label>
                                <textarea id="notes-input" name="notes" rows="3" placeholder="Opsional — pesan untuk pemilik kos" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">{{ old('notes') }}</textarea>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <a href="{{ route('tenant.kos.show', ['kos' => request('kos_id')]) }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 transition">Batal</a>
                                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 active:bg-primary-700 rounded-lg transition shadow-sm shadow-primary-500/30">Konfirmasi Booking</button>
                            </div>
                        </div>

                        {{-- Review / Ringkasan --}}
                        <aside class="lg:col-span-2">
                            <div class="rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50/60 dark:bg-slate-800/40 p-5 lg:sticky lg:top-20">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Review Booking</h3>
                                <dl class="mt-4 space-y-3 text-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <dt class="text-gray-500 dark:text-slate-400 shrink-0">Kamar</dt>
                                        <dd id="sum-kamar" class="font-semibold text-gray-800 dark:text-slate-100 text-right">—</dd>
                                    </div>
                                    <div class="flex items-start justify-between gap-3">
                                        <dt class="text-gray-500 dark:text-slate-400 shrink-0">Tipe sewa</dt>
                                        <dd id="sum-type" class="font-semibold text-gray-800 dark:text-slate-100 text-right">—</dd>
                                    </div>
                                    <div class="flex items-start justify-between gap-3">
                                        <dt class="text-gray-500 dark:text-slate-400 shrink-0">Periode</dt>
                                        <dd id="sum-periode" class="font-semibold text-gray-800 dark:text-slate-100 text-right">—</dd>
                                    </div>
                                    <div class="pt-3 border-t border-gray-200 dark:border-slate-700 flex items-center justify-between gap-3">
                                        <dt class="text-gray-500 dark:text-slate-400">Biaya sewa</dt>
                                        <dd id="sum-total" class="text-lg font-black text-primary-500">—</dd>
                                    </div>
                                </dl>
                                <p class="mt-4 pt-3 border-t border-gray-200 dark:border-slate-700 text-[11px] leading-relaxed text-gray-400 dark:text-slate-500">
                                    Booking akan berstatus <strong class="text-yellow-600 dark:text-yellow-400">Menunggu Persetujuan</strong>. Pembayaran dilakukan setelah pemilik menyetujui booking Anda.
                                </p>
                            </div>
                        </aside>
                    </div>
                </form>
            @elseif(request('kos_id'))
                <div class="text-center py-10 text-gray-400 dark:text-slate-500">
                    <i class="ri-home-smile-line text-3xl"></i>
                    <p class="mt-2 text-sm">Tidak ada kamar tersedia untuk kos ini.</p>
                    <a href="{{ route('tenant.kos.index') }}" class="mt-3 inline-block text-sm font-semibold text-primary-500 hover:text-primary-600 transition">Cari kos lain</a>
                </div>
            @else
                <div class="text-center py-10 text-gray-400 dark:text-slate-500">
                    <i class="ri-arrow-up-line text-3xl"></i>
                    <p class="mt-2 text-sm">Pilih kos terlebih dahulu untuk melihat kamar tersedia.</p>
                </div>
            @endif
        </div>
    </div>

    @if($kamar->count())
        <script>
            (function () {
                var fmt = function (n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); };
                var roomSel = document.getElementById('kamar-select');
                var typeSel = document.getElementById('rental-type');
                var startIn = document.getElementById('start-date');
                var endIn = document.getElementById('end-date');
                if (!roomSel) return;

                var elKamar = document.getElementById('sum-kamar');
                var elType = document.getElementById('sum-type');
                var elPeriode = document.getElementById('sum-periode');
                var elTotal = document.getElementById('sum-total');

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
                        return;
                    }
                    var isDaily = typeSel.value === 'daily';
                    var price = Number(opt.dataset[isDaily ? 'daily' : 'monthly'] || 0);
                    var unit = isDaily ? '/hari' : '/bulan';

                    elKamar.textContent = opt.dataset.room || ('Kamar #' + opt.value);
                    elType.textContent = (isDaily ? 'Harian' : 'Bulanan') + ' · ' + fmt(price) + unit;

                    var s = startIn.value, e = endIn.value;
                    if (s && e && daysBetween(s, e) > 0) {
                        var n = daysBetween(s, e);
                        elPeriode.textContent = s + ' s/d ' + e + ' (' + n + ' hari)';
                    } else {
                        elPeriode.textContent = 'Tanggal tidak valid';
                    }

                    elTotal.textContent = price > 0 ? fmt(price) : 'Hubungi pemilik';
                }

                [roomSel, typeSel, startIn, endIn].forEach(function (el) {
                    el.addEventListener('change', update);
                    el.addEventListener('input', update);
                });
                update();
            })();
        </script>
    @endif
</x-app-layout>
