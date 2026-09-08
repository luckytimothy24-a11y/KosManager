<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <x-alert />

        <div class="flex items-center gap-3">
            <a href="{{ route('super-admin.partners.index') }}" class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-arrow-left-line"></i>
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Edit Partner</h1>
        </div>

        <form method="POST" action="{{ route('super-admin.partners.update', $partner) }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama Partner *</label>
                    <input type="text" name="name" value="{{ old('name', $partner->name) }}" required maxlength="120" placeholder="DANA"
                           class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $partner->slug) }}" maxlength="130" placeholder="dana (kosong = otomatis)"
                           class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    @error('slug')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Logo URL</label>
                    <input type="url" name="logo" value="{{ old('logo', $partner->logo) }}" maxlength="255" placeholder="https://..."
                           class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    @error('logo')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Website URL</label>
                    <input type="url" name="website_url" value="{{ old('website_url', $partner->website_url) }}" maxlength="500" placeholder="https://..."
                           class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    @error('website_url')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tipe Monetisasi</label>
                    <select name="monetization_type" class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">— Pilih —</option>
                        <option value="cpm" {{ old('monetization_type', $partner->monetization_type) === 'cpm' ? 'selected' : '' }}>CPM (per impressi)</option>
                        <option value="cpc" {{ old('monetization_type', $partner->monetization_type) === 'cpc' ? 'selected' : '' }}>CPC (per klik)</option>
                        <option value="deal" {{ old('monetization_type', $partner->monetization_type) === 'deal' ? 'selected' : '' }}>Kesepakatan paket</option>
                        <option value="contract" {{ old('monetization_type', $partner->monetization_type) === 'contract' ? 'selected' : '' }}>Kontrak</option>
                    </select>
                    @error('monetization_type')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Status</label>
                    <select name="status" class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="active" {{ old('status', $partner->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $partner->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Deskripsi</label>
                <textarea name="description" rows="3" maxlength="2000" class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">{{ old('description', $partner->description) }}</textarea>
                @error('description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama Kontak</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name', $partner->contact_name) }}" maxlength="120" placeholder="Nama PIC"
                           class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    @error('contact_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Email Kontak</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $partner->contact_email) }}" maxlength="190" placeholder="pic@partner.com"
                           class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    @error('contact_email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('super-admin.partners.index') }}" class="text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-5 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Batal</a>
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-bold text-white bg-primary-500 hover:bg-primary-600 px-6 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-app-layout>
