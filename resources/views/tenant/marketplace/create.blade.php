<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Jual Barang Bekas" description="Jual barang bekas Anda." />
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 py-6">
        <form method="POST" action="{{ route('tenant.marketplace.store') }}" enctype="multipart/form-data" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-5">
            @csrf

            <div>
                <label class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                    <i class="ri-file-text-line text-primary-500"></i> Nama Barang
                </label>
                <input type="text" name="title" class="field-input" required maxlength="255" placeholder="Contoh: Kipas angin, Kasur, dll">
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                    <i class="ri-file-text-line text-primary-500"></i> Deskripsi
                </label>
                <textarea name="description" class="field-input" rows="3" maxlength="2000" placeholder="Jelaskan kondisi barang, alasan jual, dll..."></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                        <i class="ri-money-dollar-circle-line text-primary-500"></i> Harga (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-semibold text-sm">Rp</span>
                        <input type="number" name="price" class="field-input pl-10" required min="0" placeholder="0">
                    </div>
                </div>
                <div>
                    <label class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                        <i class="ri-checkbox-circle-line text-primary-500"></i> Kondisi
                    </label>
                    <select name="condition" class="field-input" required>
                        <option value="baru">Baru</option>
                        <option value="bekas" selected>Bekas</option>
                        <option value="rusak_ringan">Rusak Ringan</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                    <i class="ri-price-tag-line text-primary-500"></i> Kategori
                </label>
                <select name="category" class="field-input" required>
                    <option value="elektronik">Elektronik</option>
                    <option value="furniture">Furniture</option>
                    <option value="pakaian">Pakaian</option>
                    <option value="buku">Buku</option>
                    <option value="lainnya" selected>Lainnya</option>
                </select>
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                    <i class="ri-image-line text-primary-500"></i> Foto Barang
                </label>
                <div class="relative border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-6 text-center hover:border-primary-300 dark:hover:border-primary-500/40 transition-colors cursor-pointer" onclick="document.querySelector('input[name=image]').click()">
                    <i class="ri-image-line text-3xl text-slate-300 dark:text-slate-600"></i>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Klik untuk upload foto</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Maks 2MB. Format: JPG, PNG.</p>
                    <input type="file" name="image" accept="image/*" class="hidden">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('tenant.marketplace.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <i class="ri-add-line"></i> Jual Barang
                </button>
            </div>
        </form>
    </div>
</x-app-layout>