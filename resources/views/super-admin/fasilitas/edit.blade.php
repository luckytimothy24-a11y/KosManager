<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Fasilitas" description="{{ $fasilitas->name }}" icon="ri-archive-drawer-line">
            <x-button href="{{ route('super-admin.fasilitas.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-xl">
        <form method="POST" action="{{ route('super-admin.fasilitas.update', $fasilitas) }}" class="form-section space-y-5"
              x-data="{ icon: @js(old('icon', $fasilitas->icon)) }">
            @csrf @method('PUT')

            <div>
                <span class="field-label">Tipe Fasilitas <span class="text-red-500">*</span></span>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition {{ old('type', $fasilitas->type) === 'kamar' ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-500/10' : 'border-slate-200 dark:border-white/[0.08] hover:border-slate-300 dark:hover:border-slate-600' }}">
                        <input type="radio" name="type" value="kamar" {{ old('type', $fasilitas->type) === 'kamar' ? 'checked' : '' }} class="sr-only">
                        <i class="ri-door-open-line text-xl text-primary-500"></i>
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Fasilitas Kamar</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">Per kamar (AC, kasur, lemari)</p>
                        </div>
                    </label>
                    <label class="relative flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition {{ old('type', $fasilitas->type) === 'kos' ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-500/10' : 'border-slate-200 dark:border-white/[0.08] hover:border-slate-300 dark:hover:border-slate-600' }}">
                        <input type="radio" name="type" value="kos" {{ old('type', $fasilitas->type) === 'kos' ? 'checked' : '' }} class="sr-only">
                        <i class="ri-home-4-line text-xl text-primary-500"></i>
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Fasilitas Umum Kos</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">Seluruh kos (WiFi, parkir, dapur)</p>
                        </div>
                    </label>
                </div>
                @error('type') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="name" class="field-label">Nama <span class="text-red-500">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name', $fasilitas->name) }}" required
                       class="field-input"
                       placeholder="cth: WiFi, AC, Kamar Mandi Dalam">
                @error('name') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="field-label">Deskripsi</label>
                <textarea id="description" name="description" rows="2"
                          class="field-input"
                          placeholder="Penjelasan singkat (opsional)">{{ old('description', $fasilitas->description) }}</textarea>
                @error('description') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="icon" class="field-label">
                    Icon <span class="font-normal text-xs text-slate-400 dark:text-slate-500">(nama ikon Remix Icon)</span>
                </label>
                <div class="relative">
                    <input id="icon" type="text" name="icon" value="{{ old('icon', $fasilitas->icon) }}" x-model="icon"
                           class="field-input pr-14 font-mono"
                           placeholder="cth: ri-wifi-line">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg bg-slate-100 dark:bg-white/[0.06] flex items-center justify-center overflow-hidden" aria-hidden="true">
                        <i :class="icon || 'ri-question-line'" class="text-slate-500 dark:text-slate-400"></i>
                    </span>
                </div>
                <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
                    Kosongkan untuk memakai icon default. Lihat katalog di
                    <a href="https://remixicon.com" target="_blank" rel="noopener noreferrer" class="text-primary-500 hover:text-primary-600 underline underline-offset-2">remixicon.com</a>.
                </p>
                @error('icon') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="category" class="field-label">Kategori</label>
                <select id="category" name="category" class="field-input">
                    <option value="">— Tanpa kategori —</option>
                    <option value="room" {{ old('category', $fasilitas->category) === 'room' ? 'selected' : '' }}>Fasilitas Kamar</option>
                    <option value="bathroom" {{ old('category', $fasilitas->category) === 'bathroom' ? 'selected' : '' }}>Kamar Mandi</option>
                    <option value="common" {{ old('category', $fasilitas->category) === 'common' ? 'selected' : '' }}>Fasilitas Bersama</option>
                    <option value="parking" {{ old('category', $fasilitas->category) === 'parking' ? 'selected' : '' }}>Parkir</option>
                    <option value="security" {{ old('category', $fasilitas->category) === 'security' ? 'selected' : '' }}>Keamanan</option>
                    <option value="service" {{ old('category', $fasilitas->category) === 'service' ? 'selected' : '' }}>Layanan</option>
                </select>
                @error('category') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-2">
                <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $fasilitas->is_active) ? 'checked' : '' }}
                       class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                <label for="is_active" class="text-sm text-slate-700 dark:text-slate-200">Aktif (dapat dipilih saat mengelola kos/kamar)</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-white/[0.06]">
                <a href="{{ route('super-admin.fasilitas.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <i class="ri-save-line"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
