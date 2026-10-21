<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit User" description="{{ $user->email }}" icon="ri-user-settings-line">
            <x-button href="{{ route('super-admin.users.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <div class="card-surface p-6 flex items-center gap-4">
            <span class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 font-bold text-lg flex items-center justify-center uppercase shrink-0">
                {{ mb_substr($user->name, 0, 1) }}
            </span>
            <div class="min-w-0">
                <p class="font-bold text-slate-900 dark:text-slate-50 truncate">{{ $user->name }}</p>
                <p class="text-sm text-slate-400 dark:text-slate-500 truncate">{{ $user->email }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('super-admin.users.update', $user) }}" class="form-section space-y-5">
            @csrf @method('PUT')

            <div>
                <label for="name" class="field-label">Nama <span class="text-red-500">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"
                       class="field-input">
                @error('name') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="field-label">Email <span class="text-red-500">*</span></label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email"
                       class="field-input">
                @error('email') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="role" class="field-label">Role <span class="text-red-500">*</span></label>
                    <select id="role" name="role" required class="field-input">
                        @foreach(['super_admin' => 'Super Admin', 'admin' => 'Admin', 'owner' => 'Owner', 'tenant' => 'Tenant'] as $r => $rLabel)
                            <option value="{{ $r }}" {{ old('role', $user->role) === $r ? 'selected' : '' }}>{{ $rLabel }}</option>
                        @endforeach
                    </select>
                    @error('role') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="field-label">Telepon</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="field-input"
                           placeholder="08xxxxxxxxxx">
                    @error('phone') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06]">
                <input type="hidden" name="is_active" value="0">
                <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                       class="mt-0.5 rounded border-slate-300 dark:border-white/[0.08] text-primary-500 focus:ring-primary-500 cursor-pointer">
                <label for="is_active" class="cursor-pointer select-none">
                    <span class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Akun Aktif</span>
                    <span class="block text-xs text-slate-400 dark:text-slate-500 mt-0.5">User nonaktif tidak dapat login ke sistem.</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-white/[0.06]">
                <a href="{{ route('super-admin.users.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <i class="ri-save-line"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
