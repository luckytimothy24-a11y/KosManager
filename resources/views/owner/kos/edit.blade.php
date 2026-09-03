<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Kos" description="{{ $kos->name }}">
            <div class="flex items-center gap-2">
                <x-button href="{{ route('owner.kos.show', $kos) }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
                <x-button href="{{ route('owner.kos.index') }}" type="secondary">Daftar Kos</x-button>
            </div>
        </x-page-header>
    </x-slot>

    @php
        $hasExistingPhoto = !empty($kos->photo) && file_exists(public_path('storage/'.$kos->photo));
    @endphp

    <div class="max-w-2xl">
        <x-alert />
        <form method="POST" action="{{ route('owner.kos.update', $kos) }}" enctype="multipart/form-data" class="space-y-6" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf @method('PUT')

            {{-- Section: Informasi Dasar --}}
            <section class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-information-line text-primary-600 dark:text-primary-400"></i></span>
                    Informasi Dasar
                </h3>

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Nama Kos <span class="text-red-500">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name', $kos->name) }}" required
                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('name') ? 'border-red-400' : '' }}"
                           placeholder="cth: Kost Melati Indah">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Alamat <span class="text-red-500">*</span></label>
                    <textarea id="address" name="address" rows="2" required
                              class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('address') ? 'border-red-400' : '' }}">{{ old('address', $kos->address) }}</textarea>
                    @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('description', $kos->description) }}</textarea>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Telepon <span class="text-red-500">*</span></label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone', $kos->phone) }}" required
                               class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="08xxxxxxxxxx">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Status</label>
                        <select id="status" name="status"
                                class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="active" {{ $kos->status === 'active' ? 'selected' : '' }}>Aktif — tampil &amp; bisa dibooking</option>
                            <option value="inactive" {{ $kos->status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>
            </section>

            {{-- Section: Foto --}}
            <section class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8"
                     x-data="{ hasExisting: {{ $hasExistingPhoto ? 'true' : 'false' }}, previewUrl: null }">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-image-line text-primary-600 dark:text-primary-400"></i></span>
                    Foto Kos
                </h3>

                <label for="photo"
                       class="flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-500/40 px-6 py-8 cursor-pointer transition group text-center">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Preview foto kos baru" class="max-h-40 rounded-xl object-cover shadow-sm">
                    </template>
                    <template x-if="!previewUrl && hasExisting">
                        <img src="{{ asset('storage/'.$kos->photo) }}" alt="Foto kos saat ini" class="max-h-40 rounded-xl object-cover shadow-sm">
                    </template>
                    <template x-if="!previewUrl && !hasExisting">
                        <span class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover:bg-primary-50 dark:group-hover:bg-primary-500/10 transition">
                            <i class="ri-upload-cloud-2-line text-xl text-slate-400 dark:text-slate-500"></i>
                        </span>
                    </template>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300" x-text="hasExisting && !previewUrl ? 'Ganti foto kos' : 'Klik untuk unggah foto'"></span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">PNG, JPG hingga 2MB.</span>
                    <input type="file" id="photo" name="photo" accept="image/*" class="sr-only"
                           x-on:change="const f = $event.target.files[0]; if (f) { previewUrl = URL.createObjectURL(f) }">
                </label>
                @error('photo') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </section>

            {{-- Section: Lokasi --}}
            <section class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-map-pin-line text-primary-600 dark:text-primary-400"></i></span>
                    Lokasi Kos
                </h3>

                <p class="text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
                    Masukkan koordinat lokasi kos. Koordinat dapat diperoleh dari
                    <a href="https://www.google.com/maps" target="_blank" rel="noopener noreferrer" class="text-primary-500 hover:text-primary-600 underline">Google Maps</a>
                    dengan klik kanan pada lokasi → koordinat.
                </p>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Latitude</label>
                        <input id="latitude" type="number" step="any" name="latitude" value="{{ old('latitude', $kos->latitude) }}"
                               class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('latitude') ? 'border-red-400' : '' }}"
                               placeholder="cth: -7.7955798" min="-90" max="90">
                        @error('latitude') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="longitude" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Longitude</label>
                        <input id="longitude" type="number" step="any" name="longitude" value="{{ old('longitude', $kos->longitude) }}"
                               class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('longitude') ? 'border-red-400' : '' }}"
                               placeholder="cth: 110.4052787" min="-180" max="180">
                        @error('longitude') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if($kos->latitude && $kos->longitude)
                    <div class="flex items-center gap-3 mt-2">
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($kos->latitude . ',' . $kos->longitude) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-500 hover:text-primary-600 transition">
                            <i class="ri-map-pin-2-fill"></i> Lihat di Google Maps
                        </a>
                    </div>
                @endif
            </section>

            {{-- Section: Detail Tambahan --}}
            <section class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-sofa-line text-primary-600 dark:text-primary-400"></i></span>
                    Fasilitas, Aturan &amp; Pembayaran
                </h3>

                <div>
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-3">Fasilitas Umum</span>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-3">Pilih fasilitas yang tersedia untuk seluruh kos.</p>
                    @if($fasilitasList->isEmpty())
                        <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-4 text-sm text-slate-400 dark:text-slate-500">
                            Belum ada fasilitas umum. Admin dapat menambahkannya pada master fasilitas.
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($fasilitasList as $f)
                                <label class="relative flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-500/40 cursor-pointer transition group">
                                    <input type="checkbox" name="fasilitas[]" value="{{ $f->id }}" {{ in_array($f->id, old('fasilitas', $selectedFasilitas)) ? 'checked' : '' }}
                                           class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                    @if($f->icon)
                                        <i class="{{ str_starts_with($f->icon, 'ri-') ? $f->icon : 'ri-'.$f->icon.'-line' }} text-slate-400 group-hover:text-primary-500 transition"></i>
                                    @else
                                        <i class="ri-checkbox-line text-slate-400 group-hover:text-primary-500 transition"></i>
                                    @endif
                                    <span class="text-sm text-slate-700 dark:text-slate-200">{{ $f->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    @error('fasilitas') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="rules" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Peraturan Kos</label>
                    <textarea id="rules" name="rules" rows="3"
                              class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('rules', $kos->rules) }}</textarea>
                </div>

                <div>
                    <label for="payment_info" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Informasi Pembayaran</label>
                    <textarea id="payment_info" name="payment_info" rows="3"
                              placeholder="Contoh:{{ "\n" }}Transfer Bank BCA 1234567890 a.n. KosManager{{ "\n" }}QRIS tersedia di loket"
                              class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('payment_info', $kos->payment_info) }}</textarea>
                    <p class="mt-1.5 flex items-start gap-1.5 text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
                        <i class="ri-eye-line mt-0.5 shrink-0"></i> Ditampilkan ke penghuni saat mereka melakukan pembayaran tagihan.
                    </p>
                    @error('payment_info') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </section>

            @if(!auth()->user()->isAdmin())
                {{-- Section: Admin yang Ditugaskan --}}
                <section class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 space-y-5">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-slate-800">
                        <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-user-settings-line text-primary-600 dark:text-primary-400"></i></span>
                        Admin Pengelola
                    </h3>

                    <div>
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-3">Pilih admin yang berhak mengelola kos ini</span>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mb-3">Admin terpilih dapat melihat dan mengelola kos ini dari dashboard mereka.</p>
                        @if($admins->isEmpty())
                            <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-4 text-sm text-slate-400 dark:text-slate-500">
                                Belum ada admin. Tambahkan admin pada menu kelola user.
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($admins as $admin)
                                    <label class="relative flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-500/40 cursor-pointer transition group">
                                        <input type="checkbox" name="admins[]" value="{{ $admin->id }}" {{ in_array($admin->id, old('admins', $selectedAdmins)) ? 'checked' : '' }}
                                               class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                        <span class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-primary-600 dark:text-primary-400 font-bold text-xs flex items-center justify-center uppercase shrink-0">
                                            {{ mb_substr($admin->name, 0, 1) }}
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-sm text-slate-700 dark:text-slate-200 truncate">{{ $admin->name }}</span>
                                            <span class="block text-xs text-slate-400 dark:text-slate-500 truncate">{{ $admin->email }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                        @error('admins') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </section>
            @endif

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('owner.kos.show', $kos) }}"
                   class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Batal</a>
                <button type="submit" :disabled="submitting"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="ri-save-line"></i> <span x-show="!submitting">Simpan Perubahan</span><span x-show="submitting" x-cloak>Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
