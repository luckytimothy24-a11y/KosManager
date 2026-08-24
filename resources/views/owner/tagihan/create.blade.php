<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route($prefix.'.tagihan.index'); @endphp
    <x-slot name="header">
        <x-page-header title="Buat Tagihan">
            <x-button href="{{ $backUrl }}" type="secondary">Kembali</x-button>
        </x-page-header>
    </x-slot>
    <div class="max-w-2xl">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <form method="POST" action="{{ route('owner.tagihan.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Kontrak <span class="text-red-500">*</span></label>
                        <select name="kontrak_id" required id="kontrak-select" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                            <option value="">Pilih Kontrak</option>
                            @foreach($kontraks as $k)
                                <option value="{{ $k->id }}"
                                    data-rental-type="{{ $k->rental_type }}"
                                    data-price="{{ $k->rental_price }}"
                                    {{ old('kontrak_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->contract_number }} - {{ $k->penghuni->user->name }} - {{ $k->kamar->room_number }} (Rp {{ number_format($k->rental_price, 0, ',', '.') }}/{{ $k->rental_type === 'daily' ? 'hari' : 'bulan' }})
                                </option>
                            @endforeach
                        </select>
                        @error('kontrak_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Jenis Tagihan <span class="text-red-500">*</span></label>
                        <input type="text" name="bill_type" value="{{ old('bill_type', 'Sewa Bulanan') }}" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Periode Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="period_start" value="{{ old('period_start') }}" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Periode Akhir <span class="text-red-500">*</span></label>
                            <input type="date" name="period_end" value="{{ old('period_end') }}" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Subtotal <span class="text-red-500">*</span></label>
                            <input type="number" name="subtotal" id="subtotal-field" value="{{ old('subtotal') }}" readonly required min="0"
                                   placeholder="Otomatis dari kontrak"
                                   class="w-full rounded-lg border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-800 text-sm cursor-not-allowed">
                            <p class="text-xs text-gray-400 mt-1">Dihitung otomatis dari harga kontrak &times; periode.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Diskon</label>
                            <input type="number" name="discount" value="{{ old('discount', 0) }}" min="0" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Denda</label>
                            <input type="number" name="penalty" value="{{ old('penalty', 0) }}" min="0" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Jatuh Tempo <span class="text-red-500">*</span></label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route("$prefix.tagihan.index") }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200">Batal</a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
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
