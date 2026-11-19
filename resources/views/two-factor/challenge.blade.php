<x-guest-layout>
    <div class="flex min-h-0 flex-col items-center justify-center px-0 py-6 sm:min-h-[85vh] sm:px-4 sm:py-0">
        <div class="mb-6 text-center fade-up animate-delay-1 sm:mb-8">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-600 shadow-lg shadow-primary-600/25 sm:h-14 sm:w-14">
                <i class="ri-shield-keyhole-line text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">Verifikasi Dua Faktor</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-500">
                Masukkan kode 6 digit dari aplikasi authenticator Anda.
            </p>
        </div>

        <div class="w-full max-w-sm rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/60 fade-up animate-delay-2 dark:border-white/[0.08] dark:bg-surface-elevated dark:shadow-black/30 sm:p-8">
            <form method="POST" action="{{ route('two-factor.challenge.verify') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="code" class="mb-2 block text-xs font-semibold text-slate-600 dark:text-slate-400">Kode Verifikasi</label>
                    <input id="code" type="text" name="code" value="{{ old('code') }}"
                           inputmode="numeric" autocomplete="one-time-code" autofocus
                           class="w-full rounded-2xl border border-slate-200 bg-surface-card px-4 py-3.5 text-center text-lg font-semibold tracking-[0.35em] text-slate-800 outline-none transition-all duration-200 placeholder-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/15 dark:border-white/[0.14] dark:bg-white/[0.06] dark:text-slate-100 dark:placeholder-slate-500"
                           placeholder="000000" maxlength="20">
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>

                <button type="submit"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-primary-700 py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary-600/25 transition-all duration-200 hover:bg-primary-700 hover:shadow-xl active:bg-primary-800">
                    Verifikasi
                    <i class="ri-arrow-right-line text-base transition-transform duration-200 group-hover:translate-x-1"></i>
                </button>
            </form>

            <p class="mt-5 border-t border-slate-100 pt-5 text-center text-xs text-slate-500 dark:border-white/[0.08] dark:text-slate-500">
                Kehilangan akses authenticator? Gunakan salah satu recovery code Anda.
            </p>

            <form method="POST" action="{{ route('logout') }}" class="mt-3 text-center">
                @csrf
                <button type="submit" class="text-xs font-medium text-slate-500 underline-offset-2 hover:underline dark:text-slate-400">
                    Keluar dari akun
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
