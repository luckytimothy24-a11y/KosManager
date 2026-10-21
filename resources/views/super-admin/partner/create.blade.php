<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Buat Partner Monetisasi" description="Daftarkan partner monetisasi baru untuk platform." icon="ri-hand-heart-line">
            <x-button href="{{ route('super-admin.partners.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <x-alert />

        <form method="POST" action="{{ route('super-admin.partners.store') }}" class="form-section space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Nama Partner *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="120" placeholder="DANA"
                           class="field-input">
                    @error('name')<p role="alert" class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" maxlength="130" placeholder="dana (kosong = otomatis)"
                           class="field-input">
                    @error('slug')<p role="alert" class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label">Logo URL</label>
                    <input type="url" name="logo" value="{{ old('logo') }}" maxlength="255" placeholder="https://..."
                           class="field-input">
                    @error('logo')<p role="alert" class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label">Website URL</label>
                    <input type="url" name="website_url" value="{{ old('website_url') }}" maxlength="500" placeholder="https://..."
                           class="field-input">
                    @error('website_url')<p role="alert" class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label">Tipe Monetisasi</label>
                    <select name="monetization_type" class="field-input">
                        <option value="">— Pilih —</option>
                        <option value="cpm" {{ old('monetization_type') === 'cpm' ? 'selected' : '' }}>CPM (per impressi)</option>
                        <option value="cpc" {{ old('monetization_type') === 'cpc' ? 'selected' : '' }}>CPC (per klik)</option>
                        <option value="deal" {{ old('monetization_type') === 'deal' ? 'selected' : '' }}>Kesepakatan paket</option>
                        <option value="contract" {{ old('monetization_type') === 'contract' ? 'selected' : '' }}>Kontrak</option>
                    </select>
                    @error('monetization_type')<p role="alert" class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label">Status</label>
                    <select name="status" class="field-input">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status')<p role="alert" class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="field-label">Deskripsi</label>
                <textarea name="description" rows="3" maxlength="2000" class="field-input">{{ old('description') }}</textarea>
                @error('description')<p role="alert" class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Nama Kontak</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name') }}" maxlength="120" placeholder="Nama PIC"
                           class="field-input">
                    @error('contact_name')<p role="alert" class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label">Email Kontak</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}" maxlength="190" placeholder="pic@partner.com"
                           class="field-input">
                    @error('contact_email')<p role="alert" class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 dark:border-white/[0.06]">
                <a href="{{ route('super-admin.partners.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Partner</button>
            </div>
        </form>
    </div>
</x-app-layout>
