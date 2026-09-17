<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Kos" description="Lengkapi informasi properti kos baru Anda." >
            <x-button href="{{ route('owner.kos.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <x-alert />
        <form method="POST" action="{{ route('owner.kos.store') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf

            {{-- Section: Informasi Dasar --}}
            <section class="bg-white dark:bg-slate-700 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-600 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-slate-600">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-information-line text-primary-600 dark:text-primary-400"></i></span>
                    Informasi Dasar
                </h3>

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Nama Kos <span class="text-red-500">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('name') ? 'border-red-400' : '' }}"
                           placeholder="cth: Kost Melati Indah">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Alamat <span class="text-red-500">*</span></label>
                    <textarea id="address" name="address" rows="2" required
                              class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('address') ? 'border-red-400' : '' }}"
                              placeholder="Alamat lengkap kos">{{ old('address') }}</textarea>
                    @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                              placeholder="Ceritakan tentang kos Anda">{{ old('description') }}</textarea>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Telepon <span class="text-red-500">*</span></label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required
                               class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="08xxxxxxxxxx">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Status</label>
                        <select id="status" name="status"
                                class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="active">Aktif — tampil &amp; bisa dibooking</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>

                @if($owners->isNotEmpty())
                    <div>
                        <label for="owner_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Pemilik Kos <span class="text-red-500">*</span></label>
                        <select id="owner_id" name="owner_id" required
                                class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- Pilih pemilik kos --</option>
                            @foreach($owners as $owner)
                                <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>{{ $owner->name }} ({{ $owner->email }})</option>
                            @endforeach
                        </select>
                        @error('owner_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif
            </section>

            {{-- Section: Foto --}}
            <section class="bg-white dark:bg-slate-700 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-600 p-6 sm:p-8" x-data="kosGalleryCreate()">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-5 border-b border-slate-100 dark:border-slate-600">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-image-line text-primary-600 dark:text-primary-400"></i></span>
                    Foto Kos
                </h3>

                <label for="photos"
                       class="flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-600 hover:border-primary-300 dark:hover:border-primary-500/40 px-6 py-8 cursor-pointer transition group text-center">
                    <span class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover:bg-primary-50 dark:group-hover:bg-primary-500/10 transition">
                        <i class="ri-upload-cloud-2-line text-xl text-slate-400 dark:text-slate-500"></i>
                    </span>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Klik untuk unggah foto</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">PNG, JPG, WEBP hingga 2MB per foto. Maksimal 10 foto. Foto pertama menjadi cover kos.</span>
                    <input type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="sr-only"
                           x-ref="photosInput" @change="handleFiles($event)">
                </label>
                <p class="mt-2 text-[11px] text-slate-400 dark:text-slate-500"><span x-text="previews.length"></span>/10 foto dipilih. Klik cover untuk memilih cover.</p>
                @error('photos') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                @error('photos.*') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                @error('cover_index') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                {{-- Legacy single photo compat --}}
                <input type="hidden" name="cover_index" :value="coverIndex">

                <div x-show="previews.length > 0" x-cloak class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <template x-for="(p, idx) in previews" :key="idx">
                        <div class="relative rounded-xl overflow-hidden border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800">
                            <img :src="p.url" :alt="'Preview ' + (idx+1)" class="w-full h-28 object-cover">
                            <button type="button" @click="removePreview(idx)" class="absolute top-1.5 right-1.5 w-7 h-7 rounded-full bg-black/60 hover:bg-red-600 text-white flex items-center justify-center transition" aria-label="Hapus preview">
                                <i class="ri-close-line text-sm"></i>
                            </button>
                            <label class="absolute bottom-1.5 left-1.5 flex items-center gap-1 bg-black/60 backdrop-blur text-white text-[11px] font-semibold px-2 py-1 rounded-lg cursor-pointer">
                                <input type="radio" name="cover_radio" :value="idx" :checked="coverIndex === idx" @change="coverIndex = idx" class="sr-only">
                                <span :class="coverIndex === idx ? 'bg-primary-500 text-white' : 'bg-white/20'" class="w-3 h-3 rounded-full border border-white/40 flex items-center justify-center">
                                    <span x-show="coverIndex === idx" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                </span>
                                Cover
                            </label>
                            <span x-show="coverIndex === idx" class="absolute top-1.5 left-1.5 text-[10px] font-bold bg-primary-500 text-white px-2 py-0.5 rounded-md">COVER</span>
                        </div>
                    </template>
                </div>
            </section>
            <script>
                function kosGalleryCreate() {
                    return {
                        previews: [],
                        coverIndex: 0,
                        filesList: [],
                        handleFiles(event) {
                            const files = Array.from(event.target.files || []);
                            const remaining = 10 - this.previews.length;
                            const toAdd = files.slice(0, remaining);
                            toAdd.forEach(f => {
                                const url = URL.createObjectURL(f);
                                this.previews.push({ url, file: f, name: f.name });
                                this.filesList.push(f);
                            });
                            this.syncInput();
                        },
                        removePreview(idx) {
                            if (this.previews[idx]?.url) URL.revokeObjectURL(this.previews[idx].url);
                            this.previews.splice(idx, 1);
                            this.filesList.splice(idx, 1);
                            if (this.coverIndex >= this.previews.length) this.coverIndex = Math.max(0, this.previews.length - 1);
                            if (this.coverIndex > idx) this.coverIndex--;
                            this.syncInput();
                        },
                        syncInput() {
                            const dt = new DataTransfer();
                            this.filesList.forEach(f => dt.items.add(f));
                            this.$refs.photosInput.files = dt.files;
                        }
                    };
                }
            </script>

            {{-- Section: Detail Tambahan --}}
            <section class="bg-white dark:bg-slate-700 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-600 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-slate-600">
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
                        <input id="latitude" type="number" step="any" name="latitude" value="{{ old('latitude') }}"
                               class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('latitude') ? 'border-red-400' : '' }}"
                               placeholder="cth: -7.7955798" min="-90" max="90">
                        @error('latitude') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="longitude" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Longitude</label>
                        <input id="longitude" type="number" step="any" name="longitude" value="{{ old('longitude') }}"
                               class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('longitude') ? 'border-red-400' : '' }}"
                               placeholder="cth: 110.4052787" min="-180" max="180">
                        @error('longitude') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Section: Detail Tambahan --}}
            <section class="bg-white dark:bg-slate-700 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-600 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-slate-600">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-sofa-line text-primary-600 dark:text-primary-400"></i></span>
                    Fasilitas, Aturan &amp; Pembayaran
                </h3>

                <div>
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-3">Fasilitas Umum</span>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-3">Pilih fasilitas yang tersedia untuk seluruh kos.</p>
                    @if($fasilitasList->isEmpty())
                        <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-600 p-4 text-sm text-slate-400 dark:text-slate-500">
                            Belum ada fasilitas umum. Admin dapat menambahkannya pada master fasilitas.
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($fasilitasList as $f)
                                <label class="relative flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-primary-300 dark:hover:border-primary-500/40 cursor-pointer transition group">
                                    <input type="checkbox" name="fasilitas[]" value="{{ $f->id }}" {{ in_array($f->id, old('fasilitas', [])) ? 'checked' : '' }}
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
                              class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                              placeholder="Satu peraturan per baris">{{ old('rules') }}</textarea>
                </div>

                <div>
                    <label for="payment_info" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Informasi Pembayaran</label>
                    <textarea id="payment_info" name="payment_info" rows="3"
                              placeholder="Contoh:{{ "\n" }}Pembayaran tunai langsung kepada pengelola di loket kos,{{ "\n" }}hubungi 0812xxxxxxx untuk konfirmasi."
                              class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('payment_info') }}</textarea>
                    <p class="mt-1.5 flex items-start gap-1.5 text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
                        <i class="ri-eye-line mt-0.5 shrink-0"></i> Ditampilkan ke penghuni saat mereka melakukan pembayaran tagihan.
                    </p>
                    @error('payment_info') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </section>

            {{-- Pemisah: Langkah Selanjutnya — Tambah Anak Kos --}}
            <section class="rounded-2xl border-2 border-dashed border-primary-200 dark:border-primary-500/30 bg-primary-50/40 dark:bg-primary-500/[0.06] p-6 sm:p-7">
                <div class="flex items-start gap-3">
                    <span class="w-10 h-10 rounded-xl bg-primary-500 text-white flex items-center justify-center shrink-0"><i class="ri-team-line text-xl"></i></span>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Langkah selanjutnya: Tambah Anak Kos</h3>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">Penghuni diatur <span class="font-semibold text-slate-700 dark:text-slate-200">per Kamar</span> (bukan per Kos). Setelah Kos disimpan, atur <span class="font-semibold">Kapasitas 1–4 orang</span> &amp; <span class="font-semibold">Patungan (tagihan pisah per anak)</span> di Kelola Kamar.</p>
                        <ol class="mt-3 space-y-1.5 text-xs text-slate-600 dark:text-slate-300 list-decimal list-inside">
                            <li>Simpan Kos ini dulu</li>
                            <li>Buka <span class="font-semibold">Kelola Kamar → Tambah Kamar</span> → pilih Kapasitas <span class="font-semibold">2</span> &amp; Mode <span class="font-semibold">Patungan</span></li>
                            <li>Di <span class="font-semibold">Detail Kamar</span> klik <span class="font-semibold">+ Tambah Penghuni</span> (Lucky Rp600.000 + Budi Rp600.000 = Rp1.200.000)</li>
                        </ol>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('owner.kamar.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 transition"><i class="ri-door-open-line"></i> Kelola Kamar</a>
                            <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 dark:text-slate-500"><i class="ri-information-line"></i> Tombol aktif setelah Kos disimpan</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('owner.kos.index') }}"
                   class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Batal</a>
                <button type="submit" :disabled="submitting"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="ri-save-line"></i> <span x-show="!submitting">Simpan Kos</span><span x-show="submitting" x-cloak>Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
