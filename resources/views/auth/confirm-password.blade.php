<x-guest-layout>
    @if(session('error'))
        <div class="mb-5 flex items-start gap-2.5 rounded-2xl border border-red-200/60 bg-red-50/80 dark:border-red-500/15 dark:bg-red-500/5 px-4 py-3 text-sm text-red-600 dark:text-red-400">
            <i class="ri-error-warning-line text-base mt-0.5 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="mb-8">
        <div class="w-12 h-12 rounded-2xl bg-primary-100/60 dark:bg-primary-500/10 flex items-center justify-center mb-4">
            <i class="ri-shield-keyhole-line text-2xl text-primary-600 dark:text-primary-400"></i>
        </div>
        <h1 class="text-[1.65rem] font-bold tracking-tight text-slate-800 dark:text-slate-100">Konfirmasi Password</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1.5 leading-relaxed">Ini adalah area terlindungi. Masukkan password Anda untuk melanjutkan.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4" x-data="{ show: false }">
        @csrf

        <div>
            <label for="password" class="block text-[13px] font-medium text-slate-600 dark:text-slate-400 mb-1.5">Password</label>
            <div class="relative">
                <i class="ri-lock-password-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400/70 dark:text-slate-500 text-[15px]"></i>
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       class="w-full pl-10 pr-11 py-3 border border-slate-200/80 dark:border-white/[0.08]/60 rounded-2xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400/70 dark:placeholder-slate-500 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/10 transition-all duration-200 bg-surface-card dark:bg-white/[0.06]"
                       placeholder="Masukkan password">
                <button type="button" @click="show = !show" tabindex="-1" aria-label="Tampilkan/sembunyikan password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition p-0.5">
                    <i class="ri-eye-off-line text-[15px]" x-show="!show"></i>
                    <i class="ri-eye-line text-[15px]" x-show="show" x-cloak></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold py-3 rounded-2xl transition-all duration-200 text-sm mt-3 shadow-sm shadow-primary-600/20 hover:shadow-md hover:shadow-primary-600/25">
            <i class="ri-shield-check-line"></i> Konfirmasi
        </button>
    </form>
</x-guest-layout>
