<x-guest-layout>
    <div class="flex min-h-0 flex-col items-center justify-center px-0 py-6 sm:min-h-[85vh] sm:px-4 sm:py-0">
        {{-- Logo & Header --}}
        <div class="mb-6 text-center fade-up animate-delay-1 sm:mb-8">
            <a href="/" class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-600 shadow-lg shadow-primary-600/25 sm:h-14 sm:w-14">
                <img src="{{ asset('favicon.svg') }}" alt="KosManager" class="h-7 w-7 brightness-0 invert sm:h-8 sm:w-8">
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">Selamat Datang Kembali</h1>
            <p class="text-sm text-slate-400 dark:text-slate-500 mt-2">Masuk untuk mengelola atau cari kos Anda</p>
        </div>

        {{-- Alert Error --}}
        @if(session('error'))
            <div class="w-full max-w-sm mb-4 flex items-start gap-2.5 rounded-2xl border border-red-200/60 bg-red-50/80 dark:border-red-500/15 dark:bg-red-500/5 px-4 py-3 text-sm text-red-600 dark:text-red-400 fade-up animate-delay-2">
                <i class="ri-error-warning-line text-base mt-0.5 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Alert Status --}}
        @if(session('status'))
            <div class="w-full max-w-sm mb-4 flex items-start gap-2.5 rounded-2xl border border-emerald-200/60 bg-emerald-50/80 dark:border-emerald-500/15 dark:bg-emerald-500/5 px-4 py-3 text-sm text-emerald-600 dark:text-emerald-400 fade-up animate-delay-2">
                <i class="ri-checkbox-circle-line text-base mt-0.5 shrink-0"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        {{-- Login Form Card --}}
        <div class="w-full max-w-sm rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/60 fade-up animate-delay-3 dark:border-white/[0.08] dark:bg-surface-elevated dark:shadow-black/30 sm:p-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email Field --}}
                <div class="fade-up animate-delay-3">
                    <label for="email" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">Email</label>
                    <div class="relative">
                        <i class="ri-mail-line absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 dark:text-slate-500 text-base"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username"
                               class="w-full pl-11 pr-4 py-3.5 bg-surface-card dark:bg-white/[0.06] border border-slate-200 dark:border-white/[0.14] rounded-2xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/15 focus:shadow-md transition-all duration-200 outline-none"
                               placeholder="anda@email.com">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                {{-- Password Field --}}
                <div x-data="{ show: false }" class="fade-up animate-delay-4">
                    <label for="password" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">Password</label>
                    <div class="relative">
                        <i class="ri-lock-password-line absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 dark:text-slate-500 text-base"></i>
                        <input id="password" :type="show ? 'text' : 'password'" name="password" autocomplete="current-password"
                               class="w-full pl-11 pr-12 py-3.5 bg-surface-card dark:bg-white/[0.06] border border-slate-200 dark:border-white/[0.14] rounded-2xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/15 focus:shadow-md transition-all duration-200 outline-none"
                               placeholder="Masukkan password">
                        <button type="button" @click="show = !show" aria-label="Tampilkan/sembunyikan password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition p-1 min-w-[44px] min-h-[44px] grid place-items-center focus-visible:ring-2 focus-visible:ring-primary-500 rounded-lg">
                            <i class="ri-eye-off-line text-base" x-show="!show"></i>
                            <i class="ri-eye-line text-base" x-show="show" x-cloak></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                {{-- Remember & Forgot --}}
                <div class="flex items-center justify-between pt-1 fade-up animate-delay-4">
                    <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded-lg border-slate-300 text-primary-500 focus:ring-primary-500/20 cursor-pointer accent-primary-500">
                        <span class="text-xs text-slate-500 dark:text-slate-400">Ingat saya</span>
                    </label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium transition">Lupa password?</a>
                    @endif
                </div>

                {{-- Submit Button --}}
                <button type="submit"
                        class="group w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold py-3.5 rounded-2xl transition-all duration-200 text-sm shadow-lg shadow-primary-600/25 hover:shadow-xl hover:shadow-primary-600/30 mt-2 fade-up animate-delay-4">
                    Masuk
                    <i class="ri-arrow-right-line text-base transition-transform duration-200 group-hover:translate-x-1"></i>
                </button>
            </form>

            {{-- Register Link --}}
            @if(Route::has('register'))
                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-white/[0.08] text-center fade-up animate-delay-4">
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-semibold transition">Daftar sekarang</a>
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-guest-layout>
