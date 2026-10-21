<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah User" description="Buat akun baru dengan role tertentu." icon="ri-user-add-line">
            <x-button href="{{ route('super-admin.users.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('super-admin.users.store') }}" class="form-section space-y-5" x-data="{ show: false, showConfirm: false }">
            @csrf

            <div>
                <label for="name" class="field-label">Nama <span class="text-red-500">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                       class="field-input"
                       placeholder="Nama lengkap">
                @error('name') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="field-label">Email <span class="text-red-500">*</span></label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                       class="field-input"
                       placeholder="anda@email.com">
                @error('email') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="role" class="field-label">Role <span class="text-red-500">*</span></label>
                    <select id="role" name="role" required class="field-input">
                        @foreach(['super_admin' => 'Super Admin', 'admin' => 'Admin', 'owner' => 'Owner', 'tenant' => 'Tenant'] as $r => $rLabel)
                            <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ $rLabel }}</option>
                        @endforeach
                    </select>
                    @error('role') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="field-label">Telepon</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}"
                           class="field-input"
                           placeholder="08xxxxxxxxxx">
                    @error('phone') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="password" class="field-label">Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="password" :type="show ? 'text' : 'password'" name="password" required minlength="8" autocomplete="new-password"
                               class="field-input pr-11"
                               placeholder="Minimal 8 karakter">
                        <button type="button" @click="show = !show" tabindex="-1" aria-label="Tampilkan/sembunyikan password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                            <i class="ri-eye-off-line" x-show="!show"></i>
                            <i class="ri-eye-line" x-show="show" x-cloak></i>
                        </button>
                    </div>
                    @error('password') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="field-label">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required minlength="8" autocomplete="new-password"
                           class="field-input"
                           placeholder="Ulangi password">
                    @error('password_confirmation') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-white/[0.06]">
                <a href="{{ route('super-admin.users.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <i class="ri-save-line"></i> Simpan User
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
