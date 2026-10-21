<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-[1.65rem] font-bold tracking-tight text-slate-800 dark:text-slate-100">Daftar Akun</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1.5">Buat akun baru untuk memulai</p>
    </div>

    @if(session('error'))
        <div class="mb-5 flex items-start gap-2.5 rounded-2xl border border-red-200/60 bg-red-50/80 dark:border-red-500/15 dark:bg-red-500/5 px-4 py-3 text-sm text-red-600 dark:text-red-400">
            <i class="ri-error-warning-line text-base mt-0.5 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ show: false, submitting: false }" x-on:submit="submitting = true">
        @csrf

        <div>
            <label for="name" class="block text-[13px] font-medium text-slate-600 dark:text-slate-400 mb-1.5">Nama</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full px-4 py-3 border border-slate-200/80 dark:border-white/[0.08]/60 rounded-2xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400/70 dark:placeholder-slate-500 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/10 transition-all duration-200 bg-surface-card dark:bg-white/[0.06]"
                   placeholder="Nama lengkap">
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <div>
            <label for="email" class="block text-[13px] font-medium text-slate-600 dark:text-slate-400 mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username"
                   class="w-full px-4 py-3 border border-slate-200/80 dark:border-white/[0.08]/60 rounded-2xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400/70 dark:placeholder-slate-500 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/10 transition-all duration-200 bg-surface-card dark:bg-white/[0.06]"
                   placeholder="anda@email.com">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <label for="password" class="block text-[13px] font-medium text-slate-600 dark:text-slate-400 mb-1.5">Password</label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password" autocomplete="new-password" minlength="8"
                       class="w-full px-4 pr-11 py-3 border border-slate-200/80 dark:border-white/[0.08]/60 rounded-2xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400/70 dark:placeholder-slate-500 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/10 transition-all duration-200 bg-surface-card dark:bg-white/[0.06]"
                       placeholder="Minimal 8 karakter">
                <button type="button" @click="show = !show" tabindex="-1" aria-label="Tampilkan/sembunyikan password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition p-0.5">
                    <i class="ri-eye-off-line text-[15px]" x-show="!show"></i>
                    <i class="ri-eye-line text-[15px]" x-show="show" x-cloak></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-[13px] font-medium text-slate-600 dark:text-slate-400 mb-1.5">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" minlength="8"
                   class="w-full px-4 py-3 border border-slate-200/80 dark:border-white/[0.08]/60 rounded-2xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400/70 dark:placeholder-slate-500 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/10 transition-all duration-200 bg-surface-card dark:bg-white/[0.06]"
                   placeholder="Ulangi password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <button type="submit"
                :disabled="submitting"
                class="group w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 disabled:bg-primary-400 text-white font-semibold py-3 rounded-2xl transition-all duration-200 text-sm mt-3 shadow-sm shadow-primary-600/20 hover:shadow-md hover:shadow-primary-600/25">
            <svg x-show="submitting" x-cloak class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span x-show="!submitting">Daftar Sekarang</span>
            <span x-show="submitting" x-cloak>Mendaftar...</span>
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-400 dark:text-slate-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-semibold transition">Masuk</a>
    </p>
</x-guest-layout>
