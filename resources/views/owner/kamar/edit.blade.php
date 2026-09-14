<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $showUrl = route("$prefix.kamar.show", $kamar); $indexUrl = route("$prefix.kamar.index"); $hasExistingPhoto = !empty($kamar->photo) && file_exists(public_path('storage/'.$kamar->photo)); $kapasitasOld = (int) old('capacity', (int) $kamar->capacity); $kapasitasCustom = $kapasitasOld > 4 ? $kapasitasOld : ''; @endphp
    <x-slot name="header">
        <x-page-header title="Edit Kamar {{ $kamar->room_number }}" description="{{ $kamar->room_name }} Â· {{ $kamar->kos->name }}">
            <div class="flex items-center gap-2">
                <x-button href="{{ $showUrl }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
                <x-button href="{{ $indexUrl }}" type="secondary">Daftar Kamar</x-button>
            </div>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <x-alert />
        <form method="POST" action="{{ route("$prefix.kamar.update", $kamar) }}" enctype="multipart/form-data" class="space-y-6" x-data="{ photoPreview: null, submitting: false, kapasitas: '{{ $kapasitasCustom ? 'custom' : $kapasitasOld }}', custom: {{ $kapasitasCustom ? 'true' : 'false' }}, customNumber: '{{ $kapasitasCustom }}' }" x-on:submit="submitting = true">
            @csrf @method('PUT')

            {{-- Section: Informasi Kamar --}}
            <section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-door-open-line text-primary-600 dark:text-primary-400"></i></span>
                    Informasi Kamar
                </h3>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="room_number" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Nomor Kamar <span class="text-red-500">*</span></label>
                        <input id="room_number" type="text" name="room_number" value="{{ old('room_number', $kamar->room_number) }}" required
                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: 101">
                        @error('room_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="floor" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Lantai</label>
                        <input id="floor" type="text" name="floor" value="{{ old('floor', $kamar->floor) }}"
                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: 1">
                    </div>

                    <div>
                        <label for="room_name" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Nama Kamar <span class="text-red-500">*</span></label>
                        <input id="room_name" type="text" name="room_name" value="{{ old('room_name', $kamar->room_name) }}" required
                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: Kamar Melati">
                        @error('room_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="area" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Luas (mÂ²)</label>
                        <input id="area" type="number" name="area" value="{{ old('area', $kamar->area) }}" step="0.1" min="0"
                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: 12">
                        @error('area') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="room_type" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Tipe Kamar <span class="text-red-500">*</span></label>
                        <input id="room_type" type="text" name="room_type" value="{{ old('room_type', $kamar->room_type) }}" required
                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: Standar AC, Suite, Ekonomi">
                        @error('room_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Status</label>
                        <select id="status" name="status"
                                class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @foreach(['available', 'booked', 'occupied', 'maintenance'] as $s)
                                <option value="{{ $s }}" {{ old('status', $kamar->status) === $s ? 'selected' : '' }}>{{ \StatusLabels::kamarLabel($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            {{-- Section: Mode Tagihan --}}
            <section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-scales-3-line text-primary-600 dark:text-primary-400"></i></span>
                    Mode Tagihan
                </h3>
                @php $billingOld = old('billing_mode', $kamar->billing_mode ?? 'personal'); @endphp
                <fieldset>
                    <legend class="sr-only">Mode Tagihan</legend>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-start gap-3 px-4 py-3 rounded-xl border cursor-pointer transition {{ $billingOld === 'personal' ? 'border-primary-500 bg-primary-50/60 dark:bg-primary-500/10' : 'bg-white dark:bg-zinc-800/60 border-slate-200 dark:border-zinc-700 hover:border-primary-300' }}">
                            <input type="radio" name="billing_mode" value="personal" {{ $billingOld === 'personal' ? 'checked' : '' }}
                                   class="mt-1 text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-900 dark:text-white">Tagihan Pribadi</span>
                                <span class="block text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Digunakan untuk satu penghuni.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3 px-4 py-3 rounded-xl border cursor-pointer transition {{ $billingOld === 'shared' ? 'border-primary-500 bg-primary-50/60 dark:bg-primary-500/10' : 'bg-white dark:bg-zinc-800/60 border-slate-200 dark:border-zinc-700 hover:border-primary-300' }}">
                            <input type="radio" name="billing_mode" value="shared" {{ $billingOld === 'shared' ? 'checked' : '' }}
                                   class="mt-1 text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-900 dark:text-white">Patungan Kamar</span>
                                <span class="block text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Biaya kamar dapat dibagi antar penghuni.</span>
                            </span>
                        </label>
                    </div>
                    @error('billing_mode') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                </fieldset>
            </section>

            {{-- Section: Kapasitas --}}
            <section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-group-line text-primary-600 dark:text-primary-400"></i></span>
                    Kapasitas Kamar
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 -mt-2">Berapa orang yang dapat menghuni kamar ini?</p>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    @foreach([1, 2, 3, 4] as $n)
                        <label class="flex items-center justify-center gap-2 px-3 py-3 rounded-xl border cursor-pointer transition has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:dark:bg-primary-500/10 {{ $kapasitasOld === (int) $n && ! $kapasitasCustom ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10' : 'border-slate-200 dark:border-zinc-700' }}">
                            <input type="radio" name="kapasitasRadio" value="{{ $n }}" x-model="kapasitas" @click="custom = false"
                                   class="sr-only">
                            <span class="flex flex-col items-center gap-0.5">
                                <span class="text-lg font-black text-slate-800 dark:text-white">{{ $n }}</span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Orang</span>
                            </span>
                        </label>
                    @endforeach

                    <label class="flex items-center justify-center gap-2 px-3 py-3 rounded-xl border cursor-pointer transition has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:dark:bg-primary-500/10 {{ $kapasitasCustom ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10' : 'border-slate-200 dark:border-zinc-700' }}">
                        <input type="radio" name="kapasitasRadio" value="custom" x-model="kapasitas" @click="custom = true"
                               class="sr-only">
                        <span class="text-sm font-bold text-slate-600 dark:text-slate-300">Lainnya</span>
                    </label>
                </div>

                <div x-show="custom" x-cloak class="sm:max-w-xs">
                    <label for="kapasitasCustom" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Jumlah orang</label>
                    <input id="kapasitasCustom" type="number" min="1" max="20" x-model.number="customNumber"
                           class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                           placeholder="cth: 6">
                </div>

                <input type="hidden" name="capacity" :value="custom ? customNumber : kapasitas">
                @error('capacity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </section>

            {{-- Section: Harga --}}
            <section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-money-dollar-circle-line text-primary-600 dark:text-primary-400"></i></span>
                    Harga Sewa
                </h3>

                <div class="grid sm:grid-cols-3 gap-5">
                    <div>
                        <label for="daily_price" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Harga Harian <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 dark:text-slate-500">Rp</span>
                            <input id="daily_price" type="number" name="daily_price" value="{{ old('daily_price', $kamar->daily_price) }}" required min="0"
                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        @error('daily_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="weekly_price" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Harga Mingguan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 dark:text-slate-500">Rp</span>
                            <input id="weekly_price" type="number" name="weekly_price" value="{{ old('weekly_price', $kamar->weekly_price) }}" required min="0"
                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        @error('weekly_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="monthly_price" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Harga Bulanan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 dark:text-slate-500">Rp</span>
                            <input id="monthly_price" type="number" name="monthly_price" value="{{ old('monthly_price', $kamar->monthly_price) }}" required min="0"
                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        @error('monthly_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('description', $kamar->description) }}</textarea>
                </div>
            </section>

            {{-- Section: Fasilitas --}}
            <section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-4 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-sofa-line text-primary-600 dark:text-primary-400"></i></span>
                    Fasilitas
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2.5">
                    @foreach($fasilitasList as $f)
                        <label class="flex items-center gap-2.5 px-3 py-2 rounded-xl bg-slate-50 dark:bg-zinc-800/60 cursor-pointer hover:bg-primary-50 dark:hover:bg-primary-500/10 transition has-[:checked]:bg-primary-50 has-[:checked]:dark:bg-primary-500/10">
                            <input type="checkbox" name="fasilitas[]" value="{{ $f->id }}"
                                   {{ in_array($f->id, old('fasilitas', $selectedFasilitas)) ? 'checked' : '' }}
                                   class="rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-slate-700 dark:text-slate-200 truncate">{{ $f->name }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- Section: Foto --}}
            <section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-5 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-image-line text-primary-600 dark:text-primary-400"></i></span>
                    Foto Kamar
                </h3>

                <label for="photo"
                       class="flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-slate-200 dark:border-zinc-700 hover:border-primary-300 dark:hover:border-primary-500/40 px-6 py-8 cursor-pointer transition group text-center">
                    <template x-if="photoPreview">
                        <img :src="photoPreview" alt="Preview foto kamar baru" class="max-h-40 rounded-xl object-cover shadow-sm">
                    </template>
                    <template x-if="!photoPreview && {{ $hasExistingPhoto ? 'true' : 'false' }}">
                        <img src="{{ asset('storage/'.$kamar->photo) }}" alt="Foto kamar saat ini" class="max-h-40 rounded-xl object-cover shadow-sm">
                    </template>
                    <template x-if="!photoPreview && {{ $hasExistingPhoto ? 'false' : 'true' }}">
                        <span class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center group-hover:bg-primary-50 dark:group-hover:bg-primary-500/10 transition">
                            <i class="ri-upload-cloud-2-line text-xl text-slate-400 dark:text-slate-500"></i>
                        </span>
                    </template>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Klik untuk unggah foto baru</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">PNG, JPG hingga 2MB.</span>
                    <input type="file" id="photo" name="photo" accept="image/*" class="sr-only"
                           x-on:change="const f = $event.target.files[0]; if (f) { photoPreview = URL.createObjectURL(f) }">
                </label>
                @error('photo') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </section>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route("$prefix.kamar.show", $kamar) }}"
                   class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Batal</a>
                <button type="submit" :disabled="submitting"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="ri-save-line"></i> <span x-show="!submitting">Simpan Perubahan</span><span x-show="submitting" x-cloak>Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
