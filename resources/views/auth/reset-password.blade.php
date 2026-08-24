<x-guest-layout>
    <div class="mb-7">
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Atur Password Baru</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Buat password baru yang kuat untuk akun Anda.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4" x-data="{ show: false, showConfirm: false }">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email</label>
            <div class="relative">
                <i class="ri-mail-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                       class="w-full pl-10 pr-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                       placeholder="anda@email.com">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Password Baru</label>
            <div class="relative">
                <i class="ri-lock-password-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password" minlength="8"
                       class="w-full pl-10 pr-11 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                       placeholder="Minimal 8 karakter">
                <button type="button" @click="show = !show" tabindex="-1" aria-label="Tampilkan/sembunyikan password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                    <i class="ri-eye-off-line text-base" x-show="!show"></i>
                    <i class="ri-eye-line text-base" x-show="show" x-cloak></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
            <div class="relative">
                <i class="ri-lock-password-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" minlength="8"
                       class="w-full pl-10 pr-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                       placeholder="Ulangi password baru">
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <button type="submit"
                class="group w-full inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white font-semibold py-2.5 rounded-xl transition text-sm mt-2 shadow-sm shadow-primary-500/30">
            Simpan Password Baru
            <i class="ri-check-line text-base"></i>
        </button>
    </form>
</x-guest-layout>
