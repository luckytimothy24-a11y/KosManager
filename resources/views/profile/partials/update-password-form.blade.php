<section>
    <form method="post" action="{{ route('password.update') }}" class="space-y-5 max-w-xl" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <div class="relative mt-1.5">
                <x-text-input id="update_password_current_password" name="current_password" type="password" x-bind:type="showCurrent ? 'text' : 'password'" class="block w-full pr-11" autocomplete="current-password" placeholder="Password saat ini" />
                <button type="button" @click="showCurrent = !showCurrent" tabindex="-1" aria-label="Tampilkan/sembunyikan password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                    <i class="ri-eye-off-line" x-show="!showCurrent"></i>
                    <i class="ri-eye-line" x-show="showCurrent" x-cloak></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <div class="relative mt-1.5">
                <x-text-input id="update_password_password" name="password" type="password" x-bind:type="showNew ? 'text' : 'password'" class="block w-full pr-11" autocomplete="new-password" placeholder="Password baru (minimal 8 karakter)" />
                <button type="button" @click="showNew = !showNew" tabindex="-1" aria-label="Tampilkan/sembunyikan password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                    <i class="ri-eye-off-line" x-show="!showNew"></i>
                    <i class="ri-eye-line" x-show="showNew" x-cloak></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <div class="relative mt-1.5">
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" x-bind:type="showConfirm ? 'text' : 'password'" class="block w-full pr-11" autocomplete="new-password" placeholder="Ulangi password baru" />
                <button type="button" @click="showConfirm = !showConfirm" tabindex="-1" aria-label="Tampilkan/sembunyikan password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                    <i class="ri-eye-off-line" x-show="!showConfirm"></i>
                    <i class="ri-eye-line" x-show="showConfirm" x-cloak></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-1">
            <x-primary-button>
                <i class="ri-lock-password-line"></i> Perbarui Password
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 5000)"
                    role="status"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-600 dark:text-green-400"
                ><i class="ri-checkbox-circle-line"></i> Password berhasil diperbarui</p>
            @endif
        </div>
    </form>
</section>
