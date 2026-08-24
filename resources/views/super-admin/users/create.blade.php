<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah User" description="Buat akun baru dengan role tertentu.">
            <x-button href="{{ route('super-admin.users.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
            <form method="POST" action="{{ route('super-admin.users.store') }}" class="space-y-5" x-data="{ show: false, showConfirm: false }">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Nama <span class="text-red-500">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                           placeholder="Nama lengkap">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                           placeholder="anda@email.com">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="role" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Role <span class="text-red-500">*</span></label>
                        <select id="role" name="role" required
                                class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @foreach(['super_admin' => 'Super Admin', 'admin' => 'Admin', 'owner' => 'Owner', 'tenant' => 'Tenant'] as $r => $rLabel)
                                <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ $rLabel }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Telepon</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}"
                               class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="08xxxxxxxxxx">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input id="password" :type="show ? 'text' : 'password'" name="password" required minlength="8" autocomplete="new-password"
                                   class="w-full pr-11 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                                   placeholder="Minimal 8 karakter">
                            <button type="button" @click="show = !show" tabindex="-1" aria-label="Tampilkan/sembunyikan password"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                                <i class="ri-eye-off-line" x-show="!show"></i>
                                <i class="ri-eye-line" x-show="show" x-cloak></i>
                            </button>
                        </div>
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required minlength="8" autocomplete="new-password"
                               class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="Ulangi password">
                        @error('password_confirmation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('super-admin.users.index') }}"
                       class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                        <i class="ri-save-line"></i> Simpan User
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
