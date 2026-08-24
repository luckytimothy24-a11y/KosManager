<x-guest-layout>
    @if(session('status'))
        <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-green-200 bg-green-50 dark:border-green-500/20 dark:bg-green-500/10 px-3.5 py-3 text-sm text-green-700 dark:text-green-300">
            <i class="ri-checkbox-circle-line text-base mt-0.5 shrink-0"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div class="mb-7">
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Selamat datang kembali</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Masuk untuk mengelola kos Anda.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email</label>
            <div class="relative">
                <i class="ri-mail-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="w-full pl-10 pr-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                       placeholder="anda@email.com">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div x-data="{ show: false }">
            <label for="password" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Password</label>
            <div class="relative">
                <i class="ri-lock-password-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       class="w-full pl-10 pr-11 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                       placeholder="Masukkan password">
                <button type="button" @click="show = !show" aria-label="Tampilkan/sembunyikan password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition"
                        tabindex="-1">
                    <i class="ri-eye-off-line text-base" x-show="!show"></i>
                    <i class="ri-eye-line text-base" x-show="show" x-cloak></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:checked:bg-primary-500 text-primary-500 focus:ring-primary-500/10 cursor-pointer">
                <span class="text-sm text-slate-500 dark:text-slate-400">Ingat saya</span>
            </label>
            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs text-slate-500 dark:text-slate-400 hover:text-primary-500 font-medium transition">Lupa password?</a>
            @endif
        </div>

        <button type="submit"
                class="group w-full inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white font-semibold py-2.5 rounded-xl transition text-sm mt-2 shadow-sm shadow-primary-500/30">
            Masuk
            <i class="ri-arrow-right-line text-base transition-transform group-hover:translate-x-0.5"></i>
        </button>
    </form>

    @if(Route::has('register'))
        <p class="mt-7 text-center text-sm text-slate-400 dark:text-slate-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-primary-500 hover:text-primary-600 font-semibold transition">Daftar sekarang</a>
        </p>
    @endif
</x-guest-layout>
