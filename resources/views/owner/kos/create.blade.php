<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Kos" description="Lengkapi informasi properti kos baru Anda." >
            <x-button href="{{ route('owner.kos.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <x-alert />
        <form method="POST" action="{{ route('owner.kos.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Section: Informasi Dasar --}}
            <section class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-information-line text-primary-600 dark:text-primary-400"></i></span>
                    Informasi Dasar
                </h3>

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Nama Kos <span class="text-red-500">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('name') ? 'border-red-400' : '' }}"
                           placeholder="cth: Kost Melati Indah">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Alamat <span class="text-red-500">*</span></label>
                    <textarea id="address" name="address" rows="2" required
                              class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('address') ? 'border-red-400' : '' }}"
                              placeholder="Alamat lengkap kos">{{ old('address') }}</textarea>
                    @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                              placeholder="Ceritakan tentang kos Anda">{{ old('description') }}</textarea>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Telepon <span class="text-red-500">*</span></label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required
                               class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="08xxxxxxxxxx">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Status</label>
                        <select id="status" name="status"
                                class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="active">Aktif — tampil &amp; bisa dibooking</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </section>

            {{-- Section: Foto --}}
            <section class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8"
                     x-data="{ previewUrl: null }">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-image-line text-primary-600 dark:text-primary-400"></i></span>
                    Foto Kos
                </h3>

                <label for="photo"
                       class="flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-500/40 px-6 py-8 cursor-pointer transition group text-center">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Preview foto kos" class="max-h-40 rounded-xl object-cover shadow-sm">
                    </template>
                    <template x-if="!previewUrl">
                        <span class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover:bg-primary-50 dark:group-hover:bg-primary-500/10 transition">
                            <i class="ri-upload-cloud-2-line text-xl text-slate-400 dark:text-slate-500"></i>
                        </span>
                    </template>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Klik untuk unggah foto</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">PNG, JPG hingga 2MB. Foto pertama menjadi cover kos.</span>
                    <input type="file" id="photo" name="photo" accept="image/*" class="sr-only"
                           x-on:change="const f = $event.target.files[0]; if (f) { previewUrl = URL.createObjectURL(f) }">
                </label>
                @error('photo') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </section>

            {{-- Section: Detail Tambahan --}}
            <section class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-sofa-line text-primary-600 dark:text-primary-400"></i></span>
                    Fasilitas, Aturan &amp; Pembayaran
                </h3>

                <div>
                    <label for="general_facilities" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Fasilitas Umum</label>
                    <textarea id="general_facilities" name="general_facilities" rows="2"
                              class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                              placeholder="cth: WiFi, Dapur bersama, Parkir motor">{{ old('general_facilities') }}</textarea>
                </div>

                <div>
                    <label for="rules" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Peraturan Kos</label>
                    <textarea id="rules" name="rules" rows="3"
                              class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                              placeholder="Satu peraturan per baris">{{ old('rules') }}</textarea>
                </div>

                <div>
                    <label for="payment_info" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Informasi Pembayaran</label>
                    <textarea id="payment_info" name="payment_info" rows="3"
                              placeholder="Contoh:{{ "\n" }}Transfer Bank BCA 1234567890 a.n. KosManager{{ "\n" }}QRIS tersedia di loket"
                              class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('payment_info') }}</textarea>
                    <p class="mt-1.5 flex items-start gap-1.5 text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
                        <i class="ri-eye-line mt-0.5 shrink-0"></i> Ditampilkan ke penghuni saat mereka melakukan pembayaran tagihan.
                    </p>
                    @error('payment_info') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </section>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('owner.kos.index') }}"
                   class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Batal</a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                    <i class="ri-save-line"></i> Simpan Kos
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
