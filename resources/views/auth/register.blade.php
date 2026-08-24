<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Daftar Akun</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Buat akun baru untuk memulai</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ show: false }">
        @csrf

        <div>
            <label for="name" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Nama</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                   placeholder="Nama lengkap">
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                   placeholder="anda@email.com">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Password</label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password" minlength="8"
                       class="w-full px-3.5 pr-11 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
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
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" minlength="8"
                   class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                   placeholder="Ulangi password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <button type="submit"
                x-data="{ submitting: false }"
                x-on:click="if ($el.form.checkValidity()) { submitting = true }"
                class="group w-full inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600 active:bg-primary-700 disabled:bg-primary-400 text-white font-semibold py-2.5 rounded-xl transition text-sm mt-2 shadow-sm shadow-primary-500/30"
                :disabled="submitting">
            <svg x-show="submitting" x-cloak class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            Daftar Sekarang
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-400 dark:text-slate-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="text-primary-500 hover:text-primary-600 font-semibold transition">Masuk</a>
    </p>
</x-guest-layout>
