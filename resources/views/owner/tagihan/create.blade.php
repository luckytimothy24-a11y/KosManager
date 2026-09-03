<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route($prefix.'.tagihan.index'); @endphp
    <x-slot name="header">
        <x-page-header title="Buat Tagihan" description="Terbitkan tagihan sewa untuk kontrak aktif.">
            <x-button href="{{ $backUrl }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <x-alert />
        <form method="POST" action="{{ route("$prefix.tagihan.store") }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf

            {{-- Section: Kontrak --}}
            <section class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-file-text-line text-primary-600 dark:text-primary-400"></i></span>
                    Kontrak &amp; Periode
                </h3>

                <div>
                    <label for="kontrak_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Kontrak Aktif <span class="text-red-500">*</span></label>
                    <select name="kontrak_id" required id="kontrak-select"
                            class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('kontrak_id') ? 'border-red-400' : '' }}">
                        <option value="">Pilih Kontrak</option>
                        @foreach($kontraks as $k)
                            <option value="{{ $k->id }}"
                                data-rental-type="{{ $k->rental_type }}"
                                data-price="{{ $k->rental_price }}"
                                {{ old('kontrak_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->contract_number }} · {{ $k->penghuni->user->name }} · Kamar {{ $k->kamar->room_number }} (Rp {{ number_format($k->rental_price, 0, ',', '.') }}/{{ $k->rental_type === 'daily' ? 'hari' : 'bulan' }})
                            </option>
                        @endforeach
                    </select>
                    @error('kontrak_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="bill_type" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Jenis Tagihan <span class="text-red-500">*</span></label>
                    <input id="bill_type" type="text" name="bill_type" value="{{ old('bill_type', 'Sewa Bulanan') }}" required maxlength="100"
                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                           placeholder="cth: Sewa Bulanan, Listrik, Kebersihan">
                    @error('bill_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="period_start" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Periode Mulai <span class="text-red-500">*</span></label>
                        <input id="period_start" type="date" name="period_start" value="{{ old('period_start') }}" required
                               class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('period_start') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="period_end" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Periode Akhir <span class="text-red-500">*</span></label>
                        <input id="period_end" type="date" name="period_end" value="{{ old('period_end') }}" required
                               class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('period_end') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Jatuh Tempo <span class="text-red-500">*</span></label>
                    <input id="due_date" type="date" name="due_date" value="{{ old('due_date') }}" required
                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('due_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </section>

            {{-- Section: Rincian Biaya --}}
            <section class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 mt-6 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-money-dollar-circle-line text-primary-600 dark:text-primary-400"></i></span>
                    Rincian Biaya
                </h3>

                <div>
                    <label for="subtotal-field" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Subtotal</label>
                    <input type="number" name="subtotal" id="subtotal-field" value="{{ old('subtotal') }}" readonly min="0"
                           placeholder="Otomatis dari kontrak &times; periode"
                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 dark:text-slate-300 text-sm cursor-not-allowed font-semibold">
                    <p class="mt-1.5 flex items-start gap-1.5 text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
                        <i class="ri-information-line mt-0.5 shrink-0"></i> Dihitung otomatis dari harga kontrak &times; periode dan diverifikasi ulang oleh sistem.
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="discount" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Diskon (Rp)</label>
                        <input id="discount" type="number" name="discount" value="{{ old('discount', 0) }}" min="0"
                               class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('discount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="penalty" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Denda (Rp)</label>
                        <input id="penalty" type="number" name="penalty" value="{{ old('penalty', 0) }}" min="0"
                               class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('penalty') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ $backUrl }}"
                   class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Batal</a>
                <button type="submit" :disabled="submitting"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="ri-save-line"></i> <span x-show="!submitting">Terbitkan Tagihan</span><span x-show="submitting" x-cloak>Menerbitkan...</span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        (function () {
            var kontrak = document.getElementById('kontrak-select');
            var start = document.querySelector('input[name="period_start"]');
            var end = document.querySelector('input[name="period_end"]');
            var subtotal = document.getElementById('subtotal-field');

            function compute() {
                if (!kontrak || !kontrak.selectedOptions.length) return;
                var opt = kontrak.selectedOptions[0];
                if (!opt.value || !opt.dataset.price) { subtotal.value = ''; return; }
                if (!start.value || !end.value || end.value <= start.value) { return; }

                var s = new Date(start.value), e = new Date(end.value);
                var price = parseFloat(opt.dataset.price);
                var units;

                if (opt.dataset.rentalType === 'daily') {
                    units = Math.round((e - s) / 86400000) + 1;
                } else {
                    units = (e.getFullYear() - s.getFullYear()) * 12 + (e.getMonth() - s.getMonth()) + 1;
                }

                if (units > 0 && !subtotal.value) {
                    subtotal.value = Math.round(price * units);
                }
            }

            [kontrak, start, end].forEach(function (el) {
                el.addEventListener('change', function () { subtotal.value = ''; compute(); });
            });
            compute();
        })();
    </script>
    @endpush
</x-app-layout>
